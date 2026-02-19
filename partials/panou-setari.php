<?php
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;
$dashboard_path = get_template_directory() . '/dashboard/';

    switch ($user_roles[0]) {
    case 'profesor':
        include_once $dashboard_path . 'settings.php';
        break;
    case 'alumni':
        include_once $dashboard_path . 'alumni-settings.php';
        break;
    case 'non-teach':
        include_once $dashboard_path . 'non-teach-settings.php';
        break;
    case 'tutor':
        include_once $dashboard_path . 'settings.php';
        break;        
    case 'admin':
        include_once $dashboard_path . 'admin-setari.php';
        break;
    case 'administrator':
        include_once $dashboard_path . 'admin-setari.php';
        break;
    default:
        echo '<p class="text-gray-700">Rol necunoscut. Te rugăm să contactezi un administrator.</p>';
        break;
    }
?>