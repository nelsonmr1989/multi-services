<?php
namespace App\Controller\Payment;

use App\Helper\GeneralHelper;
use http\Exception;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use \net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\MerchantAuthenticationType;

/*
 * TODO
 * 0- Create repo
 * 1- Create handler of exception
 * 2- Create customs exception when payment failed, send email or kind of notification when that happened
 * 3- Create validations
 * 3- Try to resolver the most TODO task
 * 4- Create endpoint to process orders
 * 5- Create endpoint to savepayment profile to re-use if and create custom profile if do niot exist
 * 6- Create endpoint to remove payment profile
 * 7- Create endpoint to update payment profile
 * 8- Create DB with Daymer (Users, Orders, Products by orders)
 * */


class AuthorizeNetService
{
    private $apiLoginId;
    private $transactionKey;
    private $environment;

    public function __construct(string $apiLoginId, string $transactionKey, string $mode)
    {
        $this->apiLoginId = $apiLoginId;
        $this->transactionKey = $transactionKey;
        $this->environment = $mode === 'sandbox'
            ? ANetEnvironment::SANDBOX
            : ANetEnvironment::PRODUCTION;
    }

    private function merchantAuthentication(?MerchantAuthenticationType &$merchantAuth) {
        if (!$merchantAuth instanceof MerchantAuthenticationType) {
            $merchantAuth = new AnetAPI\MerchantAuthenticationType();
            $merchantAuth->setName($this->apiLoginId);
            $merchantAuth->setTransactionKey($this->transactionKey);
        }
    }

    private function apiHandlerResponse($response): void {
        if (empty($response) ||  $response->getMessages()->getResultCode() !== "Ok") {
            // TODO throw exception
        }
    }

    public function processTransaction(?MerchantAuthenticationType $merchantAuth, array $cardDetails, float $amount, array $customerData): array
    {
        // TODO MOVE THIS CODE TO OTHER PLACE
        $invoiceNumber = 'INV' . time() . GeneralHelper::getRandomCode(4);

        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        $refId = 'ref' . time();

        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($invoiceNumber);
        $order->setDescription("Customer multi-service");

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType("authCaptureTransaction");
        $transactionRequest->setAmount($amount);
        $transactionRequest->setOrder($order);

        try {
            if (!empty($customerData['customerProfileId']) && !empty($customerData['customerPaymentProfileId'])) {
                $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
                $profileToCharge->setCustomerProfileId($customerData['customerProfileId']);
                $paymentProfile = new AnetAPI\PaymentProfileType();
                $paymentProfile->setPaymentProfileId($customerData['customerPaymentProfileId']);
                $profileToCharge->setPaymentProfile($paymentProfile);
                $transactionRequest->setProfile($profileToCharge);
            } else {
                $customerProfileId = $this->createCustomerProfile($merchantAuth, $customerData);
                $customerPaymentProfileId = $this->createCustomerPaymentProfile($merchantAuth, $customerProfileId, $customerData['payment']);

                $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
                $profileToCharge->setCustomerProfileId($customerProfileId);
                $paymentProfile = new AnetAPI\PaymentProfileType();
                $paymentProfile->setPaymentProfileId($customerPaymentProfileId);
                $profileToCharge->setPaymentProfile($paymentProfile);
                $transactionRequest->setProfile($profileToCharge);
            }
        } catch (\Exception $ex) {
            if (!empty($customerData['payment'])) {
                $paymentData =  $customerData['payment'];

                $customerAddress = new AnetAPI\CustomerAddressType();
                $customerAddress->setFirstName($paymentData['billTo']['firstName']);
                $customerAddress->setLastName($paymentData['billTo']['lastName']);
                $customerAddress->setAddress($paymentData['billTo']['address']);
                $customerAddress->setCity($paymentData['billTo']['city']);
                $customerAddress->setState($paymentData['billTo']['state']);
                $customerAddress->setZip($paymentData['billTo']['zip']);
                $customerAddress->setCountry($paymentData['billTo']['country']);

                $customerData = new AnetAPI\CustomerDataType();
                $customerData->setType("individual");
                $customerData->setEmail($customerData['email']);

                $creditCard = new AnetAPI\CreditCardType();
                $creditCard->setCardNumber($paymentData['cardNumber']);
                $creditCard->setExpirationDate($paymentData['expirationDate']);
                $creditCard->setCardCode($paymentData['cvv']);

                // Add the payment data to a paymentType object
                $paymentOne = new AnetAPI\PaymentType();
                $paymentOne->setCreditCard($creditCard);

                $transactionRequest->setPayment($paymentOne);
                $transactionRequest->setBillTo($customerAddress);
                $transactionRequest->setCustomer($customerData);
            } else {
                // TODO throw the same exception
            }
        }

        // Request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequest);

        // Execute transaction
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->apiHandlerResponse($response);

        $transactionResponse = $response->getTransactionResponse();

        return [
            'status' => 'success',
            'transaction_id' => $transactionResponse->getTransId(),
            'message' => $transactionResponse->getMessages()[0]->getDescription(),
        ];
    }

