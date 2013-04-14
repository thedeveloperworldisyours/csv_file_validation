<?php
/**
 * Store field must correspond to an existing store in the database.
 */
class Semantic_Store extends Abstract_Semantic {
    protected static $QUERY = 'SELECT code from stores where code in("%s")';
    protected static $FIELD = 'code';
}

