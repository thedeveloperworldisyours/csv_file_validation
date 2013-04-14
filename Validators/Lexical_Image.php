<?php
/*
 * Image is an optional field, but if it's not empty, it must be a valid image name.
 */
class Lexical_Image extends Abstract_Lexical {
    public function validate(&$input) {
        parent::validate(&$input);
        return strlen($input) == 0 || preg_match('/[a-zA-Z0-9]+.(jpg|png|gif)/', $input);
    }

    public function getErrorMsg($input) {
        return 'allowed values: valid image name (jpg|png|gif), ' . parent::getErrorMsg($input);
    }
}
