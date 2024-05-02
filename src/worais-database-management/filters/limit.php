<?php
use PhpMyAdmin\SqlParser\Components\Limit;

add_filter('worais-database-statement-select', function($query){
    if(!$query['statement']->limit){ 
        $query['statement']->limit = new Limit(10); 
    };

    return $query;
});