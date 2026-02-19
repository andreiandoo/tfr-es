<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/helpers.php';

function edu_render_student_manager_generations() {
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $table_students    = $wpdb->prefix . 'edu_students';
    $table_generations = $wpdb->prefix . 'edu_generations';

    // Lista generațiilor
    $gens = $wpdb->get_results("
        SELECT g.*, u.display_name AS prof_name
        FROM {$table_generations} g
        LEFT JOIN {$wpdb->users} u ON u.ID = g.professor_id
        ORDER BY g.year DESC, g.professor_id ASC, g.level ASC, g.class_label ASC
    ");

    // Insert bulk
    if (!empty($_POST['edu_save_students_gen']) && check_admin_referer('edu_save_students_gen')) {
        $generation_id = intval($_POST['generation_id'] ?? 0);

        if ($generation_id <= 0) {
            echo '<div class="notice notice-error"><p>Selectează generația.</p></div>';
        } else {
            $gen = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_generations} WHERE id=%d", $generation_id));
            if (!$gen) {
                echo '<div class="notice notice-error"><p>Generație invalidă.</p></div>';
            } else {
                $prof_id = intval($gen->professor_id);
                $rows = $_POST['students'] ?? [];
                $added = 0;

                foreach ($rows as $row) {
                    $first = sanitize_text_field($row['first_name'] ?? '');
                    $last  = sanitize_text_field($row['last_name'] ?? '');
                    if (!$first || !$last) continue;

                    $age    = ($row['age'] ?? '') !== '' ? intval($row['age']) : null;
                    $gender = sanitize_text_field($row['gender'] ?? '');
                    $obs    = sanitize_text_field($row['observation'] ?? '');
                    $sit    = sanitize_text_field($row['sit_abs'] ?? '');
                    $frec   = sanitize_text_field($row['frecventa'] ?? '');
                    $bursa  = sanitize_text_field($row['bursa'] ?? '');
                    $dif    = sanitize_text_field($row['dif_limba'] ?? '');
                    $notes  = sanitize_textarea_field($row['notes'] ?? '');

                    $wpdb->insert($table_students, [
                        'generation_id'         => $generation_id,
                        'professor_id'          => $prof_id,
                        // class_id rămâne neatins (NULL) — pentru back-compat/migrare
                        'first_name'            => $first,
                        'last_name'             => $last,
                        'age'                   => $age,
                        'gender'                => $gender,
                        'observation'           => $obs,
                        'situatie_absenteism'   => $sit,
                        'frecventa_gradinita'   => $frec,
                        'bursa_sociala'         => $bursa,
                        'difera_limba'          => $dif,
                        'notes'                 => $notes,
                        'created_at'            => current_time('mysql'),
                    ]);
                    $added++;
                }
                echo '<div class="notice notice-success is-dismissible"><p>'.esc_html($added).' elev(i) adăugat(i) pe generație.</p></div>';
            }
        }
    }

    // Listare elevi (filtrați pe generație)
    $filter_gen = isset($_GET['generation_id']) ? intval($_GET['generation_id']) : 0;
    $students = [];
    if ($filter_gen > 0) {
        $students = $wpdb->get_results($wpdb->prepare("
            SELECT s.*, g.level, g.class_label, g.year, u.display_name AS prof_name
            FROM {$table_students} s
            LEFT JOIN {$table_generations} g ON g.id = s.generation_id
            LEFT JOIN {$wpdb->users} u ON u.ID = g.professor_id
            WHERE s.generation_id = %d
            ORDER BY s.last_name, s.first_name
        ", $filter_gen));
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Elevi (Generații)</h1>

        <form method="post" class="card" style="padding:16px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; margin-top:14px;">
            <?php wp_nonce_field('edu_save_students_gen'); ?>
            <input type="hidden" name="edu_save_students_gen" value="1">

            <div class="grid" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label class="block mb-1"><strong>Generație</strong></label>
                    <select name="generation_id" id="generation_id" class="regular-text" style="width:100%;" required>
                        <option value="">— alege —</option>
                        <?php foreach ($gens as $g): ?>
                            <option value="<?php echo (int)$g->id; ?>" <?php selected($filter_gen, (int)$g->id); ?>>
                                <?php printf('%s — %s — %s — %d',
                                    $g->prof_name ?: ('#'.$g->professor_id),
                                    edu_level_human($g->level),
                                    $g->class_label,
                                    $g->year
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr style="margin:14px 0;">

            <table class="widefat striped" id="studentsTable">
                <thead>
                    <tr>
                        <th>Prenume</th>
                        <th>Nume</th>
                        <th>Vârstă</th>
                        <th>Gen</th>
                        <th>Observații</th>
                        <th>Absenteism</th>
                        <th>Frecvență</th>
                        <th>Bursă</th>
                        <th>Dif. limbă</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="studentsBody"></tbody>
            </table>

            <div style="margin-top:12px;">
                <button type="button" class="button" id="addStudentRow">+ Adaugă elev</button>
            </div>

            <div style="margin-top:16px;">
                <button type="submit" class="button button-primary">Salvează elevi</button>
            </div>
        </form>

        <hr style="margin:20px 0;">

        <form method="get">
            <input type="hidden" name="page" value="edu-students-generations">
            <label><strong>Filtru generație:</strong></label>
            <select name="generation_id" onchange="this.form.submit()">
                <option value="">— toate —</option>
                <?php foreach ($gens as $g): ?>
                    <option value="<?php echo (int)$g->id; ?>" <?php selected($filter_gen, (int)$g->id); ?>>
                        <?php printf('%s — %s — %s — %d',
                            $g->prof_name ?: ('#'.$g->professor_id),
                            edu_level_human($g->level),
                            $g->class_label,
                            $g->year
                        ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <table class="widefat striped" style="margin-top:12px;">
            <thead>
                <tr>
                    <th>Nume</th>
                    <th>Gen</th>
                    <th>Vârstă</th>
                    <th>Profesor</th>
                    <th>Nivel</th>
                    <th>Clasa</th>
                    <th>An</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($students): foreach ($students as $st): ?>
                <tr>
                    <td><?php echo esc_html($st->last_name . ' ' . $st->first_name); ?></td>
                    <td><?php echo esc_html($st->gender); ?></td>
                    <td><?php echo esc_html($st->age); ?></td>
                    <td><?php echo esc_html($st->prof_name ?: ('#'.$st->professor_id)); ?></td>
                    <td><?php echo esc_html(edu_level_human($st->level)); ?></td>
                    <td><?php echo esc_html($st->class_label); ?></td>
                    <td><?php echo esc_html($st->year); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7">Niciun elev pentru filtrul curent.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    (function(){
        let idx = 0;
        const tbody = document.getElementById('studentsBody');
        const addBtn = document.getElementById('addStudentRow');

        function rowTemplate(i){
            return `
            <tr data-idx="${i}">
                <td><input type="text"  name="students[${i}][first_name]" class="regular-text" required></td>
                <td><input type="text"  name="students[${i}][last_name]"  class="regular-text" required></td>
                <td><input type="number" name="students[${i}][age]"        class="small-text" min="1"></td>
                <td>
                    <select name="students[${i}][gender]">
                        <option value=""></option>
                        <option value="M">M</option>
                        <option value="F">F</option>
                    </select>
                </td>
                <td>
                    <select name="students[${i}][observation]" class="obs-select">
                        <option value="">—</option>
                        <option value="DA">Are observații</option>
                        <option value="NU">Nu</option>
                    </select>
                </td>
                <td>
                    <select name="students[${i}][sit_abs]">
                        <option value="">—</option>
                        <option>Deloc</option>
                        <option>Uneori/Rar</option>
                        <option>Des</option>
                        <option>Foarte Des</option>
                    </select>
                </td>
                <td>
                    <select name="students[${i}][frecventa]">
                        <option value="">—</option>
                        <option>Nu</option>
                        <option>Da (1an)</option>
                        <option>Da (2ani)</option>
                        <option>Da (3ani)</option>
                    </select>
                </td>
                <td>
                    <select name="students[${i}][bursa]">
                        <option value="">—</option>
                        <option>Nu</option>
                        <option>Da</option>
                    </select>
                </td>
                <td>
                    <select name="students[${i}][dif_limba]">
                        <option value="">—</option>
                        <option>Nu</option>
                        <option>Da</option>
                    </select>
                </td>
                <td>
                    <textarea name="students[${i}][notes]" rows="1" style="min-width:160px;display:none;" placeholder="Detalii observații…"></textarea>
                </td>
                <td>
                    <button type="button" class="button-link delete-row" aria-label="Șterge">✕</button>
                </td>
            </tr>`;
        }

        function addRow(){ tbody.insertAdjacentHTML('beforeend', rowTemplate(idx++)); }
        addRow();

        addBtn.addEventListener('click', addRow);

        tbody.addEventListener('click', function(e){
            if (e.target && e.target.classList.contains('delete-row')) {
                e.preventDefault();
                const tr = e.target.closest('tr');
                if (tr) tr.remove();
            }
        });

        tbody.addEventListener('change', function(e){
            if (e.target && e.target.classList.contains('obs-select')) {
                const ta = e.target.closest('tr').querySelector('textarea');
                if (e.target.value === 'DA') { ta.style.display = 'block'; }
                else { ta.style.display = 'none'; ta.value = ''; }
            }
        });
    })();
    </script>
    <?php
}
