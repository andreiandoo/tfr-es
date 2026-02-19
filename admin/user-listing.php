<?php
// admin/user-listing.php

function edu_render_user_listing() {
    $user_roles = [
        'profesor'  => 'Profesor',
        'tutor'     => 'Tutor',
        'alumni'    => 'Alumni',
        'non-teach' => 'Non-Teach'
    ];
    $statusuri_profesori = [
        'in_asteptare'         => 'În așteptare',
        'activ'                => 'Activ',
        'drop-out'             => 'Drop-out',
        'eliminat'             => 'Eliminat',
        'concediu_maternitate' => 'Concediu maternitate',
        'concediu_studii'      => 'Concediu studii'
    ];

    // — Bulk reset links —
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        ! empty($_POST['reset_password_links']) &&
        ! empty($_POST['selected_users'])
    ) {
        foreach ((array) $_POST['selected_users'] as $uid) {
            $user = get_userdata(intval($uid));
            if ($user) {
                $url = wp_lostpassword_url() . '?user_login=' . urlencode($user->user_login);
                wp_mail(
                    $user->user_email,
                    'Resetare parolă cont EDU',
                    "Bună {$user->display_name},\n\nAccesează linkul: $url"
                );
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>Linkurile au fost trimise.</p></div>';
    }

    // — Filter & Search args —
    $selected_roles = $_GET['filter_roles'] ?? [];
    $search         = sanitize_text_field($_GET['user_search'] ?? '');
    $args = [
        'role__in' => $selected_roles ?: array_keys($user_roles),
        'orderby'  => 'registered',
        'order'    => 'DESC',
    ];
    if ($search) {
        $args['search']         = "*{$search}*";
        $args['search_columns'] = ['user_login','user_email','display_name'];
    }
    $users = get_users($args);

    // — Page header & Add button —
    echo '<div class="wrap">';
      echo '<h1 class="wp-heading-inline">Utilizatori Edu Start</h1>';
      echo '<a href="#" id="toggleAddUserForm" class="page-title-action">Adaugă utilizator nou</a>';
    echo '</div>';

    // — AJAX form container —
    echo '<div id="addUserForm" style="display: none;" class="p-4 my-4 bg-white rounded shadow">';
      echo '<div id="addUserFormContainer"></div>';
    echo '</div>';

    // — Search + Role Filter (unchanged!) —
    echo '<form method="get" class="flex items-start items-center mb-4 gap-x-4">';
      echo '<input type="hidden" name="page" value="edu-user-listing">';
      echo '<input type="text" name="user_search" placeholder="Caută nume/email..." class="p-2 border rounded" value="'.esc_attr($search).'">';
      echo '<label class="block font-medium">Filtru după rol:</label>';
      echo '<select name="filter_roles[]" id="filter_roles_select" multiple class="w-full p-2 border rounded md:w-1/2">';
        foreach ($user_roles as $slug => $label) {
            $sel = in_array($slug, $selected_roles) ? 'selected' : '';
            echo "<option value='$slug' $sel>$label</option>";
        }
      echo '</select>';
      echo '<button class="mt-2 button">Aplică filtre</button>';
    echo '</form>';

    // — Users table with new “Vezi detalii” column —
    echo '<form method="post">';
    echo '<table class="wp-list-table widefat striped users">';
      echo '<thead><tr class="sticky top-0">';
        echo '<th class="manage-column check-column"><input type="checkbox" id="check_all"></th>';
        echo '<th>Imagine</th><th>Nume</th><th>Email</th><th>Rol</th><th>Status</th>';
        echo '<th>Școli</th>';
        echo '<th>Mentor SEL</th><th>Mentor Literatie</th><th>Mentor Numeratie</th>';
        echo '<th>Creat</th>';
        echo '<th>Acțiuni</th>';
        echo '<th>Vezi detalii</th>';   // ← added
        echo '<th>Resetare</th>';
      echo '</tr></thead>';
      echo '<tbody id="eduUserTableBody">';
        foreach ($users as $user) {
            echo edu_render_user_row($user, $statusuri_profesori, $user_roles);
        }
      echo '</tbody>';
    echo '</table>';
    echo '</form>';

    ?>
    <!-- details modal -->
    <div id="userDetailsModal" class="fixed top-0 left-0 z-50 flex items-center justify-center w-full h-full" style="background:rgb(29 35 39 / 60%); display:none;">
        <div class="relative p-6 m-auto bg-white" style="max-width:500px;width:90%;">
            <button id="closeUserDetails" class="absolute text-2xl bg-transparent border-none top-2 right-2">×</button>
            <div id="userDetailsContent" class="">Se încarcă…</div>
        </div>
    </div>
    <?php
}

function edu_render_user_row($user, $statusuri_profesori, $user_roles_map) {
    global $wpdb;

    // Profile image
    $img_id = get_user_meta($user->ID,'profile_image',true);
    $img    = $img_id
      ? wp_get_attachment_image($img_id,'thumbnail',false,[
          'style'=>'width:30px;height:30px;border-radius:50%;'
        ])
      : '';

    // Role label
    $labels   = array_map(fn($r)=>$user_roles_map[$r]??$r, (array)$user->roles);
    $role_lbl = implode(', ',$labels);

    // Status
    $stat = get_user_meta($user->ID,'user_status_profesor',true)
         ?: get_user_meta($user->ID,'user_status_tutor',true)
         ?: '';
    $stat_lbl = $statusuri_profesori[$stat] ?? ucfirst($stat) ?: '-';

    // Schools
    $sids  = get_user_meta($user->ID,'assigned_school_ids',true) ?: [];
    $names = [];
    foreach ((array)$sids as $sid) {
        if ($n = $wpdb->get_var(
          $wpdb->prepare("SELECT name FROM {$wpdb->prefix}edu_schools WHERE id=%d",$sid)))
        {
          $names[] = $n;
        }
    }

    // Tutor
    $tutor_obj  = get_user_by('ID',get_user_meta($user->ID,'assigned_tutor_id',true));
    $tutor_name = $tutor_obj ? esc_html($tutor_obj->display_name) : '-';

    $mentor_sel_id       = get_user_meta($user->ID,'mentor_sel',true);
    $mentor_liter_id     = get_user_meta($user->ID,'mentor_literatie',true);
    $mentor_num_id       = get_user_meta($user->ID,'mentor_numeratie',true);
    $mentor_sel_name     = $mentor_sel_id   ? get_userdata($mentor_sel_id)->display_name   : '-';
    $mentor_liter_name   = $mentor_liter_id ? get_userdata($mentor_liter_id)->display_name : '-';
    $mentor_num_name     = $mentor_num_id   ? get_userdata($mentor_num_id)->display_name   : '-';

    // Created
    $created = date_i18n('d.m.Y H:i',strtotime($user->user_registered));

    return "
    <tr id='user-row-{$user->ID}'>
      <th class='check-column'>
        <input type='checkbox' name='selected_users[]' value='{$user->ID}'>
      </th>
      <td>{$img}</td>
      <td>{$user->display_name}</td>
      <td>{$user->user_email}</td>
      <td>{$role_lbl}</td>
      <td>{$stat_lbl}</td>
      <td>".implode(', ',$names)."</td>
      <td>".esc_html($mentor_sel_name)."</td>
      <td>".esc_html($mentor_liter_name)."</td>
      <td>".esc_html($mentor_num_name)."</td>
      <td>{$created}</td>
      <td>
        <a href='#' class='edit-user button button-small' data-user-id='{$user->ID}'>
          Editează
        </a>
        <a href='#' class='delete-user button button-small button-danger' data-user-id='{$user->ID}'>
          Șterge
        </a>
      </td>
      <td>
        <a href='#' class='view-details button button-small' data-user-id='{$user->ID}'>
          Detalii
        </a>
      </td>
      <td>
        <button type='button' class='send-reset-link button small' data-user-id='{$user->ID}'>
          Trimite link
        </button>
        <span id='reset-msg-{$user->ID}' style='display:none;margin-left:8px;color:green;'>
          Trimis
        </span>
      </td>
    </tr>
    ";
}
