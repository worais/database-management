<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use PhpMyAdmin\SqlParser\Components\Limit;

add_filter('worais-database-statement-select', function($query){
    if(!$query['statement']->limit){ 
        $query['statement']->limit = new Limit(10); 
    };

    if(isset($_POST['offset']) && $_POST['offset'] != 'false'){
        $limit  = esc_html(sanitize_text_field($_POST['limit']));
        $offset = esc_html(sanitize_text_field($_POST['offset']));

        $query['statement']->limit = new Limit($limit, $offset); 
    }

    echo "<script>window.offset= ".$query['statement']->limit->offset.";</script>";    
    echo "<script>window.limit = ".$query['statement']->limit->rowCount.";</script>";

    return $query;
});