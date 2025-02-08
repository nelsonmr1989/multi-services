<?php

namespace App\Controller\Payment\Validations;

use App\Helper\GeneralHelper;
use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCustomerPaymentProfileValidation extends Base implements IValidator
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
            ],
            'billTo.firstName' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Nombre')
                ]),
            ],
            'billTo.lastName' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Apellidos')
                ]),
            ],
            'billTo.address' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Dirección')
                ]),
            ],
            'billTo.city' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Ciudad')
                ]),
            ],
            'billTo.state' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Estado')
                ]),
            ],
            'billTo.zip' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Código postal')
                ]),
            ],
            'billTo.country' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Código postal')
                ]),
                new Assert\Regex([
                    'pattern' => '/^[A-Z]{2}$/',
                    'message' => 'Código del pais inválido ex. (ISO Alpha-2) AR, US, ES ....',
                ])
            ],
            'billTo.phoneNumber' => [
                new Assert\NotBlank([
                    'message' => GeneralHelper::getRequiredText('Número de teléfono')
                ])
            ]
        ];
        return $validations;
    }
}