    public function getCustomerProfile(?MerchantAuthenticationType $merchantAuth, $customerProfileId) {

        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId($customerProfileId);

        $controller = new AnetController\GetCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->apiHandlerResponse($response);

        $profileSelected = $response->getProfile();

        return $profileSelected->jsonSerialize();
    }

    public function createCustomerProfile(?MerchantAuthenticationType $merchantAuth, array $customerData): ?string
    {
        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        $refId = 'ref' . time();

        // Customer profile
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setMerchantCustomerId(uniqid());
        $customerProfile->setEmail($customerData['email']);
        $customerProfile->setDescription("Customer multi-service");

        // Request
        $request = new AnetAPI\CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setProfile($customerProfile);
        $request->setRefId($refId);

        $controller = new AnetController\CreateCustomerProfileController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->apiHandlerResponse($response);
        return $response->getCustomerProfileId();
    }

    public function deleteCustomerProfile(?MerchantAuthenticationType $merchantAuth, $customerProfileId): bool {
        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        $request = new AnetAPI\DeleteCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId( $customerProfileId );

        $controller = new AnetController\DeleteCustomerProfileController($request);
        $response = $controller->executeWithApiResponse( $this->environment);

        $this->apiHandlerResponse($response);
        return true;
    }

    public function createCustomerPaymentProfile(?MerchantAuthenticationType $merchantAuth, $customerProfileId, array $paymentData): ?string {

        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        // Set credit card information for payment profile
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($paymentData['cardNumber']);
        $creditCard->setExpirationDate($paymentData["expirationDate"]);
        $creditCard->setCardCode($paymentData['cvv']);
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billto = new AnetAPI\CustomerAddressType();
        $billto->setFirstName($paymentData['billTo']['firstName']);
        $billto->setLastName($paymentData['billTo']['lastName']);
        $billto->setAddress($paymentData['billTo']['address']);
        $billto->setCity($paymentData['billTo']['city']);
        $billto->setState($paymentData['billTo']['state']);
        $billto->setZip($paymentData['billTo']['zip']);
        $billto->setCountry($paymentData['billTo']['country']);
        $billto->setPhoneNumber($paymentData['billTo']['phoneNumber']);

        // Create a new Customer Payment Profile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType('individual');
        $paymentProfile->setBillTo($billto);
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfile->setDefaultPaymentProfile(true);

        // Assemble the complete transaction request
        $paymentProfileRequest = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $paymentProfileRequest->setMerchantAuthentication($merchantAuth);

        // Add an existing profile id to the request
        $paymentProfileRequest->setCustomerProfileId($customerProfileId);
        $paymentProfileRequest->setPaymentProfile($paymentProfile);
        $paymentProfileRequest->setValidationMode("liveMode");

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerPaymentProfileController($paymentProfileRequest);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->apiHandlerResponse($response);
        return $response->getCustomerPaymentProfileId();
    }

    public function updateCustomerPaymentProfile(?MerchantAuthenticationType $merchantAuth, $customerProfileId, $customerPaymentProfileId, array $paymentData): bool {
        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        $refId = 'ref' . time();

        $request = new AnetAPI\GetCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setRefId( $refId);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($customerPaymentProfileId);

        $controller = new AnetController\GetCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse($this->environment);

        $this->apiHandlerResponse($response);

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber( $paymentData['cardNumber']);
        $creditCard->setExpirationDate($paymentData["expirationDate"]);

        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        $paymentProfile = new AnetAPI\CustomerPaymentProfileExType();

        $billto = $response->getPaymentProfile()->getbillTo();
        if ($paymentData['billTo']['isUpdated']) {
            $billto->setFirstName($paymentData['billTo']['firstName']);
            $billto->setLastName($paymentData['billTo']['lastName']);
            $billto->setAddress($paymentData['billTo']['address']);
            $billto->setCity($paymentData['billTo']['city']);
            $billto->setState($paymentData['billTo']['state']);
            $billto->setZip($paymentData['billTo']['zip']);
            $billto->setCountry($paymentData['billTo']['country']);
            $billto->setPhoneNumber($paymentData['billTo']['phoneNumber']);

            $paymentProfile->setBillTo($billto);
        }

        $paymentProfile->setBillTo($billto);
        $paymentProfile->setCustomerPaymentProfileId($customerPaymentProfileId);
        $paymentProfile->setPayment($paymentCreditCard);

        $request = new AnetAPI\UpdateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId($customerProfileId);
        $request->setPaymentProfile($paymentProfile);

        $controller = new AnetController\UpdateCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse( $this->environment);

        $this->apiHandlerResponse($response);

        return true;
    }

    public function deleteCustomerPaymentProfile(?MerchantAuthenticationType $merchantAuth, $customerProfileId, $customerPaymentProfileId): bool {
        // Merchant authentication
        $this->merchantAuthentication($merchantAuth);

        $request = new AnetAPI\DeleteCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuth);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($customerPaymentProfileId);
        $controller = new AnetController\DeleteCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse( $this->environment);

        $this->apiHandlerResponse($response);

        return true;
    }
}
