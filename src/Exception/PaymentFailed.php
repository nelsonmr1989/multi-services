<?php
namespace App\Exception;

class PaymentFailed extends \Exception
{
    private array $_details;

    public function __construct($message = null, array $details = [], $code = 400, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_details = $details;
    }

    public function getErrorDetails(): array
    {
        return $this->_details;
    }
}
