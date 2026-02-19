<?php
// partials/panou-profil.php — ROUTER + PERMISIUNI + IMPERSONARE
if (!defined('ABSPATH')) exit;

global $wpdb;

$prof_id  = (int) ( get_query_var('profesor_id') ?: ($_GET['profesor_id'] ?? 0) );
$tutor_id = (int) ( get_query_var('tutor_id')    ?: ($_GET['tutor_id']    ?? 0) );

$logged     = wp_get_current_user();
$uid        = (int) ($logged->ID ?? 0);
$roles      = (array) ($logged->roles ?? []);
$is_admin   = current_user_can('manage_options');
$is_prof    = in_array('profesor', $roles, true);
$is_tutor   = in_array('tutor', $roles, true);
$debug      = !empty($_GET['debug']);

$dash_dir   = get_template_directory() . '/dashboard';
$prof_tpl   = $dash_dir . '/profesor-profile.php';
$tutor_tpl  = $dash_dir . '/tutor-profile.php';

if ($debug) {
  echo '<pre style="margin:1rem 0;padding:.75rem;background:#fff;border:1px solid #e5e7eb;border-radius:.5rem">';
  echo "DEBUG router profil\n";
  echo "logged_user_id: {$uid}\nroles: ".implode(',', $roles)."\n";
  echo "profesor_id(qv): {$prof_id} | tutor_id(qv): {$tutor_id}\n";
  echo "</pre>";
}

/** helper: verifică dacă user-ul $prof_id există și e profesor */
function es_is_profesor_user($user_id) {
  $u = get_userdata($user_id);
  return ($u && in_array('profesor', (array)$u->roles, true));
}

/** helper: verifică dacă user-ul $tutor_id există și e tutor */
function es_is_tutor_user($user_id) {
  $u = get_userdata($user_id);
  return ($u && in_array('tutor', (array)$u->roles, true));
}

/** helper: tutor-ul $tutor_uid este alocat profesorului $prof_uid ? */
function es_tutor_owns_prof($prof_uid, $tutor_uid) {
  return ((int) get_user_meta($prof_uid, 'assigned_tutor_id', true)) === (int)$tutor_uid;
}

/** helper: profesorul $prof_uid îl are alocat pe tutorul $tutor_uid ? (vice-versa) */
function es_prof_has_tutor($prof_uid, $tutor_uid) {
  return es_tutor_owns_prof($prof_uid, $tutor_uid);
}

/** helper: include cu impersonare target user, apoi revine la user-ul inițial */
function es_include_as_user($target_uid, $template_path, $debug = false) {
  $orig_uid = get_current_user_id();
  if ($debug) {
    echo '<!-- IMPERSONATE from '.$orig_uid.' to '.$target_uid.' -->';
  }
  wp_set_current_user($target_uid);
  $GLOBALS['current_user'] = wp_get_current_user();
  define('EDUS_PROFILE_PROXY', true);

  include $template_path;

  if ($debug) {
    echo '<!-- RESTORE user '.$orig_uid.' -->';
  }
  wp_set_current_user($orig_uid);
  $GLOBALS['current_user'] = wp_get_current_user();
}

/* ====== 1) /panou/profesor/{id} ====== */
if ($prof_id > 0) {
  if (!es_is_profesor_user($prof_id)) {
    echo '<div class="p-4 mx-6 my-6 text-red-600 bg-white border rounded-xl border-slate-200">Utilizatorul #'.(int)$prof_id.' nu este profesor sau nu există.</div>';
    return;
  }

  // permisiuni: self OR admin OR tutor alocat
  $allowed = false;
  if ($is_admin) {
    $allowed = true;
  } elseif ($uid === $prof_id && $is_prof) {
    $allowed = true;
  } elseif ($is_tutor && es_tutor_owns_prof($prof_id, $uid)) {
    $allowed = true;
  }

  if (!$allowed) {
    echo '<div class="p-4 mx-6 my-6 text-red-600 bg-white border rounded-xl border-slate-200">Nu ai permisiuni pentru profilul acestui profesor.</div>';
    return;
  }

  // pentru a refolosi profesor-profile.php (care verifică rolul/propriul user), impersonăm profesorul țintă
  if (file_exists($prof_tpl)) {
    es_include_as_user($prof_id, $prof_tpl, $debug);
  } else {
    echo '<div class="p-4 mx-6 my-6 text-red-600 bg-white border rounded-xl border-slate-200">Lipsește fișierul <code>dashboard/profesor-profile.php</code>.</div>';
  }
  return;
}

/* ====== 2) /panou/tutor/{id} ====== */
if ($tutor_id > 0) {
  if (!es_is_tutor_user($tutor_id)) {
    echo '<div class="p-4 mx-6 my-6 text-red-600 bg-white border rounded-xl border-slate-200">Utilizatorul #'.(int)$tutor_id.' nu este tutor sau nu există.</div>';
    return;
  }

  // permisiuni: self OR admin OR profesor care îl are alocat pe acest tutor
  $allowed = false;
  if ($is_admin) {
    $allowed = true;
  } elseif ($uid === $tutor_id && $is_tutor) {
    $allowed = true;
  } elseif ($is_prof && es_prof_has_tutor($uid, $tutor_id)) {
    $allowed = true;
  }

  if (!$allowed) {
    echo '<div class="p-4 mx-6 my-6 text-red-600 bg-white border rounded-xl border-slate-200">Nu ai permisiuni pentru profilul acestui tutor.</div>';
    return;
  }

  // Refolosim tutor-profile.php ⇒ impersonăm tutorul țintă ca să treacă verificările din template
  if (file_exists($tutor_tpl)) {
    es_include_as_user($tutor_id, $tutor_tpl, $debug);
  } else {
    echo '<div class="p-4 mx-6 my-6 text-red-600 bg-white border rounded-xl border-slate-200">Lipsește fișierul <code>dashboard/tutor-profile.php</code>.</div>';
  }
  return;
}

/* ====== 3) Fără ID în URL: păstrăm comportamentul tău actual ====== */
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;
$dashboard_path = get_template_directory() . '/dashboard/';

switch ($user_roles[0] ?? '') {
  case 'profesor':
    include_once $dashboard_path . 'profesor-profile.php';
    break;
  case 'alumni':
    include_once $dashboard_path . 'alumni-profile.php';
    break;
  case 'non-teach':
    include_once $dashboard_path . 'non-teach-profile.php';
    break;
  case 'tutor':
    include_once $dashboard_path . 'tutor-profile.php';
    break;    
  case 'admin':
    include_once $dashboard_path . 'admin-profile.php';
    break;
  case 'administrator':
    include_once $dashboard_path . 'admin-profile.php';
    break;
  default:
    echo '<p class="px-6 py-4 text-gray-700">Rol necunoscut. Te rugăm să contactezi un administrator.</p>';
    break;
}
