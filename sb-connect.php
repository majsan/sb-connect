<?php
/**
* Plugin Name: SB Connect
* Plugin URI: https://github.com/majsan/sb-connect
* Description: Show saldo in Systembolaget on your site
* Version: 1.0
* Author: Majsan
* Author URI: 
**/



function sb_connect_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "articles"; 
}


add_action('admin_menu', 'plugin_setup_menu');

function plugin_setup_menu() {
    add_menu_page( 'SBConnect', 'SBConnect', 'manage_options', 'sb-connect', 'init', '', 0 );
}

function init() {
   echo "<h1>SBConnect</h1>";
   echo "<h2>Poop</h2>";
}

