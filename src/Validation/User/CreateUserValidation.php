<?php

namespace App\Validation\User;

use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
            'firstName' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 50])
            ],
            'lastName' => [
                new Assert\Length(['max' => 100])
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 180]),
                new Assert\Email([
                    'message' => 'The email "{{ value }}" is not valid.',
                ])
            ],
            'phoneNumber' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 20])
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 20])
            ]
        ];
        return $validations;
    }
}

