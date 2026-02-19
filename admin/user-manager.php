<?php
// admin/user-manager.php

function edu_render_user_manager($user_id = null) {
    global $wpdb;

    $editing_user = $user_id ? get_userdata($user_id) : null;

    // Basic fields
    $first_name  = $editing_user ? $editing_user->first_name : '';
    $last_name   = $editing_user ? $editing_user->last_name  : '';
    $email       = $editing_user ? $editing_user->user_email  : '';
    $phone       = $editing_user ? get_user_meta($user_id, 'phone', true) : '';
    $role        = $editing_user && !empty($editing_user->roles) ? $editing_user->roles[0] : '';
    $profile_id  = $editing_user ? get_user_meta($user_id, 'profile_image', true) : '';

    // Profesor-only metas (helper)
    $meta          = function($key) use ($editing_user, $user_id){ return $editing_user ? get_user_meta($user_id, $key, true) : ''; };
    $status_prof   = $meta('user_status_profesor');
    $generatie     = $meta('generatie');
    $an_program    = $meta('an_program');
    $cod_slf       = $meta('cod_slf');
    $statut_prof   = $meta('statut_prof');
    $calificare    = $meta('calificare');
    $experienta    = $meta('experienta');
    $segment_rsoi  = $meta('segment_rsoi');
    $nivel_predare = $meta('nivel_predare');
    $materia       = $meta('materia_predata');
    $materia_alta  = $meta('materia_alta');

    // Mentori
    $mentor_sel       = $meta('mentor_sel');
    $mentor_literatie = $meta('mentor_literatie');
    $mentor_numeratie = $meta('mentor_numeratie');

    // Lists
    $tutors    = get_users(['role'=>'tutor']);
    $profesori = get_users(['role'=>'profesor']);

    // Generații list (saved in option)
    $generatii = get_option('edu_generatii', []);
    if (!$generatii) {
        for ($i = 1; $i <= 12; $i++) $generatii[] = "G{$i}";
        update_option('edu_generatii', $generatii);
    }

    // Status pentru profesor
    $statusuri = [
        'in_asteptare'         => 'În așteptare',
        'activ'                => 'Activ',
        'drop-out'             => 'Drop-out',
        'eliminat'             => 'Eliminat',
        'concediu_maternitate' => 'Concediu maternitate',
        'concediu_studii'      => 'Concediu studii',
    ];

    // Status pentru tutor (handlerul salvează stringul, setăm o listă simplă)
    $statusuri_tutor = [
        'activ'        => 'Activ',
        'in_asteptare' => 'În așteptare',
        'suspendat'    => 'Suspendat',
    ];
    $status_tutor = $meta('user_status_tutor');

    // Preload școli selectate (pt. fallback fără select2)
    $assigned_schools = $editing_user
        ? (array) get_user_meta($user_id, 'assigned_school_ids', true)
        : [];

    $initial_schools = [];
    if ($assigned_schools) {
        $placeholders = implode(',', array_fill(0, count($assigned_schools), '%d'));
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, name FROM {$wpdb->prefix}edu_schools WHERE id IN ($placeholders)",
                ...$assigned_schools
            )
        );
        foreach ($rows as $r) {
            $initial_schools[] = [
                'id'   => (int)$r->id,
                'text' => $r->name,
            ];
        }
    }

    // Tutor coordonator pentru profesor
    $assigned_tutor_id = (int) $meta('assigned_tutor_id');

    ?>
    <div class="flex flex-col items-start justify-between mb-4 wrap">
      <h2 class="text-xl font-semibold"><?php echo $editing_user ? "Editează: " . esc_html($first_name . ' ' . $last_name) : 'Adaugă utilizator'; ?></h2>

      <form method="post" id="eduAddUserForm" enctype="multipart/form-data" class="flex flex-wrap gap-4">
        <?php wp_nonce_field('edu_user_form'); ?>
        <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">

        <div class="flex w-full gap-4">
            <div class="flex flex-col">
                <label class="block font-medium">Tip utilizator</label>
                <select name="user_role" id="user_role" class="w-full p-2 border rounded" required>
                    <option value="">–</option>
                    <option value="profesor"  <?php selected($role,'profesor');  ?>>Profesor</option>
                    <option value="tutor"     <?php selected($role,'tutor');     ?>>Tutor</option>
                    <option value="alumni"    <?php selected($role,'alumni');    ?>>Alumni</option>
                    <option value="non-teach" <?php selected($role,'non-teach'); ?>>Non-Teach</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap w-full gap-4">
            <!-- PROFESOR: Status -->
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Status profesor</label>
                <select name="user_status_profesor" class="w-full p-2 border rounded">
                    <?php foreach ($statusuri as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($status_prof, $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- TUTOR: Status -->
            <div class="flex flex-col w-1/6 tutor-only" style="display:none;">
                <label class="block font-medium">Status tutor</label>
                <select name="user_status_tutor" class="w-full p-2 border rounded">
                    <?php foreach ($statusuri_tutor as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($status_tutor, $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nume/Prenume/Email/Telefon -->
            <div class="flex flex-col w-1/6">
                <label class="block font-medium">Prenume</label>
                <input type="text" name="first_name" class="w-full p-2 border rounded" value="<?php echo esc_attr($first_name); ?>">
            </div>

            <div class="flex flex-col w-1/6">
                <label class="block font-medium">Nume</label>
                <input type="text" name="last_name" class="w-full p-2 border rounded" value="<?php echo esc_attr($last_name); ?>">
            </div>

            <div class="flex flex-col w-1/6">
                <label class="block font-medium">Email</label>
                <input type="email" name="email" class="w-full p-2 border rounded" value="<?php echo esc_attr($email); ?>">
            </div>

            <div class="flex flex-col w-1/6">
                <label class="block font-medium">Telefon</label>
                <input type="text" name="phone" class="w-full p-2 border rounded" value="<?php echo esc_attr($phone); ?>">
            </div>

            <!-- Parola: info numai (parola e generată în handler) -->
            <div class="flex flex-col w-1/6">
                <label class="block font-medium">Parolă</label>
                <input type="password" name="password" id="user_password" class="w-full p-2 border rounded" placeholder="(se generează automat)" disabled>
                <p class="text-xs mt-1">La creare, parola este generată automat. Poți trimite link de resetare după salvare.</p>
            </div>

            <!-- PROFESOR: Școli atribuite (AJAX Select2) -->
            <div class="flex items-end justify-between w-1/3 profesor-only" style="display:none;">
                <div class="flex flex-col w-full">
                    <label class="block font-medium">Școli atribuite</label>
                    <select
                        id="school_select_ajax"
                        name="assigned_school_ids[]"
                        multiple
                        class="w-full p-2 border rounded"
                        style="width:100%">
                        <?php
                        // Preload selected options (fallback fără Select2/AJAX)
                        foreach ($initial_schools as $sch) {
                            printf('<option value="%1$d" selected>%2$s</option>', esc_attr($sch['id']), esc_html($sch['text']));
                        }
                        ?>
                    </select>
                    <p class="text-xs mt-1">Poți căuta după nume sau cod SIIIR (dacă e activ Select2).</p>
                </div>
            </div>

            <!-- PROFESOR: Tutor coordonator -->
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Tutor coordonator</label>
                <select name="assigned_tutor_id" class="w-full p-2 border rounded">
                    <option value="">–</option>
                    <?php foreach ($tutors as $t): ?>
                        <option value="<?php echo (int)$t->ID; ?>" <?php selected($assigned_tutor_id, $t->ID); ?>>
                            <?php echo esc_html($t->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- PROFESOR: Generație -->
            <div class="flex items-end justify-between w-1/6 profesor-only" style="display:none;">
                <div class="flex flex-col flex-auto w-full">
                    <label class="block font-medium">Generație</label>
                    <select name="generatie" id="generatie_select" class="w-48 p-2 border rounded">
                        <?php foreach ($generatii as $g): ?>
                            <option value="<?php echo esc_attr($g); ?>" <?php selected($generatie,$g); ?>><?php echo esc_html($g); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="button" id="add_generatie_btn" class="button">Adaugă</button>
            </div>

            <!-- PROFESOR: An program -->
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">An de program</label>
                <select name="an_program" class="w-48 p-2 border rounded">
                    <option value="An 1" <?php selected($an_program,'An 1'); ?>>An 1</option>
                    <option value="An 2" <?php selected($an_program,'An 2'); ?>>An 2</option>
                </select>
            </div>

            <!-- PROFESOR: diverse meta -->
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Cod prof SLF</label>
                <input type="text" name="cod_slf" class="w-full p-2 border rounded" value="<?php echo esc_attr($cod_slf); ?>">
            </div>

            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Statut</label>
                <select name="statut_prof" class="w-full p-2 border rounded">
                    <?php foreach ([
                        'Suplinitor necalificat','Suplinitor calificat','Titular',
                        'Titular pe viabilitatea postului','Director','Inspector'
                    ] as $st): ?>
                        <option <?php selected($statut_prof,$st); ?>><?php echo esc_html($st); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Calificare</label>
                <select name="calificare" class="w-full p-2 border rounded">
                    <option value="Calificat"   <?php selected($calificare,'Calificat');   ?>>Calificat</option>
                    <option value="Necalificat" <?php selected($calificare,'Necalificat'); ?>>Necalificat</option>
                </select>
            </div>

            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Experiență</label>
                <select name="experienta" class="w-full p-2 border rounded">
                    <option <?php selected($experienta,'2 ani sau mai putin'); ?>>2 ani sau mai puțin</option>
                    <option <?php selected($experienta,'3-5 ani'); ?>>3-5 ani</option>
                    <option <?php selected($experienta,'mai mult de 5 ani'); ?>>Mai mult de 5 ani</option>
                </select>
            </div>

            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Segment RSOI</label>
                <select name="segment_rsoi" class="w-full p-2 border rounded">
                    <option <?php selected($segment_rsoi,'YP'); ?>>YP</option>
                    <option <?php selected($segment_rsoi,'ED'); ?>>ED</option>
                    <option <?php selected($segment_rsoi,'CC'); ?>>CC</option>
                </select>
            </div>

            <?php
            // Opțiuni nivel predare (normalizare)
            $allowed_levels = [
                'prescolar' => 'Preșcolar',
                'primar'    => 'Primar',
                'gimnazial' => 'Gimnazial',
                'liceu'     => 'Liceu',
            ];
            $old_to_new = [
                'Prescolar'           => 'prescolar',
                'Preșcolar'           => 'prescolar',
                'Primar Mic'          => 'primar',
                'Primar Mare'         => 'primar',
                'Gimnaziu'            => 'gimnazial',
                'Primar & Gimnaziu'   => 'gimnazial',
                'Liceu'               => 'liceu',
            ];
            $saved_level_code = '';
            if ($nivel_predare) {
                $low = strtolower($nivel_predare);
                if (isset($allowed_levels[$low]))       $saved_level_code = $low;
                elseif (isset($old_to_new[$nivel_predare])) $saved_level_code = $old_to_new[$nivel_predare];
            }
            ?>
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Nivel de predare</label>
                <select name="nivel_predare" id="nivel_predare" class="w-full p-2 border rounded" required>
                    <option value="">— alege —</option>
                    <?php foreach ($allowed_levels as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($saved_level_code, $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label class="block font-medium">Materia predată</label>
                <select name="materia_predata" id="materia_select" class="w-full p-2 border rounded">
                    <?php
                    $default_materii = [
                        'Educator','Învățător','Română','Engleză','Franceză','Germană','Latină',
                        'Geografie','Istorie','Biologie','Fizică','Chimie','Matematică',
                        'Educație tehnologică','TIC','Cultură civică & Educație civică',
                        'Educație socială','Religie','Arte','Educație plastică','Educație muzicală',
                        'Educație fizică și sport','Învățământ special','Consilier școlar','Turcă','Alta'
                    ];
                    foreach($default_materii as $mat) {
                        echo "<option value='".esc_attr($mat)."' ".selected($materia,$mat,false).">".esc_html($mat)."</option>";
                    }
                    ?>
                </select>
                <input type="text" name="materia_alta" id="materia_alta_input" class="w-full p-2 mt-2 border rounded" placeholder="Specifica materia" value="<?php echo esc_attr($materia_alta); ?>" style="display:none;">
            </div>

            <!-- Mentori -->
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label>Mentor alocat SEL</label>
                <select name="mentor_sel" class="w-48 p-2 border rounded">
                    <option value="">–</option>
                    <?php foreach($tutors as $t): ?>
                        <option value="<?php echo (int)$t->ID; ?>" <?php selected($mentor_sel,$t->ID); ?>>
                            <?php echo esc_html($t->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label>Mentor alocat literatie</label>
                <select name="mentor_literatie" class="w-48 p-2 border rounded">
                    <option value="">–</option>
                    <?php foreach($tutors as $t): ?>
                        <option value="<?php echo (int)$t->ID; ?>" <?php selected($mentor_literatie,$t->ID); ?>>
                            <?php echo esc_html($t->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col w-1/6 profesor-only" style="display:none;">
                <label>Mentor alocat numeratie</label>
                <select name="mentor_numeratie" class="w-48 p-2 border rounded">
                    <option value="">–</option>
                    <?php foreach($tutors as $t): ?>
                        <option value="<?php echo (int)$t->ID; ?>" <?php selected($mentor_numeratie,$t->ID); ?>>
                            <?php echo esc_html($t->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-start w-full gap-x-4">
            <div class="flex items-center gap-x-4">
                <label class="block font-medium">Imagine profil</label>
                <input type="file" name="profile_image" class="w-full p-2 border rounded">
            </div>

            <div class="profesor-only" style="display:none;">
                <label><input type="checkbox" name="send_reset_link" value="1"> Trimite link resetare parolă</label>
            </div>

            <div class="flex gap-x-4">
                <button type="submit" class="button button-primary"><?php echo $editing_user? 'Salvează' : 'Adaugă'; ?></button>
                <button type="button" class="button button-secondary" id="cancelUserForm">Anulează</button>
            </div>
        </div>
      </form>

    <?php
    // asigură obiectul global pentru AJAX, cu nonce-ul corect
    $edu_ajax_boot = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('edu_nonce'),
    ];
    echo '<script>window.edu_ajax = window.edu_ajax || '.json_encode($edu_ajax_boot).';</script>';
    ?>
    <script>
    jQuery(function($){
        // Toggle secțiuni după rol
        function toggleByRole(role){
            $('.profesor-only').toggle(role === 'profesor');
            $('.tutor-only').toggle(role === 'tutor');
            $('.alumni-only').toggle(role === 'alumni');
        }
        $('#user_role').on('change', function(){ toggleByRole(this.value); }).trigger('change');

        // Materia “Alta”
        $('#materia_select').on('change', function(){
            $('#materia_alta_input').toggle(this.value === 'Alta');
        }).trigger('change');

        // Generație – add + save via AJAX
        $('#add_generatie_btn').on('click', function(){
            var gen = prompt('Introduceți noua generație (ex: G13):');
            if (!gen) return;
            $('<option>').val(gen).text(gen).appendTo('#generatie_select');
            $('#generatie_select').val(gen);
            // save option
            if (window.edu_ajax) {
                $.post(edu_ajax.ajax_url, { action:'edu_add_generatie', nonce: edu_ajax.nonce, generatie: gen });
            }
        });

        // Școli atribuite: Select2 AJAX (dacă există select2)
        var $schoolSel = $('#school_select_ajax');
        if ($.fn.select2 && window.edu_ajax) {
            $schoolSel.select2({
                width: 'resolve',
                placeholder: 'Caută școli...',
                ajax: {
                    url: edu_ajax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            action: 'edu_search_schools',
                            nonce: edu_ajax.nonce,
                            q: params.term || ''
                        };
                    },
                    processResults: function (data) {
                        // așteptăm [{id, cod, name, city, county, text}]
                        return { results: (data || []) };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });
        } else {
            // Fallback: rămâne lista preîncărcată (opțiunile selectate deja sunt randate)
        }
    });
    </script>
    <?php
}
