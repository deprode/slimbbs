<?php

namespace App\Rule;

use \Fuel\Validation\AbstractRule;

class IsArrayRule extends AbstractRule
{
    protected $message = '不正な配列です';

    public function validate($array, $field = null, $allFields = null)
    {
        if (count($array) === 0 || !is_array($array)) {
            return false;
        }

        return true;
    }
}
