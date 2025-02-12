<?php

namespace App\Controller\Recipient\Validations;

use App\Helper\GeneralHelper;
use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CreateRecipientValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 150])
            ],
            'firstName' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 150])
            ],
            'lastName' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 150])
            ],
            'ci' => [
                new Assert\Length(['max' => 11])
            ],
            'email' => [
                new Assert\Length(['max' => 150]),
                new Assert\Email([
                    'message' => 'The email "{{ value }}" is not valid.',
                ])
            ],
            'phoneNumber' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 50])
            ],
            'alternatePhone' => [
                new Assert\Length(['max' => 50])
            ],
            'address' => [
                new Assert\NotBlank()
            ]
        ];
        return $validations;
    }
}

