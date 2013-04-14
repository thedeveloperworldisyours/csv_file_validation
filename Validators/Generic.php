<?php
class Generic extends Abstract_Validator {
    public function validate(&$input) {
        return true;
    }
    public function getErrorMsg($input) {
        return false;
    }
}
