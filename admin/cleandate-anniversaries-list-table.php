<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CleanDate_Anniversaries_List_Table extends WP_List_Table {

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . "cleandate_anniversaries";

        // Set pagination parameters
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Get sort and order
        $orderby = !empty($_GET['orderby']) ? $_GET['orderby'] : 'date';
        $order = !empty($_GET['order']) ? $_GET['order'] : 'asc';

        // Query the database
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $this->items = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_name 
            ORDER BY $orderby $order 
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        // Set pagination
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ]);
    }

    public function get_columns() {
        $columns = [
            'name' => 'Name',
            'date' => 'Date',
            'actions' => 'Actions'
        ];
        return $columns;
    }

    protected function column_name($item) {
        return esc_html($item->name);
    }

    protected function column_date($item) {
        return esc_html(date('F j, Y', strtotime($item->date)));
    }

    protected function column_actions($item) {
        return sprintf(
            '<form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="%d">
                <input type="submit" value="Delete" class="button button-secondary">
            </form>',
            $item->id
        );
    }

    protected function get_sortable_columns() {
        return [
            'name' => ['name', true],
            'date' => ['date', true]
        ];
    }

    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'name':
            case 'date':
                return $item->$column_name;
            default:
                return print_r($item, true); // Show the whole array for troubleshooting purposes
        }
    }
}
