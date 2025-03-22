<?php

namespace App\Validation\Payment;

use App\Helper\GeneralHelper;
use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCustomerPaymentProfileValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
            'cardNumber' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Número de tarjeta'),
                ]),
                new Assert\CardScheme(['VISA', 'MASTERCARD', 'AMEX', 'DISCOVER'], 'El número de tarjeta es invalido')
            ],
            'expirationDate' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Fecha de expiración'),
                ]),
                new Assert\Regex([
                    'pattern' => '/^\d{4}-(0[1-9]|1[0-2])$/',
                    'message' => 'Formato de fecha invalido: ex. YYYY-MM.',
                ]),
                new Assert\Callback(function ($value, $context) {
                    $currentDate = new \DateTime('first day of this month');
                    $inputDate = \DateTime::createFromFormat('Y-m', $value);

                    if (!$inputDate || $inputDate < $currentDate) {
                        $context->buildViolation('La tarjeta esta expirada')
                            ->addViolation();
                    }
                }),
            ],
            'cvv' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('CVV')
                ]),
                new Assert\Regex([
                    'pattern' => '/^\d{3,4}$/',
                    'message' => 'CVV debería ser de 3 o 4 dígitos',
                ]),
            ]
        ];
        return $validations;
    }
}

