<?php
namespace App\Controller\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController {

    #[Route('/process-payment', name: 'process_payment', methods: ['POST'])]
    public function processPayment(Request $request, AuthorizeNetService $authorizeNetService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $cardDetails = [
            'card_number' => $data['card_number'],
            'expiration_date' => $data['expiration_date'], // Fecha de expiración (YYYY-MM)
            'cvv' => $data['cvv']
        ];

        $amount = $data['amount'];

        $transactionResponse = $authorizeNetService->processTransaction($cardDetails, $amount);

        if ($transactionResponse['status'] === 'success') {
            return new JsonResponse($transactionResponse);
        } else {
            // TODO remove this (create custom exception)
            return new JsonResponse(['status' => $transactionResponse['status'], 'message' => $transactionResponse['message']], 400);
        }

        if ($transactionResponse !== null && $transactionResponse->getResponseCode() === "1") {
            return new JsonResponse([
                'status' => 'success',
                'transaction_id' => $transactionResponse->getTransId(),
                'message' => $transactionResponse->getMessages()[0]->getDescription(),
            ]);
        }

        return new JsonResponse(['status' => 'error', 'message' => 'Transaction failed'], 400);
    }
}
