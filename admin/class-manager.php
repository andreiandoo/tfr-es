<?php
// admin/class-manager.php


// ===========================================
// Admin menu: Classes
// ===========================================
add_action('admin_menu', function() {
    return; // nu mai înregistrăm pagina în meniu
});
// add_action('admin_menu', function() {
//     add_menu_page(
//         'Gestionare Clase',
//         'Clase',
//         'manage_edu_classes',
//         'edu-classes',
//         'edu_render_class_manager',
//         'dashicons-welcome-learn-more',
//         35
//     );
// });

function edu_render_class_manager() {
    global $wpdb;
    $table = $wpdb->prefix . 'edu_classes';

    // Handle add/edit/delete via POST/GET
    if (isset($_POST['edu_save_class'])) {
        check_admin_referer('edu_class_form');
        $data = [
            'school_id'  => intval($_POST['school_id']),
            'teacher_id' => intval($_POST['teacher_id']),
            'name'       => sanitize_text_field($_POST['class_name']),
            'level'      => sanitize_text_field($_POST['class_level']),
        ];
        if (!empty($_POST['class_id'])) {
            $wpdb->update($table, $data, ['id' => intval($_POST['class_id'])]);
        } else {
            $wpdb->insert($table, $data);
        }
        echo '<div class="notice notice-success is-dismissible"><p>Clasa salvată.</p></div>';
    }
    if (isset($_GET['delete_class'])) {
        check_admin_referer('delete_class_' . intval($_GET['delete_class']));
        $wpdb->delete($table, ['id' => intval($_GET['delete_class'])]);
        echo '<div class="notice notice-success is-dismissible"><p>Clasa ștearsă.</p></div>';
    }

    // Fetch for edit
    $edit_class = null;
    if (isset($_GET['edit_class'])) {
        $edit_class = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['edit_class']))
        );
    }

    // Determine current user role
    $current_user = wp_get_current_user();
    $user_roles   = (array) $current_user->roles;
    $is_profesor  = in_array('profesor', $user_roles, true);

    // For dropdowns
    $profesori = get_users(['role' => 'profesor']);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Gestionare Clase</h1>
        <a href="#" id="toggleAddClassForm" class="page-title-action">Adaugă clasă</a>

        <!-- Add/Edit Form -->
        <div id="classFormContainer" style="display:none; margin-top:20px;">
            <form method="post" id="eduClassForm" class="max-w-lg p-4 bg-white rounded shadow">
            <?php wp_nonce_field('edu_class_form'); ?>
            <?php if ($edit_class): ?>
                <input type="hidden" name="class_id" value="<?php echo esc_attr($edit_class->id); ?>">
            <?php endif; ?>

            <div class="flex flex-wrap items-end w-full gap-4 mb-4">
                <div class="flex items-center w-1/4 gap-x-4">
                    <div class="flex flex-col w-full">
                        <label class="block mb-1 font-medium">Școală</label>
                        <select name="school_id" id="class_school" class="w-full p-2 mb-4 border rounded" required data-selected="<?php echo esc_attr($edit_class->school_id ?? ''); ?>">
                            <option value="">Selectează școala...</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-x-4">
                        <p id="class_city_display" class="text-gray-700"></p>
                        <p id="class_county_display" class="text-gray-700"></p>
                    </div>
                </div>
                <?php if (! $is_profesor): // admin or tutor ?>
                    <div class="flex flex-col w-1/6">
                        <div class="flex flex-col">
                            <label class="block mb-1 font-medium">Profesor</label>
                            <select 
                                name="teacher_id" 
                                id="class_teacher" 
                                class="w-full p-2 mb-4 border rounded"
                                data-selected="<?php echo esc_attr( $edit_class->teacher_id ?? '' ); ?>"
                                required
                            >
                                <?php if ( $edit_class && $edit_class->teacher_id ) : 
                                $t = get_user_by('ID', $edit_class->teacher_id );
                                ?>
                                <option value="<?php echo $t->ID; ?>" selected>
                                    <?php echo esc_html( $t->display_name ); ?>
                                </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                <?php else: // profesor ?>
                    <input type="hidden" name="teacher_id" value="<?php echo esc_attr($current_user->ID); ?>">
                <?php endif; ?>
                <div class="flex flex-col w-1/6">
                    <div class="flex flex-col">
                        <label class="block mb-1 font-medium">Nivel</label>
                        <select 
                            name="class_level" 
                            id="class_level" 
                            class="w-full p-2 border rounded" 
                            required
                            >
                            <option value="Prescolar"   <?php selected($edit_class->level ?? '', 'Prescolar'); ?>>Prescolar</option>
                            <option value="Primar Mic"   <?php selected($edit_class->level ?? '', 'Primar Mic'); ?>>Primar Mic</option>
                            <option value="Primar Mare"   <?php selected($edit_class->level ?? '', 'Primar Mare'); ?>>Primar Mare</option>
                            <option value="Gimnazial"<?php selected($edit_class->level ?? '', 'Gimnazial'); ?>>Gimnazial</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col w-1/6">
                    <div class="flex flex-col">
                        <label class="block mb-1 font-medium">Numele clasei</label>
                        <input type="text" name="class_name" class="w-full p-2 border rounded" required value="<?php echo esc_attr($edit_class->name ?? ''); ?>">
                    </div>
                </div>
                <div class="flex items-center w-1/6 gap-x-4">
                    <button type="submit" name="edu_save_class" class="button button-primary">
                        <?php echo $edit_class ? 'Salvează modificări' : 'Salvează'; ?>
                    </button>
                    <button type="button" id="cancelClassForm" class="ml-2 button">Anulează</button>
                </div>
            </div>            
            </form>
        </div>

        <!-- Listing -->
        <h2 class="mt-8">Toate clasele</h2>
        <?php
        // Build listing query
        $base_select = "c.id, c.name, c.level, s.name AS school_name, u.display_name AS teacher_name";
        $base_join   = "JOIN {$wpdb->prefix}edu_schools s ON c.school_id = s.id 
                        JOIN {$wpdb->prefix}users u      ON c.teacher_id = u.ID";

        if (!$is_profesor) {
            // Admin/Tutor see city & county
            $base_select .= ", city.name AS city_name, county.name AS county_name";
            $base_join   .= "
            JOIN {$wpdb->prefix}edu_cities city   ON s.city_id      = city.id
            JOIN {$wpdb->prefix}edu_counties county ON city.county_id = county.id";
        }

        $classes = $wpdb->get_results(
            "SELECT $base_select FROM $table c $base_join
            ORDER BY s.name, c.level, c.name"
        );
        ?>
        <table class="mt-4 wp-list-table widefat striped">
            <thead>
            <tr>
                <th>Școală</th>
                <?php if (!$is_profesor): ?>
                <th>Localitate</th><th>Județ</th>
                <?php endif; ?>
                <th>Profesor</th><th>Clasa</th><th>Nivel</th><th>Acțiuni</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($classes as $cls): ?>
                <tr>
                <td><?php echo esc_html($cls->school_name); ?></td>
                <?php if (!$is_profesor): ?>
                    <td><?php echo esc_html($cls->city_name); ?></td>
                    <td><?php echo esc_html($cls->county_name); ?></td>
                <?php endif; ?>
                <td><?php echo esc_html($cls->teacher_name); ?></td>
                <td><?php echo esc_html($cls->name); ?></td>
                <td><?php echo esc_html($cls->level); ?></td>
                <td>
                    <a href="<?php echo admin_url("admin.php?page=edu-classes&edit_class={$cls->id}"); ?>" class="button small">Editează</a>
                    <a href="<?php echo wp_nonce_url(admin_url("admin.php?page=edu-classes&delete_class={$cls->id}"), 'delete_class_' . $cls->id); ?>" onclick="return confirm('Sigur dorești să ștergi această clasă?')" class="ml-2 button small button-danger">Șterge</a>
                </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
