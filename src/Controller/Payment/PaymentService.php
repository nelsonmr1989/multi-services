<?php

namespace App\Controller\Payment;

use App\Exception\PaymentFailed;
use App\Helper\GeneralHelper;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller as AnetController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class PaymentService
{
    private AuthorizeNetService $authorizeNetService;

    public function __construct(AuthorizeNetService $authorizeNetService)
    {
        $this->authorizeNetService = $authorizeNetService;
    }

    public function createCustomerPaymentProfile(array $data): array {
        $merchantAuth = null;

        $this->authorizeNetService->merchantAuthentication($merchantAuth);

        $customerProfileId = $this->authorizeNetService->createCustomerProfile($merchantAuth, $data);
        $customerPaymentProfileId = $this->authorizeNetService->createCustomerPaymentProfile($merchantAuth, $customerProfileId, $data['payment']);

        return $this->authorizeNetService->getCustomerPaymentProfile($merchantAuth, $customerProfileId, $customerPaymentProfileId);
    }
}
