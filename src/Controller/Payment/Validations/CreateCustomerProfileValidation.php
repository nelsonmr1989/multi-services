<?php

namespace App\Controller\Payment\Validations;

use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCustomerProfileValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
            'email' => [
                new Assert\NotBlank()
            ],
        ];
        return $validations;
    }
}

