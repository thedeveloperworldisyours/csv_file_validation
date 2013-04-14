<?php
abstract class Abstract_Semantic extends Abstract_Validator {
    protected $dbLink;
    protected $errors;
    protected static $FIELD;
    protected static $QUERY;
    protected static $TYPE = 'validateExisting';

    function __construct($dbLink = null) {
        $errors = array();
        if($dbLink) {
            $this->dbLink = $dbLink;
        }
    }

    /*
     * Default validator
     * Ensure the extended class has defined the required values
     * Perform the query and pass the result to the proper method
     *
     * @param $input simple array with data
     * @return bool
     */
    public function validate(&$input) {

        if (!$this->dbLink || !static::$QUERY || !static::$FIELD || !static::$TYPE) {
            $this->errors[] = 'Error validating ' . get_class($this) . ', incomplete data was provided.';
            return false;
        }
        $field = static::$FIELD;
        $type = static::$TYPE;
        $list = $this->dbLink->query(sprintf(static::$QUERY, implode($input, '","')));
        return $this->$type($list, $field, $input);
    }

    /*
     * Ensure all requested items where present in the database
     * Otherwise, log all not found items
     *
     * @param list mysqli_result
     * @param $field field to check
     * @param $input array with data
     * @return bool
     */
    protected function validateExisting($list, $field, $input) {
        $ret = $list->num_rows == count($input);
        if(!$ret) {
            $found = array();
            while($obj = $list->fetch_object()){
                $found[] = $obj->$field;
            }
            $this->errors = array_diff($input, $found);
        }
        return $ret;
    }

    /*
     * Return the errors found during the validation
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Return the description message for the error.
     *
     * @param $input array
     * @return string
     */
    public function getErrorMsg($input) {
        if (static::$TYPE == 'validateExisting') {
            $exist = 'must exist';
        } else $exist = 'cannot exist';

        return sprintf('%s(s) "%s" %s on the database.', str_replace('Semantic_', '', get_class($this)), implode($input, ', '), $exist);
    }
}
