<?php
/**
 * Plugin Name: CleanDate Anniversaries
 * Description: Display cleandate anniversaries via shortcode, with an admin page to manage anniversaries.
 * Version: 1.0
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Author: California Steve
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Activation hook to create custom database table
register_activation_hook(__FILE__, 'cleandate_anniversaries_install');
function cleandate_anniversaries_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "cleandate_anniversaries";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        date date NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Enqueue script for frontend tooltips
function cleandate_anniversaries_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('cleandate-tooltip', plugins_url('/js/tooltip.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style('cleandate-tooltip-css', plugins_url('/css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'cleandate_anniversaries_enqueue_scripts');

// Include the admin page
include plugin_dir_path(__FILE__) . 'admin/cleandate-anniversaries-admin.php';

// Shortcode to display anniversaries
function cleandate_anniversaries_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . "cleandate_anniversaries";
    
    $today = date('Y-m-d');
    $month_day_today = date('m-d');

    // Fetch anniversaries sorted by month and day, starting from today forward
    $results = $wpdb->get_results("
        SELECT name, date 
        FROM $table_name 
        ORDER BY 
            CASE WHEN DATE_FORMAT(date, '%m-%d') >= '$month_day_today' THEN 1 ELSE 2 END,
            DATE_FORMAT(date, '%m-%d') ASC
    ");

    if (!$results) return "<p>No clean date anniversaries found.</p>";

    $output = '<ul class="cleandate-anniversaries">';
    foreach ($results as $row) {
        $name = esc_html($row->name);
        $anniversary_date = date('F j', strtotime($row->date));
        $years = date('Y') - date('Y', strtotime($row->date));

        // Tooltip with years
        $output .= "<li><span class='anniversary-tooltip' title='$years years clean'>$name ($anniversary_date)</span></li>";
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('cleandate_anniversaries', 'cleandate_anniversaries_shortcode');
