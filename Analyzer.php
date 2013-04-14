<?php
class Analyzer {

    const LEXICAL = 'lexical';
    const SEMANTIC = 'semantic';

    /**
     * Allowed profiles
     */
    private static $_PROFILES = array('import', 'update');

    private $_validators;
    private $_columnIndexes;
    private $_requiredFields;
    private $_profile;
    private $_errors;
    private $_results;

    function __construct(array $required, $profile) {
        $this->_validators = array();
        $this->_columnIndexes = array();
        $this->_errors = array();
        $this->_results = array();
        $this->_requiredFields = $required;
        $this->_profile = $profile;
        if(!in_array($profile, self::$_PROFILES)) {
            die('Unknown profile specified.');
        }
    }

    public function getProfile() {
        return $this->_profile;
    }
    public function getErrors() {
        return $this->_errors;
    }
    public function getResults() {
        return $this->_results;
    }

    /**
     * getDelimiter
     * Try to detect the delimiter character on a CSV file, by reading the first row.
     *
     * @param mixed $file
     * @access public
     * @return string
     */
    public static function getDelimiter($file) {
        $delimiter = false;
        $line = '';
        if($f = fopen($file, 'r')) {
            $line = fgets($f); // read until first newline
            fclose($f);
        }
        if(strpos($line, ';') !== FALSE && strpos($line, ',') === FALSE) {
            $delimiter = ';';
        } else if(strpos($line, ',') !== FALSE && strpos($line, ';') === FALSE) {
            $delimiter = ',';
        } else {
            die('Unable to find the CSV delimiter character. Make sure you use "," or ";" as delimiter and try again.');
        }
        return $delimiter;
    }

    /*
     * Wrapper method that performs the specified analysis
     * Keep track of the time spent on the analysis
     *
     * @param $type (lexical|semantic)
     * @param $args mixed
     * @return bool
     */
    public function analyze($type, $args) {
        $passed = false;
        if(method_exists($this, $type)){

            $start = microtime(true);
            $result = $this->$type($args);
            $time_taken = microtime(true) - $start;

            if ($result) {
                $this->_results[] = 'The ' . $type . ' analysis has successfully passed all validations for the ' . $this->getProfile() . ' profile.' . "\n";
            }
            $this->_results[] = 'Time taken: ' . $time_taken . ' seconds' . "\n";
        }
        return $result;
    }

    /**
     * parseFirstRow
     * Check that the column names aren't duplicated
     * Ensure all required fields are present
     * Create the instances of each validator.
     *
     * @param array $data
     * @access protected
     * @return bool
     */
    protected function parseFirstRow(array $data) {
        $valid = true;
        //Clean the data
        $data = array_filter(array_map('trim', array_map('strtolower', $data)));

        //Ensure that there aren't duplicated columns
        $dupes = array_diff_key($data, array_unique($data));
        if(!empty($dupes)) {
            $this->_errors[] = sprintf('The following columns are duplicated on the CSV: "%s".', implode($dupes, '", "'));
            $valid = false;
        }

        //Ensure all required columns are present
        if($valid &&
            //The number of columns is lower than the required fields, we don't need to keep checking, some columns are missing.
            (count($data) < count($this->_requiredFields) ||
            //The number of optional fields must match with the number of fields that are not required, otherwise something is missing.
            count(array_diff($data, $this->_requiredFields)) !== (count($data) - count($this->_requiredFields)) ||
            //If the operation is an import, either categories or category_ids must be present
            ($this->_profile == 'import' && !(in_array('categories', $data) || in_array('category_ids', $data))))) {

                $required = implode(array_diff($this->_requiredFields, $data), '", "');
                if($this->_profile == 'import' && !in_array('category_ids', $data) && !in_array('categories', $data)) {
                    if($required) {
                        $required .= '" and "categories" or "category_ids';
                    } else {
                        $required = 'categories" or "category_ids';
                    }
                }
                $this->_errors[] = sprintf('The following columns are missing on the CSV: "%s".', $required);
                $valid = false;
            }

        if($valid) {
            //Instantiate all the lexical validators
            foreach ($data as $key => $value) {
                $this->_validators[$key] = new ValidatorContext(Analyzer::LEXICAL, $value, $this->_profile);
                $this->_columnIndexes[$key] = $value;
            }
        }
        return $valid;
    }

    /*
     * Perform the lexical analysis over the CSV
     * Iterate over the file and exit if an error is found
     *
     * @param $file string full path of the csv file
     * @return bool whether the file is valid (from a lexical point of view) or not
     */
    protected function lexical($file) {
        if (!file_exists($file))  {
            return false;
        } else {
            $delimiter = self::getDelimiter($file);
        }

        $handle = fopen($file, 'r');

        //Parse the first row, instantiate all the validators
        $valid = $this->parseFirstRow(fgetcsv($handle, 0, $delimiter));
        //Number of columns specified on the header
        $num_columns = sizeOf($this->_columnIndexes);
        //line number count
        $i = 1;

        while(($data = fgetcsv($handle, 0, $delimiter)) !== FALSE && $valid) {
            $errors = array();

            //For each column
            foreach ($data as $key => $value) {

                //Skip all columns without header
                if($key >= $num_columns) {
                    break;
                }

                $value = trim($value);

                //Validate
                $errors[$this->_columnIndexes[$key]] = $this->_validators[$key]->validate($value);

                $valid = $valid && $errors[$this->_columnIndexes[$key]];
            }

            //If any error was found, exit
            if(!$valid) {
                $filtered_errors = array_keys($errors, false);
                if(count($filtered_errors) > 0) {
                    //Store the errors founds on the current line
                    $this->_errors[$i] = $filtered_errors;
                }
                break;
            }
            $i++;
        }
        fclose($handle);

        return $valid;
    }

    /**
     * semantic
     * TODO: By you! :)
     * 
     * @param mixed $input multidimensional array with all the collected tokens of the lexical phase
     * @access protected
     * @return void
     */
    protected function semantic($input) {
        //Establish a db connection and foreach field check if the given values are correct
        //HINT: You should probably make an array_merge of the whole input in order to get a simple array
        //and then minimize the queries to the database.
    }
}
