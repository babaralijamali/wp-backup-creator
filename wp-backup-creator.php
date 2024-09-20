<?php
/*
Plugin Name: WP Backup Creator
Plugin URI: https://github.com/babaralijamali/wp-backup-creator
Description: A simple and fastest WordPress plugin to create backups of your site.
Version: 1.0
Author: Babar Ali Jamali
Author URI: https://www.facebook.com/babaralijamali.official or https://github.com/babaralijamali
License: GPL2
*/

defined('ABSPATH') or die('You cannot access this file.');

class WPBackupCreator {

    // Constructor to add hooks
    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
    }

    // Create menu for the plugin
    public function create_menu() {
        add_menu_page('Backup Creator', 'Backup Creator', 'manage_options', 'wp-backup-creator', array($this, 'backup_page'));
    }

    // Create the page layout
    public function backup_page() {
        echo '<div class="wrap">';
        echo '<h1>Backup Creator</h1>';
        echo '<form method="post" action="">';
        echo '<input type="submit" name="backup_now" class="button button-primary" value="Create Backup Now">';
        echo '</form>';

        if (isset($_POST['backup_now'])) {
            $this->create_backup();
        }

        echo '</div>';
    }

    // Function to create a backup of the database
    public function create_backup() {
        global $wpdb;
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

        $backup_file = WP_CONTENT_DIR . '/uploads/wp-backup-' . date('Y-m-d-H-i-s') . '.sql';

        $handle = fopen($backup_file, 'w+');
        if ($handle === false) {
            echo '<div class="error"><p>Failed to create backup file.</p></div>';
            return;
        }

        foreach ($tables as $table) {
            $table_name = $table[0];
            $create_table_query = $wpdb->get_results("SHOW CREATE TABLE $table_name", ARRAY_N);
            fwrite($handle, "\n\n" . $create_table_query[0][1] . ";\n\n");

            $rows = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_N);
            foreach ($rows as $row) {
                $row_data = array_map('addslashes', $row);
                $row_values = "('" . implode("', '", $row_data) . "')";
                fwrite($handle, "INSERT INTO $table_name VALUES $row_values;\n");
            }
        }

        fclose($handle);
        echo '<div class="updated"><p>Backup created successfully! File saved at: ' . $backup_file . '</p></div>';
    }
}

new WPBackupCreator();
