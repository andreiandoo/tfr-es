<?php
// admin/generation-manager.php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/helpers.php';

function edu_render_generation_manager() {
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $table = $wpdb->prefix . 'edu_generations';

    // — Delete —
    if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'edu_del_gen')) {
        $wpdb->delete($table, ['id' => intval($_GET['delete'])]);
        echo '<div class="notice notice-success"><p>Generația a fost ștearsă.</p></div>';
    }

    // — Add / Update —
    // Save (o singură generație, cu array de clase în JSON)
    if (!empty($_POST['edu_save_generation']) && check_admin_referer('edu_save_generation')) {
        $gen_id       = intval($_POST['gen_id'] ?? 0);
        $professor_id = intval($_POST['professor_id'] ?? 0);
        $level        = sanitize_text_field(strtolower($_POST['level'] ?? ''));
        $year         = intval($_POST['year'] ?? date('Y'));
        $name         = sanitize_text_field($_POST['name'] ?? '');

        // class_label poate fi array
        $class_labels = $_POST['class_label'] ?? [];
        if (!is_array($class_labels)) $class_labels = array_filter([$class_labels]);

        // Validări
        if (!in_array($level, edu_levels_allowed(), true)) {
            echo '<div class="notice notice-error"><p>Nivel de predare invalid.</p></div>';
        } elseif ($professor_id <= 0) {
            echo '<div class="notice notice-error"><p>Selectează profesorul.</p></div>';
        } else {
            // normalizează și validează opțiunile de clasă
            $allowed = edu_class_labels_by_level($level);
            $labels_valid = [];
            foreach ($class_labels as $cl) {
                $cl = sanitize_text_field($cl);
                if (in_array($cl, $allowed, true) && !in_array($cl, $labels_valid, true)) {
                    $labels_valid[] = $cl;
                }
            }

            // fallback: dacă nu ai selectat nicio clasă, mergem cu array gol (înțelegând că „doar nivelul contează”)
            $json = $labels_valid ? wp_json_encode(array_values($labels_valid)) : wp_json_encode([]);

            // Back-compat: păstrăm în 'class_label' prima valoare (tabelul e NOT NULL pe acest câmp)
            $first_label = $labels_valid[0] ?? ($allowed[0] ?? ''); // garantăm non-empty

            $data = [
                'professor_id'      => $professor_id,
                'level'             => $level,
                'class_label'       => $first_label,        // back-compat (NOT NULL)
                'class_labels_json' => $json,               // array complet
                'year'              => $year,
                'name'              => $name,
            ];

            if ($gen_id) {
                $wpdb->update($table, $data, ['id' => $gen_id]);
                echo '<div class="notice notice-success"><p>Generația a fost actualizată.</p></div>';
            } else {
                $wpdb->insert($table, $data);
                echo '<div class="notice notice-success"><p>Generația a fost adăugată.</p></div>';
            }
        }
    }


    // — Edit load —
    $edit = null;
    if (isset($_GET['edit'])) {
        $edit = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", intval($_GET['edit'])));
    }

    $existing_labels = [];
    if ($edit) {
        if (!empty($edit->class_labels_json)) {
            $tmp = json_decode($edit->class_labels_json, true);
            if (is_array($tmp)) $existing_labels = $tmp;
        } elseif (!empty($edit->class_label)) {
            $existing_labels = [$edit->class_label]; // fallback vechi
        }
    }


    // — Lists —
    $gens = $wpdb->get_results("
        SELECT g.*, u.display_name AS prof_name
        FROM {$table} g
        LEFT JOIN {$wpdb->users} u ON u.ID = g.professor_id
        ORDER BY g.year DESC, g.professor_id ASC, g.level ASC, g.class_label ASC
    ");

    $all_prof = get_users(['role'=>'profesor', 'orderby'=>'display_name','order'=>'ASC']);
    $current_year = intval(date('Y'));
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline">Generații</h1>
        <a href="#" id="toggleGenForm" class="page-title-action"><?php echo $edit ? 'Editează' : 'Adaugă generație'; ?></a>

        <div id="genForm" style="display:<?php echo $edit ? 'block':'none'; ?>;margin-top:20px;">
            <form method="post" class="card" style="padding:16px;max-width:720px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;">
                <?php wp_nonce_field('edu_save_generation'); ?>
                <input type="hidden" name="edu_save_generation" value="1">
                <input type="hidden" name="gen_id" value="<?php echo esc_attr($edit->id ?? 0); ?>">

                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label class="block mb-1"><strong>Profesor</strong></label>
                        <select name="professor_id" id="professor_id" required class="regular-text" style="width:100%;">
                            <option value="">— alege —</option>
                            <?php foreach ($all_prof as $p): ?>
                                <option value="<?php echo (int)$p->ID; ?>" <?php selected(($edit->professor_id ?? 0), (int)$p->ID); ?>>
                                    <?php echo esc_html($p->display_name . ' (#' . $p->ID . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Nivelul se preia automat din profilul profesorului.</p>
                    </div>

                    <div>
                        <label class="block mb-1"><strong>Nivel</strong></label>
                        <input type="text" id="level_display" class="regular-text" style="width:100%;" value="<?php
                            echo $edit ? esc_attr(edu_level_human($edit->level)) : ''; ?>" readonly>
                        <input type="hidden" name="level" id="level" value="<?php echo $edit ? esc_attr($edit->level) : ''; ?>">
                    </div>

                    <div>
                        <label class="block mb-1"><strong>Clase (poți selecta multiple)</strong></label>
                        <select name="class_label[]" id="class_label" required multiple size="6" class="regular-text" style="width:100%;">
                            <?php
                            if ($edit) {
                                $existing_labels = [];
                                if (!empty($edit->class_labels_json)) {
                                    $tmp = json_decode($edit->class_labels_json, true);
                                    if (is_array($tmp)) $existing_labels = $tmp;
                                } elseif (!empty($edit->class_label)) {
                                    // fallback vechi
                                    $existing_labels = [$edit->class_label];
                                }
                                foreach (edu_class_labels_by_level($edit->level) as $opt) {
                                    printf(
                                        '<option value="%1$s"%2$s>%1$s</option>',
                                        esc_attr($opt),
                                        in_array($opt, $existing_labels, true) ? ' selected' : ''
                                    );
                                }
                            } else {
                                echo '<option value="">— selectează profesorul mai întâi —</option>';
                            }
                            ?>
                        </select>
                        <p class="description">Ține Ctrl/Cmd pentru selecții multiple.</p>
                    </div>

                    <div>
                        <label class="block mb-1"><strong>Nume generație</strong></label>
                        <input type="text" name="name" class="regular-text" style="width:100%;"
                            value="<?php echo $edit ? esc_attr($edit->name ?? '') : ''; ?>"
                            placeholder="ex: Generația A – 2025">
                    </div>

                    <?php $current_year = intval(date('Y')); ?>
                    <div class="hidden">
                        <label class="block mb-1"><strong>An</strong></label>
                        <input type="text" class="regular-text" style="width:100%;" value="<?php
                            echo esc_attr($edit->year ?? $current_year); ?>" disabled>
                        <input type="hidden" name="year" value="<?php echo esc_attr($edit->year ?? $current_year); ?>">
                        <p class="description">Stabilit automat de sistem.</p>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <button type="submit" class="button button-primary"><?php echo $edit ? 'Salvează' : 'Adaugă'; ?></button>
                    <a href="<?php echo admin_url('admin.php?page=edu-generations'); ?>" class="button" style="margin-left:8px;">Renunță</a>
                </div>
            </form>
        </div>

        <hr style="margin:20px 0;">

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nume</th>
                    <th>Profesor</th>
                    <th>Nivel</th>
                    <th>Clase</th>
                    <th>An</th>
                    <th>Creat la</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($gens): foreach ($gens as $g): ?>
                <tr>
                    <td><?php echo (int)$g->id; ?></td>
                    <td><?php echo esc_html($g->name ?? ''); ?></td>
                    <td><?php echo esc_html($g->prof_name ?: ('#'.$g->professor_id)); ?></td>
                    <td><?php echo esc_html(edu_level_human($g->level)); ?></td>
                    <td>
                        <?php
                        $labels = [];
                        if (!empty($g->class_labels_json)) {
                            $tmp = json_decode($g->class_labels_json, true);
                            if (is_array($tmp)) $labels = $tmp;
                        }
                        if (!$labels && !empty($g->class_label)) $labels = [$g->class_label]; // fallback vechi
                        echo $labels ? esc_html(implode(', ', $labels)) : '—';
                        ?>
                    </td>
                    <td><?php echo esc_html($g->year); ?></td>
                    <td><?php echo esc_html($g->created_at); ?></td>
                    <td>
                        <a class="button button-small" href="<?php echo esc_url( admin_url('admin.php?page=edu-generations&edit='.$g->id) ); ?>">Editează</a>
                        <a class="button button-small button-link-delete" style="margin-left:6px;"
                           href="<?php echo esc_url( wp_nonce_url(admin_url('admin.php?page=edu-generations&delete='.$g->id), 'edu_del_gen') ); ?>"
                           onclick="return confirm('Ștergi această generație?');">Șterge</a>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="7">Nu există generații încă.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    (function(){
        const profSel = document.getElementById('professor_id');
        const levelInput = document.getElementById('level');
        const levelDisp  = document.getElementById('level_display');
        const classSel   = document.getElementById('class_label');
        const toggleBtn  = document.getElementById('toggleGenForm');
        const formWrap   = document.getElementById('genForm');

        if (toggleBtn && formWrap) {
            toggleBtn.addEventListener('click', function(e){
                e.preventDefault();
                formWrap.style.display = (formWrap.style.display === 'none' || !formWrap.style.display) ? 'block' : 'none';
            });
        }

        function fillOptions(arr) {
            classSel.innerHTML = '';
            if (!arr || !arr.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '— nu sunt opțiuni pentru nivelul curent —';
                opt.disabled = true;
                classSel.appendChild(opt);
                return;
            }
            arr.forEach(function(x){
                const opt = document.createElement('option');
                opt.value = x;
                opt.textContent = x;
                classSel.appendChild(opt);
            });
        }

        function human(level) {
            const map = {prescolar:'Preșcolar', primar:'Primar', gimnazial:'Gimnazial', liceu:'Liceu'};
            return map[level] || level;
        }

        async function fetchLevel(profId) {
            if (!profId) return;
            try {
                const resp = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        action: 'edu_get_professor_level',
                        nonce: '<?php echo esc_js( wp_create_nonce('edu_nonce') ); ?>',
                        professor_id: String(profId)
                    })
                });
                const data = await resp.json();
                if (data && data.success) {
                    const lvl = data.data.level || '';
                    // setăm hidden + display
                    levelInput.value = lvl;
                    levelDisp.value  = lvl ? human(lvl) : '';

                    // populăm opțiunile pentru "Clasa"
                    fillOptions(data.data.labels || []);
                } else {
                    // fallback: reset
                    levelInput.value = '';
                    levelDisp.value = '';
                    fillOptions([]);
                }
            } catch(e) {
                levelInput.value = '';
                levelDisp.value = '';
                fillOptions([]);
            }
        }

        // la schimbarea profesorului → populate nivel + clase
        if (profSel) {
            profSel.addEventListener('change', function(){
                const id = parseInt(this.value || '0', 10);
                fetchLevel(id > 0 ? id : 0);
            });
        }

        // dacă formularul e "Adaugă" (nu edit) și există deja o selecție la load, populăm
        <?php if (!isset($edit) || !$edit): ?>
        if (profSel && profSel.value) {
            const id = parseInt(profSel.value, 10);
            if (id > 0) fetchLevel(id);
        }
        <?php endif; ?>

    })();
    </script>
    <?php
}
