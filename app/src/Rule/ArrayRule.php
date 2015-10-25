<?php

namespace App\Rule;

class ArrayRule extends \Fuel\Validation\AbstractRule
{
    protected $message = 'エラーです';

    public function validate($value, $field = null, $allFields = null)
    {
        if (count($value) === 0) {
            return false;
        }

        foreach ($value as $i => $val) {
            if (preg_match('/[0-9a-zA-Z]/', $val) == false) {
                return false;
            }
        }

        return true;
    }
}
