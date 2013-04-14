<?php
/*
 * EAN is an optional field, but if it's not empty, it must be a valid code.
 */
class Lexical_Ean extends Abstract_Lexical {
    protected function checkEAN($fullcode) {
        $code = substr($fullcode, 0, -1);
        $checksum = 0;
        foreach (str_split(strrev($code)) as $pos => $val) {
            $checksum += $val * (3 - 2 * ($pos % 2));
        }
        return (10 - ($checksum % 10)) % 10 == substr($fullcode,-1);
    }
    public function validate(&$input) {
        parent::validate(&$input);
        return strlen($input) == 0 || $this->checkEAN($input);
    }

    public function getErrorMsg($input) {
        return 'allowed values: valid EAN code (13 digits number), ' . parent::getErrorMsg($input);
    }
}
