<?php
// Add menu page in admin
add_action('admin_menu', 'cleandate_anniversaries_admin_menu');
function cleandate_anniversaries_admin_menu() {
    add_menu_page(
        'CleanDate Anniversaries', 
        'Recovery Anniversaries', 
        'manage_options', 
        'cleandate-anniversaries', 
        'cleandate_anniversaries_admin_page',
        'dashicons-calendar-alt',
        20
    );
}

// Admin page content
function cleandate_anniversaries_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "cleandate_anniversaries";

    // Handle form submission
    if ($_POST['action'] == 'add') {
        $name = sanitize_text_field($_POST['name']);
        $date = sanitize_text_field($_POST['date']);
        $wpdb->insert($table_name, ['name' => $name, 'date' => $date]);
    } elseif ($_POST['action'] == 'delete') {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, ['id' => $id]);
    }

    // Fetch current anniversaries
    $anniversaries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date ASC");

    ?>
    <div class="wrap">
        <h1>Manage Recovery Anniversaries</h1>
        <h4>To add recovery anniversaries to page use [cleandate_anniversaries shortcode.</h4>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <table class="form-table">
                <tr>
                    <th>Name</th>
                    <td><input type="text" name="name" required></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><input type="date" name="date" required></td>
                </tr>
            </table>
            <p><input type="submit" value="Add Anniversary" class="button button-primary"></p>
        </form>

        <h2>Existing Anniversaries</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($anniversaries as $anniversary) { ?>
                <tr>
                    <td><?php echo esc_html($anniversary->name); ?></td>
                    <td><?php echo esc_html(date('F j, Y', strtotime($anniversary->date))); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $anniversary->id; ?>">
                            <input type="submit" value="Delete" class="button button-secondary">
                        </form>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}