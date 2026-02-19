<?php
// ajax/load-cities.php

add_action('wp_ajax_edu_get_cities', function () {
    check_ajax_referer('edu_location_nonce', 'nonce');

    global $wpdb;
    $county_id = intval($_POST['county_id']);
    if (!$county_id) {
        echo '<option value="">Selectează județ mai întâi</option>';
        wp_die();
    }

    $cities = $wpdb->get_results($wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}edu_cities WHERE county_id = %d AND parent_city_id IS NULL ORDER BY name",
        $county_id
    ));

    if ($cities) {
        echo '<option value="">Selectează oraș</option>';
        foreach ($cities as $city) {
            echo "<option value='{$city->id}'>" . esc_html($city->name) . "</option>";
        }
    } else {
        echo '<option value="">Niciun oraș disponibil</option>';
    }

    wp_die();
});

add_action('wp_ajax_edu_get_villages', function () {
    check_ajax_referer('edu_location_nonce', 'nonce');

    global $wpdb;
    $city_id = intval($_POST['city_id']);
    if (!$city_id) {
        echo '<option value="">Selectează oraș mai întâi</option>';
        wp_die();
    }

    $villages = $wpdb->get_results($wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}edu_cities WHERE parent_city_id = %d ORDER BY name",
        $city_id
    ));

    if ($villages) {
        echo '<option value="">Selectează comună/sat (opțional)</option>';
        foreach ($villages as $village) {
            echo "<option value='{$village->id}'>" . esc_html($village->name) . "</option>";
        }
    } else {
        echo '<option value="">Nicio comună sau sat</option>';
    }

    wp_die();
});

add_action('wp_ajax_edu_get_parent_cities', function () {
    check_ajax_referer('edu_location_nonce', 'nonce');

    global $wpdb;
    $county_id = intval($_POST['county_id']);
    if (!$county_id) {
        echo '<option value="">Selectează județ mai întâi</option>';
        wp_die();
    }

    $cities = $wpdb->get_results($wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}edu_cities WHERE county_id = %d AND parent_city_id IS NULL ORDER BY name",
        $county_id
    ));

    if ($cities) {
        echo '<option value="">(opțional) Este sub localitatea...</option>';
        foreach ($cities as $city) {
            echo "<option value='{$city->id}'>" . esc_html($city->name) . "</option>";
        }
    } else {
        echo '<option value="">Nicio localitate disponibilă</option>';
    }

    wp_die();
});