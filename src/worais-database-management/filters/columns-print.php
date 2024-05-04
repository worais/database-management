<?php
add_filter('worais-database-columns-print-wp_posts-post_author', function($value){
    global $wpdb;

    $user_login = $wpdb->get_var("SELECT `user_login` FROM `wp_users` WHERE `ID` = $value;");
    return "<a href='#query=".base64_encode("SELECT * FROM `wp_users` WHERE `ID` = $value;")."'>$value:$user_login</a>";
});

add_filter('worais-database-columns-print-wp_postmeta-post_id', function($value){
    return "<a href='#query=".base64_encode("SELECT * FROM `wp_posts` WHERE `ID` = $value;")."'>$value</a>";
});


add_filter('worais-database-columns-print-wp_posts-post_content', function($value){
    return strip_tags(mb_strimwidth($value, 0, 100, "..."));
});

add_filter('worais-database-columns-print-primary', function($opts){
    $index = $opts['row'][ $opts['column']['field'] ];
    $opts['value'] .= ":<a class='row-edit' for='$index'>Edit</a>";
    echo "<script>window.data[$index] = ".json_encode($opts['row']).";</script>";
    
    return $opts;
});