<?php
// admin/roles.php

/**
 * Register EDU custom roles.
 */


function edu_register_custom_roles() {
    $caps = [
      'read'               => true,
      'manage_edu_classes' => true,
    ];
    

    add_role( 'profesor',  'Profesor',  $caps );
    add_role( 'tutor',     'Tutor',     $caps );
    add_role( 'alumni',    'Alumni',    $caps );
    add_role( 'non-teach', 'Non-Teach', $caps );

    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('manage_edu_classes');
    }
}
add_action( 'init', 'edu_register_custom_roles' );

/**
 * Clean up roles on plugin deactivation.
 */
function edu_remove_custom_roles() {
    remove_role( 'profesor' );
    remove_role( 'tutor' );
    remove_role( 'alumni' );
    remove_role( 'non-teach' );

    $admin = get_role('administrator');
    if ($admin) {
        $admin->remove_cap('manage_edu_classes');
    }
}
register_deactivation_hook( __FILE__, 'edu_remove_custom_roles' );
