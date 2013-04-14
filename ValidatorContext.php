<?php
class ValidatorContext {

    private $strategy;
    private $warnings;

    /*
     * Create the instance of the validator by building the class name with the UpperCamelCase format
     * Generate a warning if the validator wasn't found, and create a generic validator instance
     *
     * @param $type string type of analysis
     * @param $strategy string validator strategy class name
     * @param $optData array optional data passed to the semantic validators
     * @return void
     */
    function __construct($type, $strategy, $profile, $optData = null) {
        $this->warnings = array();
        $validator = ucfirst($type) . '_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $strategy)));
        if(class_exists($validator)) {
            $this->strategy = new $validator($profile, $optData);
        }else {
            $this->strategy = new Generic();
            $this->warnings[] = sprintf('Strategy %s (%s) is not implemented for the %s analizer ' . "\n", $strategy, $validator, $type);
        }
    }

    /**
     * Remove elements from memory
     * @return void
     */
    function __destruct() {
        unset($this->strategy);
    }

    /**
     * getTokens
     * Return the tokens retrieved from the validator
     * @return array
     */
    public function getTokens() {
        return $this->strategy->getTokens();
    }

    /**
     * getErrors
     * Return the errrors found during the validation
     * @return array
     */
    public function getErrors() {
        return $this->strategy->getErrors();
    }

    /**
     * getWarnings
     * Return the warnings found during the validation
     * @return array
     */
    public function getWarnings() {
        return $this->warnings;
    }

    /**
     * getErrorMsg
     * Return the errror description to help the user to solve the problem
     * @param $input string|array
     * @return string
     */
    public function getErrorMsg($input) {
        return $this->strategy->getErrorMsg($input);
    }

    /**
     * validate
     * @access public
     * @param $input mixed input data of the csv
     * @return bool whether this data is valid or not for current validator
     */
    public function validate(&$input) {
        //Uncomment the line below to debug
        //echo get_class($this->_strategy) . ": " . $input . "\n";
        return $this->strategy->validate($input);
    }

}
