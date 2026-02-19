<?php
// admin/menu.php

require_once plugin_dir_path(__FILE__) . '../includes/helpers.php';

function edu_register_location_admin_menu() {
    add_menu_page(
        'Administrare locații',
        'Locații',
        'manage_options',
        'edu-location-manager',
        'edu_render_location_page',
        'dashicons-location',
        30
    );
}
add_action('admin_menu', 'edu_register_location_admin_menu');

function edu_render_location_page() {
    $tab = $_GET['tab'] ?? 'counties';

    echo '<div class="wrap">';
    echo '<h1 class="mb-4 text-2xl font-bold">Administrare locații</h1>';
    echo '<nav class="nav-tab-wrapper">';
    echo '<a href="?page=edu-location-manager&tab=counties" class="nav-tab ' . ($tab == 'counties' ? 'nav-tab-active' : '') . '">Județe</a>';
    echo '<a href="?page=edu-location-manager&tab=cities" class="nav-tab ' . ($tab == 'cities' ? 'nav-tab-active' : '') . '">Orașe</a>';
    echo '<a href="?page=edu-location-manager&tab=schools" class="nav-tab ' . ($tab == 'schools' ? 'nav-tab-active' : '') . '">Școli</a>';
    echo '<a href="?page=edu-location-manager&tab=import" class="nav-tab ' . ($tab == 'import' ? 'nav-tab-active' : '') . '">Import CSV</a>';
    echo '</nav><div class="mt-4">';

    switch ($tab) {
        case 'cities':
            edu_render_city_manager();
            break;
        case 'schools':
            edu_render_school_manager();
            break;
        case 'import':
            edu_render_import_interface();
            break;
        case 'counties':
        default:
            edu_render_county_manager();
            break;
    }

    echo '</div></div>';
}

//require_once plugin_dir_path(__FILE__) . 'user-manager.php';
require_once plugin_dir_path(__FILE__) . 'user-listing.php';
function edu_register_user_listing_page() {
    add_menu_page(
        'Gestionare utilizatori',
        'Utilizatori EDU',
        'manage_options',
        'edu-users',
        'edu_render_user_listing',
        'dashicons-admin-users',
        25
    );
}
add_action('admin_menu', 'edu_register_user_listing_page');


// — Generații (nou) —
require_once plugin_dir_path(__FILE__) . 'generation-manager.php';
function edu_register_generations_page() {
    add_menu_page(
        'Generații',
        'Generații',
        'manage_options',
        'edu-generations',
        'edu_render_generation_manager',
        'dashicons-groups',
        35
    );
}
add_action('admin_menu', 'edu_register_generations_page');