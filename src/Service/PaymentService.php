<?php

namespace App\Service;


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
