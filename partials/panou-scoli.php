<?php
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;
$dashboard_path = get_template_directory() . '/dashboard/';

    switch ($user_roles[0]) {      
    case 'admin':
        include_once $dashboard_path . 'admin-scoli.php';
        break;
    case 'administrator':
        include_once $dashboard_path . 'admin-scoli.php';
        break;
    default:
        echo '<p class="text-gray-700">Rol necunoscut. Te rugăm să contactezi un administrator.</p>';
        break;
    }
?>