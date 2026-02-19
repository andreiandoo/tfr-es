<?php
// admin/student-manager.php

// — Top-level: Elevi —
add_action('admin_menu', function() {
    add_menu_page(
        'Gestionare Elevi',
        'Elevi',
        'manage_edu_classes',
        'edu-students',
        'edu_render_student_manager',
        'dashicons-id',
        33
    );
});

function edu_render_student_manager() {
    if ( ! current_user_can('manage_edu_classes') ) {
        wp_die( 'Nu ai permisiuni pentru a accesa această pagină.' );
    }

    global $wpdb;

    $students_table  = $wpdb->prefix . 'edu_students';
    $classes_table   = $wpdb->prefix . 'edu_classes';
    $schools_table   = $wpdb->prefix . 'edu_schools';
    $cities_table    = $wpdb->prefix . 'edu_cities';
    $counties_table  = $wpdb->prefix . 'edu_counties';
    $generations_tbl = $wpdb->prefix . 'edu_generations';

    // — Ștergere elev (acțiune rapidă)
    if ( isset($_GET['delete_student'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'edu_delete_student') ) {
        $sid = intval($_GET['delete_student']);
        if ($sid > 0) {
            $wpdb->delete($students_table, ['id' => $sid]);
            echo '<div class="notice notice-success is-dismissible"><p>Elevul a fost șters.</p></div>';
        }
    }

    // — Salvare edit elev (minim: prenume, nume, gen, vârstă, generație)
    if ( isset($_POST['update_student']) && check_admin_referer('edu_update_student') ) {
        $sid = intval($_POST['student_id'] ?? 0);
        if ($sid > 0) {
            $data = [
                'first_name'    => sanitize_text_field($_POST['first_name'] ?? ''),
                'last_name'     => sanitize_text_field($_POST['last_name']  ?? ''),
                'gender'        => sanitize_text_field($_POST['gender']     ?? ''),
                'age'           => ($_POST['age'] === '' ? null : intval($_POST['age'])),
                'generation_id' => ($_POST['generation_id'] === '' ? null : intval($_POST['generation_id'])),
            ];
            $wpdb->update($students_table, $data, ['id' => $sid]);
            echo '<div class="notice notice-success is-dismissible"><p>Datele elevului au fost actualizate.</p></div>';
        }
    }

    // — Filtre & search (GET)
    $filter_prof   = isset($_GET['professor_id'])  ? intval($_GET['professor_id']) : 0;
    $filter_gen    = isset($_GET['generation_id']) ? intval($_GET['generation_id']) : 0;
    $filter_county = isset($_GET['county_id'])     ? intval($_GET['county_id'])     : 0;
    $filter_city   = isset($_GET['city_id'])       ? intval($_GET['city_id'])       : 0;
    $q             = isset($_GET['q'])             ? trim(sanitize_text_field($_GET['q'])) : '';

    // — Liste pentru filtre
    $profesori = get_users(['role' => 'profesor', 'orderby' => 'display_name', 'order' => 'ASC']);
    $counties  = $wpdb->get_results("SELECT id, name FROM {$counties_table} ORDER BY name");
    $cities    = $filter_county
        ? $wpdb->get_results($wpdb->prepare("SELECT id, name FROM {$cities_table} WHERE county_id=%d ORDER BY name", $filter_county))
        : $wpdb->get_results("SELECT id, name FROM {$cities_table} ORDER BY name");

    // — Edit: încărcare rând pentru formularul de edit
    $edit_student = null;
    if ( isset($_GET['edit_student']) ) {
        $sid = intval($_GET['edit_student']);
        if ($sid > 0) {
            $edit_student = $wpdb->get_row($wpdb->prepare("
                SELECT st.*
                FROM {$students_table} st
                WHERE st.id = %d
            ", $sid));
        }
    }

    // — WHERE dinamic
    $where   = [];
    $params  = [];

    if ($filter_prof > 0) {
        // profesor fie din generație, fie (fallback) din clasă
        $where[] = "(g.professor_id = %d OR c.teacher_id = %d)";
        $params[] = $filter_prof;
        $params[] = $filter_prof;
    }
    if ($filter_gen > 0) {
        $where[]  = "st.generation_id = %d";
        $params[] = $filter_gen;
    }
    if ($filter_county > 0) {
        $where[]  = "county.id = %d";
        $params[] = $filter_county;
    }
    if ($filter_city > 0) {
        $where[]  = "city.id = %d";
        $params[] = $filter_city;
    }
    if ($q !== '') {
        $like = '%' . $wpdb->esc_like($q) . '%';
        $where[]  = "(CONCAT_WS(' ', st.first_name, st.last_name) LIKE %s OR s.name LIKE %s)";
        $params[] = $like;
        $params[] = $like;
    }

    // — Query listare globală elevi (compat: generații + clase)
    $sql = "
        SELECT
            st.id,
            st.first_name,
            st.last_name,
            st.gender,
            st.age,
            st.generation_id,

            g.name               AS generation_name,
            g.level              AS generation_level,
            g.class_labels_json  AS generation_labels_json,
            g.class_label        AS generation_label,

            COALESCE(u_g.display_name, u_c.display_name) AS professor_name,
            s.name   AS school_name,
            city.name AS city_name,
            county.name AS county_name
        FROM {$students_table} st
        LEFT JOIN {$generations_tbl} g    ON g.id = st.generation_id
        LEFT JOIN {$classes_table}    c    ON c.id = st.class_id
        LEFT JOIN {$wpdb->users}      u_g  ON u_g.ID = g.professor_id
        LEFT JOIN {$wpdb->users}      u_c  ON u_c.ID = c.teacher_id
        LEFT JOIN {$schools_table}    s    ON s.id = c.school_id
        LEFT JOIN {$cities_table}     city ON city.id = s.city_id
        LEFT JOIN {$counties_table}   county ON county.id = city.county_id
        " . ( $where ? "WHERE " . implode(' AND ', $where) : '' ) . "
        ORDER BY st.last_name, st.first_name
    ";

    $students = $params ? $wpdb->get_results($wpdb->prepare($sql, $params)) : $wpdb->get_results($sql);

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Elevi</h1>

        <!-- Filtre -->
        <form method="get" class="mb-4" style="margin: 12px 0 18px;">
            <input type="hidden" name="page" value="edu-students">

            <label style="margin-right:6px;"><strong>Profesor:</strong></label>
            <select name="professor_id" class="p-2 border rounded" style="min-width:200px; margin-right:10px;">
                <option value="">Toți profesorii</option>
                <?php foreach ($profesori as $p): ?>
                    <option value="<?php echo (int)$p->ID; ?>" <?php selected($filter_prof, (int)$p->ID); ?>>
                        <?php echo esc_html($p->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="margin:0 6px;"><strong>ID generație:</strong></label>
            <input type="number" name="generation_id" value="<?php echo esc_attr($filter_gen ?: ''); ?>" class="p-2 border rounded" style="width:120px; margin-right:10px;">

            <label style="margin:0 6px;"><strong>Județ:</strong></label>
            <select name="county_id" id="filter_county" class="p-2 border rounded" style="min-width:180px; margin-right:10px;">
                <option value="">Toate județele</option>
                <?php foreach ($counties as $ct): ?>
                    <option value="<?php echo (int)$ct->id; ?>" <?php selected($filter_county, (int)$ct->id); ?>>
                        <?php echo esc_html($ct->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="margin:0 6px;"><strong>Oraș:</strong></label>
            <select name="city_id" id="filter_city" data-selected="<?php echo esc_attr($filter_city ?: ''); ?>" class="p-2 border rounded" style="min-width:180px; margin-right:10px;">
                <option value="">Toate orașele</option>
                <?php foreach ($cities as $cty): ?>
                    <option value="<?php echo (int)$cty->id; ?>" <?php selected($filter_city, (int)$cty->id); ?>>
                        <?php echo esc_html($cty->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label style="margin:0 6px;"><strong>Căutare:</strong></label>
            <input type="text" name="q" value="<?php echo esc_attr($q); ?>" placeholder="nume elev sau nume școală" class="p-2 border rounded" style="min-width:240px; margin-right:6px;">

            <button class="button button-primary">Filtrează</button>
            <a class="button" style="margin-left:6px;" href="<?php echo esc_url( admin_url('admin.php?page=edu-students') ); ?>">Reset</a>
        </form>

        <!-- Formular edit (simplu) când e selectat un elev -->
        <?php if ($edit_student): ?>
            <form method="post" class="card" style="padding:16px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; margin: 10px 0 20px;">
                <?php wp_nonce_field('edu_update_student'); ?>
                <input type="hidden" name="update_student" value="1">
                <input type="hidden" name="student_id" value="<?php echo (int)$edit_student->id; ?>">
                <h2 style="margin-top:0;">Editează elev (#<?php echo (int)$edit_student->id; ?>)</h2>
                <div style="display:grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap:12px;">
                    <div>
                        <label class="block mb-1"><strong>Prenume</strong></label>
                        <input type="text" name="first_name" class="regular-text" value="<?php echo esc_attr($edit_student->first_name); ?>">
                    </div>
                    <div>
                        <label class="block mb-1"><strong>Nume</strong></label>
                        <input type="text" name="last_name" class="regular-text" value="<?php echo esc_attr($edit_student->last_name); ?>">
                    </div>
                    <div>
                        <label class="block mb-1"><strong>Gen</strong></label>
                        <select name="gender">
                            <option value=""></option>
                            <option value="M" <?php selected($edit_student->gender, 'M'); ?>>M</option>
                            <option value="F" <?php selected($edit_student->gender, 'F'); ?>>F</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1"><strong>Vârstă</strong></label>
                        <input type="number" name="age" min="1" class="small-text" value="<?php echo esc_attr($edit_student->age); ?>">
                    </div>
                    <div>
                        <label class="block mb-1"><strong>ID generație</strong></label>
                        <input type="number" name="generation_id" class="small-text" value="<?php echo esc_attr($edit_student->generation_id); ?>">
                        <p class="description">Lasă gol pentru niciuna.</p>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <button class="button button-primary">Salvează</button>
                    <a class="button" href="<?php echo esc_url( admin_url('admin.php?page=edu-students') ); ?>" style="margin-left:8px;">Renunță</a>
                </div>
            </form>
        <?php endif; ?>

        <?php
            $___ajax_url = admin_url('admin-ajax.php');
            $___nonce    = wp_create_nonce('edu_nonce');
        ?>

        <script>
            jQuery(function($){
            const $county = $('#filter_county');
            const $city   = $('#filter_city');
            const selectedCity = $city.data('selected') ? String($city.data('selected')) : '';

            function fillCities(cities, keepSelected=false) {
                const currentCounty = $county.val() || '';
                const selected = keepSelected ? (selectedCity || '') : ($city.val() || '');
                $city.empty();
                $city.append(new Option('Toate orașele', ''));
                (cities || []).forEach(function(c){
                const opt = new Option(c.name, String(c.id));
                if (selected && String(c.id) === selected) opt.selected = true;
                $city.append(opt);
                });
            }

            function fetchCities() {
                const countyId = $county.val() || '';
                $.post('<?php echo esc_js($___ajax_url); ?>', {
                    action: 'edu_cities_by_county',
                    nonce: '<?php echo esc_js($___nonce); ?>',
                    county_id: countyId
                }).done(function(resp){
                    if (resp && resp.success) {
                    fillCities(resp.data.cities, true);
                    }
                });
            }

            // la schimbarea județului, re-încarcă orașele
            $county.on('change', function(){
                // resetăm selecția orașului înainte de fetch
                $city.val('');
                fetchCities();
            });

            // inițial: dacă e selectat un județ, populăm orașele pentru el și încercăm să păstrăm orașul selectat
            if ($county.val()) {
                fetchCities();
            } else {
                $city.empty();
                $city.append(new Option('Toate orașele', ''));
            }
            });
        </script>

        <!-- Tabel listare -->
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Prenume</th>
                    <th>Nume</th>
                    <th>Gen</th>
                    <th>Vârstă</th>
                    <th>Profesor</th>
                    <th>Școală</th>
                    <th>Oraș</th>
                    <th>Județ</th>
                    <th>Generație</th>
                    <th style="width:140px;">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($students): foreach ($students as $st): ?>
                <tr>
                    <td><?php echo esc_html($st->first_name); ?></td>
                    <td><?php echo esc_html($st->last_name); ?></td>
                    <td><?php echo esc_html($st->gender); ?></td>
                    <td><?php echo esc_html($st->age); ?></td>
                    <td>
                        <?php if (!empty($st->professor_id)): ?>
                            <a href="<?php echo esc_url( admin_url('user-edit.php?user_id='.(int)$st->professor_id) ); ?>">
                            <?php echo esc_html($st->professor_name ?: ('#'.$st->professor_id)); ?>
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html($st->school_name ?: '—'); ?></td>
                    <td><?php echo esc_html($st->city_name ?: '—'); ?></td>
                    <td><?php echo esc_html($st->county_name ?: '—'); ?></td>
                    <td>
                        <?php if ($st->generation_id): ?>
                            <?php
                            $gen_url  = admin_url('admin.php?page=edu-generations&edit='.(int)$st->generation_id);
                            $gen_name = $st->generation_name ? (string)$st->generation_name : '';
                            // text vizibil: "#ID — Nume" (sau doar "#ID" dacă nu există nume)
                            $link_text = '#' . (int)$st->generation_id . ($gen_name ? ' — ' . $gen_name : '');

                            // nivel (human)
                            $map = ['prescolar'=>'Preșcolar','primar'=>'Primar','gimnazial'=>'Gimnazial','liceu'=>'Liceu'];
                            $lvl_h = isset($map[$st->generation_level]) ? $map[$st->generation_level] : (string)$st->generation_level;

                            // clase din JSON (fallback la class_label)
                            $labels = [];
                            if (!empty($st->generation_labels_json)) {
                                $tmp = json_decode($st->generation_labels_json, true);
                                if (is_array($tmp)) $labels = $tmp;
                            }
                            if (!$labels && !empty($st->generation_label)) {
                                $labels = [$st->generation_label];
                            }
                            $labels_str = $labels ? implode(', ', $labels) : '';

                            // tooltip (title)
                            $title = trim(
                                ($lvl_h ? 'Nivel: ' . $lvl_h : '') .
                                ($labels_str ? "\nClase: " . $labels_str : '')
                            );
                            ?>
                            <a href="<?php echo esc_url($gen_url); ?>" title="<?php echo esc_attr($title); ?>">
                            <?php echo esc_html($link_text); ?>
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="button button-small" href="<?php echo esc_url( admin_url('admin.php?page=edu-students&edit_student='.(int)$st->id) ); ?>">Editează</a>
                        <a class="button button-small" style="margin-left:6px;"
                           href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=edu-students&delete_student='.(int)$st->id), 'edu_delete_student' ) ); ?>"
                           onclick="return confirm('Ștergi acest elev?');">Șterge</a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="10">Nu există elevi pentru filtrul curent.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}


?>

