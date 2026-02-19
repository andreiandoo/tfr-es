<?php
// admin/cities.php

function edu_render_city_manager() {
    global $wpdb;
    $county_table = $wpdb->prefix . 'edu_counties';
    $city_table = $wpdb->prefix . 'edu_cities';

    // Adăugare localitate
    if (
        isset($_POST['action']) &&
        $_POST['action'] === 'adauga_oras' &&
        !empty($_POST['city_name']) &&
        !empty($_POST['county_id'])
    ) {
        $wpdb->insert($city_table, [
            'name' => sanitize_text_field($_POST['city_name']),
            'county_id' => intval($_POST['county_id']),
            'parent_city_id' => !empty($_POST['parent_city_id']) ? intval($_POST['parent_city_id']) : null
        ]);
        echo '<div class="notice notice-success"><p>Localitate adăugată.</p></div>';
    }

    // Ștergere
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $wpdb->delete($city_table, ['id' => intval($_GET['delete'])]);
        echo '<div class="notice notice-success"><p>Localitate ștearsă.</p></div>';
    }

    $counties = $wpdb->get_results("SELECT * FROM $county_table ORDER BY name ASC");

    echo '<form method="post" class="mb-4">';
    echo '<input type="hidden" name="action" value="adauga_oras">';
    echo '<select name="county_id" id="edu_county" required class="p-2 mr-2 border rounded">';
    echo '<option value="">Selectează județ</option>';
    foreach ($counties as $county) {
        echo "<option value='{$county->id}'>{$county->name}</option>";
    }
    echo '</select>';
    echo '<input type="text" name="city_name" required placeholder="Nume localitate" class="p-2 mr-2 border rounded">';

    // dropdown gol inițial, populat dinamic
    echo '<select name="parent_city_id" id="parent_city_id" class="p-2 mr-2 border rounded">';
    echo '<option value="">(opțional) Este sub localitatea...</option>';
    echo '</select>';

    echo '<button class="button button-primary">Adaugă localitate</button>';
    echo '</form>';

    // Listă localități
    $rows = $wpdb->get_results("SELECT c.id, c.name as city_name, ct.name as county_name, p.name as parent_name
        FROM $city_table c
        JOIN $county_table ct ON c.county_id = ct.id
        LEFT JOIN $city_table p ON c.parent_city_id = p.id
        ORDER BY ct.name, c.name");

    echo '<table class="fixed wp-list-table widefat striped">';
    echo '<thead><tr><th>Județ</th><th>Localitate</th><th>Sub localitatea</th><th>Acțiuni</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $del_url = admin_url("admin.php?page=edu-location-manager&tab=cities&delete={$row->id}");
        $parent = $row->parent_name ? $row->parent_name : '-';
        echo "<tr><td>{$row->county_name}</td><td>{$row->city_name}</td><td>{$parent}</td><td><a href='$del_url' class='text-red-500'>Șterge</a></td></tr>";
    }
    echo '</tbody></table>';
}
