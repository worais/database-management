<?php
use PhpMyAdmin\SqlParser\Components\Expression;

add_filter('worais-database-statement-select', function($query){
    if(isset($_POST['colunms'])){
        $colunms_input = $_POST['colunms'];
        if(!empty($colunms_input)){
            $colunms_input = explode(',', $colunms_input);
            foreach($query['columns'] as $key => $row){
                $query['columns'][$key]['active'] = false;
            }            
            foreach($colunms_input as $key => $column){
                $query['statement']->expr[$key] = new Expression($null, null, $column, null);
                $query['columns'][$column]['active'] = true;
            }              
        }
    }      

    if($query['statement']->expr[0]->expr == '*'){
      
    }else{
        foreach($query['columns'] as $key => $row){
            $query['columns'][$key]['active'] = false;
        }        
        foreach($query['statement']->expr as $expr){
            $query['columns'][$expr->column]['active'] = true;
        }
        echo "<script>window.colunms = false;</script>";
    }

    echo "<script>window.colunms = ".json_encode($query['columns']).";</script>";

    return $query;
});