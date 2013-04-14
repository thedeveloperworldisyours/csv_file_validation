<?php
/**
 * Include all the required files 
 */
require_once dirname(__FILE__) . '/Analyzer.php';
require_once dirname(__FILE__) . '/ValidatorContext.php';
require_once dirname(__FILE__) . '/Validators/Abstract_Validator.php';
foreach (glob(dirname(__FILE__) . '/Validators/*.php') as $filename) {
    require_once $filename;
}

/**
 * Some examples
 */
$required_fields = array('ean', 'qty');
$profile = 'update';
$files = array('ex1.csv','ex2.csv', 'ex3.csv', 'ex4.csv');
foreach($files as $file) {
    $analyzer = new Analyzer($required_fields, $profile);
    echo 'Result:' . "\n";
    var_dump($analyzer->analyze(Analyzer::LEXICAL, $file));
    var_dump($analyzer->getResults());
    echo 'Errors:' . "\n";
    var_dump($analyzer->getErrors());
}
