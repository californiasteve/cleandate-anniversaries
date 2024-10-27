<?php 
// Add menu page in admin
add_action('admin_menu', 'cleandate_anniversaries_admin_menu');
function cleandate_anniversaries_admin_menu() {
    add_menu_page(
        'Clean Date Anniversaries', 
        'Clean Date Anniversaries', 
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
    } elseif ($_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $date = sanitize_text_field($_POST['date']);
        $wpdb->update($table_name, ['name' => $name, 'date' => $date], ['id' => $id]);
    }

    // Check if editing an existing anniversary
    $edit_anniversary = null;
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        $edit_anniversary = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $edit_id");
    }

    // Handle sorting parameters
    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'date';
    $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
    $next_order = ($order === 'ASC') ? 'DESC' : 'ASC';

    // Fetch current anniversaries with sorting
    $anniversaries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY $sort $order");

    ?>
    <div class="wrap">
        <h1>Manage Clean Date Anniversaries</h1>
        <form method="post">
            <input type="hidden" name="action" value="<?php echo $edit_anniversary ? 'edit' : 'add'; ?>">
            <?php if ($edit_anniversary): ?>
                <input type="hidden" name="id" value="<?php echo esc_attr($edit_anniversary->id); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th>Name</th>
                    <td><input type="text" name="name" value="<?php echo esc_attr($edit_anniversary->name ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><input type="date" name="date" value="<?php echo esc_attr($edit_anniversary->date ?? ''); ?>" required></td>
                </tr>
            </table>
            <p>
                <input type="submit" value="<?php echo $edit_anniversary ? 'Update Clean Date' : 'Add Clean Date'; ?>" class="button button-primary">
                <?php if ($edit_anniversary): ?>
                    <a href="?page=cleandate-anniversaries" class="button button-secondary">Return to Add</a>
                <?php endif; ?>
            </p>
        </form>

        <h2>Existing Clean Date Anniversaries</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>
                        <a href="?page=cleandate-anniversaries&sort=name&order=<?php echo $sort === 'name' ? $next_order : 'ASC'; ?>">
                            Name <?php echo $sort === 'name' ? ($order === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?page=cleandate-anniversaries&sort=date&order=<?php echo $sort === 'date' ? $next_order : 'ASC'; ?>">
                            Date <?php echo $sort === 'date' ? ($order === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($anniversaries as $anniversary) { ?>
                <tr>
                    <td><?php echo esc_html($anniversary->name); ?></td>
                    <td><?php echo esc_html(date('F j, Y', strtotime($anniversary->date))); ?></td>
                    <td>
                        <a href="?page=cleandate-anniversaries&edit_id=<?php echo $anniversary->id; ?>" class="button button-secondary">Edit</a>
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
