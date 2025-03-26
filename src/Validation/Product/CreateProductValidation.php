<?php

namespace App\Validation\Product;

use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CreateProductValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'category' => [
                new Assert\NotBlank()
            ],
            'price' => [
                new Assert\NotBlank()
            ],
            'quantityInStock' => [
                new Assert\NotBlank()
            ],
            'minimumInStock' => [
                new Assert\NotBlank()
            ]
        ];
        return $validations;
    }
}

