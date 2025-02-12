<?php

namespace App\Controller\Payment\Validations;

use App\Helper\GeneralHelper;
use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerPaymentProfileBillToValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
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

