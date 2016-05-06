<?php

namespace App\Traits;

/**
 * Validation
 */
trait Validation
{
    private $cached_errors;

    public function __construct()
    {
        $this->cached_errors = [];
    }

    abstract public function validation($validator, $input);

    public function getValidationMessage($errors = null)
    {
        if (!is_array($errors)) {
            $errors = $this->cached_errors;
        }

        $mes = '';
        foreach ($errors as $error) {
            $mes = $mes . '' . $error . PHP_EOL;
        }
        return $mes;
    }
}
