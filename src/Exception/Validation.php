<?php
namespace App\Exception;

class Validation extends \Exception
{
    private $_messages;

    public function __construct($message = null, $code = 0, array $messages = [], \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_messages = $messages;
    }

    public function getValidationMessages()
    {
        return $this->_messages;
    }
}
