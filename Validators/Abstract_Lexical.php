<?php
abstract class Abstract_Lexical extends Abstract_Validator {

    protected $tokens;

    function __construct() {
        $this->tokens = array();
    }

    public function getTokens() {
        return $this->tokens;
    }
    public function validate(&$input) {
        $this->tokens[] = $input;
    }

    /*
     * Return the last part of the error message
     * Can be called from a child class to complete the returned message.
     *
     * @param $input string
     * @return string
     */
    public function getErrorMsg($input) {
        return sprintf('but "%s" provided.', $input);
    }
}
