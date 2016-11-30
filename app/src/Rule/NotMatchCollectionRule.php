<?php

namespace App\Rule;

use \Fuel\Validation\AbstractRule;

class NotMatchCollectionRule extends AbstractRule
{
    protected $message = 'いずれかに一致してはいけません';


    public function validate($value, $field = null, $allFields = null)
    {
        $params = $this->getParameter();

        $result = array_filter($params, function ($var) use ($value) {
            return mb_strpos($value, $var) !== false;
        });

        return count($result) === 0;
    }

    public function setParameter($params)
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        return parent::setParameter($params);
    }

    public function getMessageParameters()
    {
        return array(
            'collection' => $this->getParameter(),
        );
    }
}
