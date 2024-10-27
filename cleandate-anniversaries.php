<?php
/**
 * Plugin Name: Clean Date Anniversaries
 * Description: Display cleandate anniversaries via shortcode, with an admin page to manage anniversaries.
 * Version: 1.0
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * Author: California Steve
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 */

 // Security Check
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

// Enqueue scripts for tooltips
function cleandate_anniversaries_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('cleandate-tooltip', plugins_url('/js/tooltip.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style('cleandate-tooltip-css', plugins_url('/css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'cleandate_anniversaries_enqueue_scripts');

// Include the admin page
include plugin_dir_path(__FILE__) . 'admin/cleandate-anniversaries-admin.php';

// Shortcode to display anniversaries in calendar or list view
function cleandate_anniversaries_shortcode($atts) {
    global $wpdb;
    $table_name = $wpdb->prefix . "cleandate_anniversaries";

    // Shortcode attributes
    $atts = shortcode_atts(array(
        'view' => 'calendar',
        'limit' => -1  // -1 means no limit
    ), $atts);

    // Current month for calendar view
    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

    if ($atts['view'] == 'list') {
        // Get anniversaries from today onward
        $results = $wpdb->get_results("
            SELECT name, date 
            FROM $table_name 
            ORDER BY 
        CASE 
            WHEN DATE_FORMAT(date, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d') THEN 0 
        ELSE 1 
    END,
    DATE_FORMAT(date, '%m-%d') ASC;
        ");
        return cleandate_anniversaries_list_view($results, intval($atts['limit']));
    } else {
        // Get anniversaries for the calendar month view
        $results = $wpdb->get_results("
            SELECT name, date 
            FROM $table_name 
            WHERE MONTH(date) = $month 
            ORDER BY DAY(date) ASC
        ");
        return cleandate_anniversaries_calendar_view($results, $month);
    }
}
add_shortcode('cleandate_anniversaries', 'cleandate_anniversaries_shortcode');

// Calendar view generation
function cleandate_anniversaries_calendar_view($results, $month) {
    // Start calendar
    $output = '<div class="cleandate-calendar">';
    $prev_month = $month == 1 ? 12 : $month - 1;
    $next_month = $month == 12 ? 1 : $month + 1;

    // Month navigation
    $output .= '<div class="calendar-navigation">';
    $output .= '<a href="?month=' . $prev_month . '">&laquo; Previous</a>';
    $output .= '<span class="calendar-month-year">' . date("F", mktime(0, 0, 0, $month, 1)) . '</span>';
    $output .= '<a href="?month=' . $next_month . '">Next &raquo;</a>';
    $output .= '</div>';

    // Days of the week
    $output .= '<div class="calendar-grid">';
    $days_of_week = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($days_of_week as $day) {
        $output .= '<div class="calendar-day-header">' . $day . '</div>';
    }

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, date('Y'));
    $first_day_of_month = date('w', mktime(0, 0, 0, $month, 1));

    // Render empty cells
    for ($i = 0; $i < $first_day_of_month; $i++) {
        $output .= '<div class="calendar-day empty"></div>';
    }

    $current_day = date('j');
    for ($day = 1; $day <= $days_in_month; $day++) {
        $isToday = ($day == $current_day && $month == date('n'));
        $day_class = $isToday ? 'current-day' : '';

        $anniversary_names = [];
        foreach ($results as $row) {
            if (date('j', strtotime($row->date)) == $day) {
                $years = date('Y') - date('Y', strtotime($row->date));
                $anniversary_names[] = esc_html($row->name) . " ($years years)";
            }
        }

        $output .= "<div class='calendar-day $day_class'>";
        $output .= "<div class='day-number'>" . $day . "</div>";
        if ($anniversary_names) {
            $output .= '<div class="anniversary-names">' . implode(', ', $anniversary_names) . '</div>';
        }
        $output .= '</div>';
    }

    $output .= '</div></div>';

    return $output;
}

// List view generation with optional limit
function cleandate_anniversaries_list_view($results, $limit) {
    if (empty($results)) {
        return "<p>No clean date anniversaries found.</p>";
    }

    // Limit results if the limit is set and greater than zero
    $limited_results = $limit > 0 ? array_slice($results, 0, $limit) : $results;

    $output = '<ul class="cleandate-anniversaries">';
    foreach ($limited_results as $row) {
        $name = esc_html($row->name);
        $anniversary_date = date('F j', strtotime($row->date));
        $years = date('Y') - date('Y', strtotime($row->date));

        $output .= "<li><span class='anniversary-tooltip' title='$years years clean'>$name ($anniversary_date)</span></li>";
    }
    $output .= '</ul>';

    return $output;
}

?>
