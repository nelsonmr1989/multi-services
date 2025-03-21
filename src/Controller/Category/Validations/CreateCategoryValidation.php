<?php

namespace App\Controller\Category\Validations;

use App\Helper\GeneralHelper;
use App\Interfaces\IValidator;
use App\Validation\Base;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCategoryValidation extends Base implements IValidator
{
    public function getValidations()
    {
        $validations = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 180])
            ]
        ];
        return $validations;
    }
}

