<?php
/**
 * Plugin Name: Worais Database Management
 * Plugin URI: https://github.com/worais/database-management
 * Description: Allows you to easily manage your WordPress database
 * Author: Morais Junior
 * Author URI: https://github.com/worais/
 * Version: 1.1.0
 * Requires at least: 5.6
 * Tested up to: 6.5
 * Text Domain: worais-database-management
 * Domain Path: /languages/
 * License: GPLv3 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;
define('WORAIS_DATABASE_URL', plugin_dir_url( __FILE__ ));
define('WORAIS_DATABASE_DIR', dirname( __FILE__ ));

require WORAIS_DATABASE_DIR . "/vendor/autoload.php";
require WORAIS_DATABASE_DIR . "/consts/options.php";
require WORAIS_DATABASE_DIR . "/filters/limit.php";
require WORAIS_DATABASE_DIR . "/filters/where.php";
require WORAIS_DATABASE_DIR . "/filters/columns.php";
require WORAIS_DATABASE_DIR . "/filters/columns-print.php";

use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Formatter;
use PhpMyAdmin\SqlParser\Statements\SelectStatement;
use PhpMyAdmin\SqlParser\Components\Expression;
use PhpMyAdmin\SqlParser\Components\Limit;

class WoraisDatabase{
    public static function install(){
        global $wpdb;

        $wpdb->query('CREATE TABLE IF NOT EXISTS `worais_database` (
            `slug` varchar(60) NOT NULL,
            `title` varchar(60) NOT NULL,
            `sql` TEXT NOT NULL,
            `status` int(11) NOT NULL,
            `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX USING BTREE(`datetime`)
        );');

        add_option( 'worais-login-protect' , WORAIS_DATABASE_CONFIGS );
    }

    public static function menu(){
		add_options_page(
			esc_html__( 'Database Management', 'worais-database' ),
			esc_html__( 'Database Management', 'worais-database' ),
			'manage_options',
			'worais-database',
			['WoraisDatabase', 'page']
		);
    }

    public static function page(){
        global $wpdb;

        include WORAIS_DATABASE_DIR . "/templates/index.php";
    }

    public static function scripts(){
        wp_enqueue_style('worais-database-style',  plugin_dir_url( __FILE__ ).'/assets/style.css');
        wp_enqueue_script('worais-database-editor', plugin_dir_url( __FILE__ ).'/assets/editor.js');
        wp_enqueue_script('worais-database-chart', plugin_dir_url( __FILE__ ).'/assets/chart.js');

        wp_enqueue_style('wp-codemirror');
        wp_enqueue_script('wp-codemirror');
    }

	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'worais-login-protect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}  

    public static function load_plugin_action_links($links){
      $settings_link = '<a href="' . admin_url('options-general.php?page=worais-login-protect') . '" title="Settings">Settings</a>';
  
      array_unshift($links, $settings_link);
  
      return $links;
    } 

    public static function update(){
        global $wpdb;

        if(!check_admin_referer('worais-database-query')){
            die();
        }

        $data    = json_decode(utf8_encode(base64_decode($_POST['data'])), true);
        $primary = json_decode(utf8_encode(base64_decode($_POST['primary'])), true);

        $wpdb->update($_POST['table'], $data, $primary);
        exit;
    }

    public static function query(){
        global $wpdb;

        if(!check_admin_referer('worais-database-query')){
            die();
        }

        $sql = base64_decode( esc_html(sanitize_text_field($_POST['sql'])) );

        if(empty($sql)){
            ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th scope="col">TABLE_NAME</th>
                        <th scope="col">TABLE_ROWS</th>
                        <th scope="col">TABLE_ENGINE</th>
                        <th scope="col">TABLE_SIZE</th>
                    </tr>
                </thead>
                <tbody>                               
                    <?php
                        global $wpdb;
                        $database = esc_html(sanitize_text_field(DB_NAME));
                        $tables = $wpdb->get_results("SELECT * FROM information_schema.tables WHERE table_schema = '$database'");            
                        foreach($tables as $table){
                            echo ($table->TABLE_ROWS == 0)? "<tr class='empty'>" : "<tr>";
                                echo "<td><a href='#query=".base64_encode("SELECT * FROM `$table->TABLE_NAME`;")."'>$table->TABLE_NAME</a></td>";
                                echo "<td>$table->TABLE_ROWS</td>";
                                echo "<td>$table->ENGINE</td>";
                                echo "<td>$table->DATA_LENGTH</td>";
                            echo "</tr>";
                        }
                    ?>                                
                </tbody>
            </table>
            <?php
        }

        $parser = new Parser($sql);

        $statement = $parser->statements[0];
        if( ($statement instanceof SelectStatement) ){            
            $table = $statement->from[0]->table;

            $columns_data = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
            $columns = [];
            foreach($columns_data as $row){
                $columns[$row->Field]['field'] = $row->Field;
                $columns[$row->Field]['type'] = $row->Type;
                $columns[$row->Field]['key'] = $row->Key;
                $columns[$row->Field]['active'] = true;
            }

            $query = apply_filters('worais-database-statement-select', [
                'statement' => $statement,
                'columns'   => $columns
            ]);

            $sql_builded = $query['statement']->build();

            $query['statement']->expr = [new Expression('COUNT(*)')];
            $query['statement']->limit = new Limit(1); 

            $sql_total   = $query['statement']->build();
            $total = $wpdb->get_var($sql_total);
            echo "<script>window.total = $total;</script>";
            echo "<script>window.sql = '".base64_encode($sql_builded)."';</script>";
            echo "<script>window.table = '$table';</script>";
            echo "<script>window.data = [];</script>";

            $wpdb->hide_errors(); 
            $rows = $wpdb->get_results( $sql_builded, ARRAY_A );
            if($wpdb->last_error !== ''):
                echo "<div id='error'>$wpdb->last_error</div>"; exit;
            endif;

            if(empty($rows)){
                echo "<div id='empty'></div>"; exit;
            }

            echo '<table class="widefat"><thead><tr>';
                foreach($rows[0] as $key => $value){
                    echo "<th class='filed-$key'>$key</th>";
                }
            echo '</tr></thead><tbody>';
            foreach($rows as $row){
                echo '<tr>';
                    foreach($row as $key => $value){
                        $class = "filed-$key";
                        if($columns[$key]['key'] == 'PRI'){ 
                            $class .= ' primary'; 

                            $value = apply_filters('worais-database-columns-print-primary', [
                                'column' => $columns[$key], 
                                'value'  => $value,
                                'row'    => $row
                            ])['value'];                            
                        };

                        $value = apply_filters('worais-database-columns-print', [
                            'column' => $columns[$key], 
                            'value' => $value
                        ])['value'];

                        $value = apply_filters("worais-database-columns-print-$table-$key", $value);                        

                        echo "<td class='$class'>$value</td>";
                    }                
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        exit;
    }
}

register_activation_hook( __FILE__, ['WoraisDatabase','install']);

add_action('init',               ['WoraisDatabase', 'scripts']);
add_action('admin_menu',         ['WoraisDatabase', 'menu']);
add_action('plugins_loaded',     ['WoraisDatabase', 'load_plugin_textdomain'] );
add_filter('plugin_action_links_' . plugin_basename(__FILE__), ['WoraisDatabase', 'load_plugin_action_links']);

add_action('wp_ajax_worais-database-query', ['WoraisDatabase', 'query']);
add_action('wp_ajax_worais-database-update', ['WoraisDatabase','update']);