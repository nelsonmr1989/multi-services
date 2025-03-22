<?php
namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/v1")]
class PaymentController extends AbstractController {

    #[Route('/payment/customer-payment-profile', methods: ['POST'])]
    public function createCustomerProfile(Request $request, PaymentService $paymentService): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $response = $paymentService->createCustomerPaymentProfile($data);
        return new JsonResponse($response, 200);
    }

    #[Route("/payment/customer-payment-profile", methods: ["GET"])]
    public function getCustomerProfile(AuthorizeNetService $authorizeNetService) {
        $customerProfile = $authorizeNetService->getCustomerProfile(null, null);
        return new JsonResponse($customerProfile, 200);
    }

    #[Route("/payment/customer-payment-profile/{customerPaymentProfileId}", methods: ["DELETE"])]
    public function deleteCustomerProfile(AuthorizeNetService $authorizeNetService, $customerPaymentProfileId) {
       $authorizeNetService->deleteCustomerPaymentProfile(null, null, $customerPaymentProfileId);
        return new JsonResponse(true, 200);
    }
}
