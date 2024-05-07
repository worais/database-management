<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use PhpMyAdmin\SqlParser\Components\Condition;

add_filter('worais-database-statement-select', function($query){   
    if(isset($_POST['where']) && isset($_POST['where']['build'])){
        $query['statement']->where = [];

        foreach($_POST['where']['conditions'] as $key => $condition){
            $key       = esc_html(sanitize_text_field($key));
            $condition = esc_html(sanitize_text_field($condition));

            $query['statement']->where[$key] = new Condition($condition);
            if($condition == 'AND' OR $condition == 'OR'){
                $query['statement']->where[$key]->isOperator = true;
            }
        }
    }

    echo "<script>window.where = ".json_encode( $query['statement']->where ).";</script>";

    return $query;
});