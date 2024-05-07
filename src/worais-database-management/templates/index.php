<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="worais-database">
    <div id="editor">
        <textarea id="sql"></textarea>  
        <?php wp_nonce_field('worais-database-query'); ?>
        <button class="btn-lg btn-primary" id="btn-sql-run"><span class="spinner is-active"></span><?php esc_html_e('Run Query', 'worais-database-management' ); ?></button>    
        
        <input type="hidden" name="colunms" value="">   
        <div id="tools"></div>     
        <div id="where-div"></div>
        <div id="colunms-div"></div>

        <div id="row-form"></div>
    </div>
    <div id="results"></div>
    <div id="footer" class="worais-footer">
        <p id="footer-left" class="alignleft">        
		    <a href="https://github.com/worais/database-management" target="_blank">Database Management</a> <?php esc_html_e('is developed and maintained by', 'worais-database-management' ); ?> <a href="https://github.com/worais/" target="_blank">Worais</a>
        </p>
    </div>    
</div>