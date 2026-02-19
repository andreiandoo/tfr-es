<?php
// admin/counties.php

function edu_render_county_manager() {
    global $wpdb;
    $table = $wpdb->prefix . 'edu_counties';

    // Adăugare județ
    if (isset($_POST['action']) && $_POST['action'] === 'adauga_judet' && !empty($_POST['county_name'])) {
        $wpdb->insert($table, ['name' => sanitize_text_field($_POST['county_name'])]);
        echo '<div class="notice notice-success"><p>Județ adăugat.</p></div>';
    }

    // Ștergere județ
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
        echo '<div class="notice notice-success"><p>Județ șters.</p></div>';
    }

    // Formular adăugare
    echo '<form method="post" class="mb-4">';
    echo '<input type="hidden" name="action" value="adauga_judet">';
    echo '<input type="text" name="county_name" required placeholder="Nume județ" class="p-2 mr-2 border rounded">';
    echo '<button class="button button-primary">Adaugă județ</button>';
    echo '</form>';

    // Listă județe
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");
    echo '<table class="fixed wp-list-table widefat striped">';
    echo '<thead><tr><th>Nume</th><th>Acțiuni</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $del_url = admin_url("admin.php?page=edu-location-manager&tab=counties&delete={$row->id}");
        echo "<tr><td>{$row->name}</td><td><a href='$del_url' class='text-red-500'>Șterge</a></td></tr>";
    }
    echo '</tbody></table>';
}