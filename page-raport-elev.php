<?php
/* Template Name: Raport Elev */

include get_template_directory() . '/partials/logged-styles.php';

global $wpdb, $wp_query;

/* ----------------------------- Helpers ----------------------------- */
function edus_resolve_student_id(): int {
  // 1) pretty permalinks / query vars standard
  foreach (['student_id','elev','student','id'] as $k) {
    $v = get_query_var($k);
    if ($v && (int)$v > 0) return (int)$v;
  }
  // 2) GET fallback
  foreach (['student_id','elev','student','id'] as $k) {
    if (!empty($_GET[$k]) && (int)$_GET[$k] > 0) return (int)$_GET[$k];
  }
  // 3) orice var numeric din query_vars
  global $wp_query;
  if (!empty($wp_query->query_vars)) {
    foreach ($wp_query->query_vars as $k => $v) {
      if (is_scalar($v) && ctype_digit((string)$v)) {
        $n = (int)$v; if ($n>0) return $n;
      }
    }
  }
  // 4) regex din REQUEST_URI
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  if ($uri && preg_match('~(?:^|/)(\d+)(?:/)?(?:\?.*)?$~', $uri, $m)) {
    $n = (int)$m[1]; if ($n>0) return $n;
  }
  return 0;
}

/* ----------------------------- Context ----------------------------- */
$logged_user   = wp_get_current_user();
$logged_id     = (int) ($logged_user->ID ?? 0);
$logged_roles  = (array) ($logged_user->roles ?? []);
$is_adminlike  = current_user_can('manage_options');
$is_tutor_role = in_array('tutor', $logged_roles, true);
$is_prof_role  = in_array('profesor', $logged_roles, true);
$debug         = !empty($_GET['debug']);

/* ----------------------------- Tabele ----------------------------- */
$tbl_students    = $wpdb->prefix . 'edu_students';
$tbl_generations = $wpdb->prefix . 'edu_generations';

/* ----------------------------- student_id ----------------------------- */
$student_id = edus_resolve_student_id();
if ($student_id <= 0) {
  status_header(400);
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">ID elev invalid sau lipsă.</p></div>';
  if ($debug) {
    echo '<pre class="max-w-5xl p-4 mx-auto mt-2 text-xs rounded bg-slate-900 text-slate-100">'
       . "DEBUG resolve failed\n"
       . 'query_vars=' . esc_html(print_r($wp_query->query_vars, true))
       . "\nGET=" . esc_html(print_r($_GET, true))
       . "\nURI=" . esc_html($_SERVER['REQUEST_URI'] ?? '') . '</pre>';
  }
  get_footer('blank'); return;
}

/* ----------------------------- Fetch elev + generație ----------------------------- */
$last_err = '';
$sql_join = $wpdb->prepare("
  SELECT
    s.*,
    g.id            AS gen_id,
    g.professor_id  AS gen_owner_id,
    g.name          AS gen_name,
    g.year          AS gen_year,
    g.level         AS gen_level,
    g.school        AS gen_school
  FROM {$tbl_students} s
  LEFT JOIN {$tbl_generations} g ON g.id = s.generation_id
  WHERE s.id = %d
  LIMIT 1
", $student_id);

$raw = $wpdb->get_row($sql_join);
$last_err = $wpdb->last_error;

// Fallback diagnostic: elevul există fără JOIN?
$sql_exists = $wpdb->prepare("SELECT COUNT(*) FROM {$tbl_students} WHERE id=%d", $student_id);
$exists = (int) $wpdb->get_var($sql_exists);
$last_err2 = $wpdb->last_error;

// Dacă JOIN nu a întors rând dar elevul există, ia în 2 pași
if (!$raw && $exists > 0) {
  $s = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_students} WHERE id=%d", $student_id));
  $g = null;
  if ($s && !empty($s->generation_id)) {
    $g = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_generations} WHERE id=%d", (int)$s->generation_id));
  }
  if ($s) {
    $raw = (object) array_merge((array)$s, [
      'gen_id'        => (int)($g->id ?? 0),
      'gen_owner_id'  => (int)($g->professor_id ?? 0),
      'gen_name'      => $g->name  ?? '',
      'gen_year'      => $g->year  ?? '',
      'gen_level'     => $g->level ?? '',
      'gen_school'    => $g->school ?? '',
    ]);
  }
}

if (!$raw) {
  status_header(404);
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">Elevul nu a fost găsit.</p></div>';
  if ($debug) {
    // scoatem primele 5 ID-uri ca să confirmăm prefixul/tabela
    $probe_ids = $wpdb->get_col("SELECT id FROM {$tbl_students} ORDER BY id DESC LIMIT 5");
    echo '<pre class="max-w-5xl p-4 mx-auto mt-2 text-xs rounded bg-slate-900 text-slate-100">'
       . "DEBUG\n"
       . 'student_id=' . (int)$student_id . "\n"
       . 'tbl_students=' . esc_html($tbl_students) . "\n"
       . 'tbl_generations=' . esc_html($tbl_generations) . "\n\n"
       . "[SQL join]\n" . esc_html($sql_join) . "\n"
       . 'last_error=' . esc_html($last_err) . "\n\n"
       . "[SQL exists]\n" . esc_html($sql_exists) . "\n"
       . 'last_error2=' . esc_html($last_err2) . "\n\n"
       . 'probe_ids=' . esc_html(print_r($probe_ids, true))
       . '</pre>';
  }
  get_footer('blank'); return;
}

$generation_id     = (int) ($raw->gen_id ?? 0);
$owner_prof_id     = (int) ($raw->gen_owner_id ?? 0);
$assigned_tutor_id = (int) get_user_meta($owner_prof_id, 'assigned_tutor_id', true);

if ($generation_id <= 0 || $owner_prof_id <= 0) {
  status_header(404);
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">Elevul nu are asociată o generație validă.</p></div>';
  if ($debug) {
    echo '<pre class="max-w-5xl p-4 mx-auto mt-2 text-xs rounded bg-slate-900 text-slate-100">'
       . 'raw=' . esc_html(print_r($raw, true)) . '</pre>';
  }
  get_footer('blank'); return;
}

/* ----------------------------- Permisiuni ----------------------------- */
$allowed = false; $viewer_mode = 'UNKNOWN';
if ($is_adminlike)                               { $allowed = true; $viewer_mode = 'ADMIN'; }
elseif ($logged_id === $owner_prof_id && $is_prof_role) { $allowed = true; $viewer_mode = 'PROF'; }
elseif ($is_tutor_role && $assigned_tutor_id === $logged_id) { $allowed = true; $viewer_mode = 'TUTOR'; }

if (!$allowed) {
  status_header(403);
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">Nu ai permisiuni pentru acest raport de elev.</p></div>';
  if ($debug) {
    echo '<pre class="max-w-5xl p-4 mx-auto mt-2 text-xs rounded bg-slate-900 text-slate-100">'
       . 'logged_id='.(int)$logged_id.' roles='.esc_html(implode(',', $logged_roles))."\n"
       . 'owner_prof_id='.(int)$owner_prof_id.' assigned_tutor_id='.(int)$assigned_tutor_id."\n"
       . '</pre>';
  }
  get_footer('blank'); return;
}

/* ----------------------------- Debug panel (opțional) ----------------------------- */
if ($debug) {
  echo '<div class="max-w-5xl p-4 mx-auto my-4 text-xs rounded-xl bg-slate-900 text-slate-100">';
  echo '<div><strong>Mode:</strong> ' . esc_html($viewer_mode) . '</div>';
  echo '<div><strong>logged_user_id:</strong> ' . (int)$logged_id . ' | <strong>logged_roles:</strong> ' . esc_html(implode(',', $logged_roles)) . '</div>';
  echo '<div><strong>student_id:</strong> ' . (int)$student_id . ' | <strong>generation_id:</strong> ' . (int)$generation_id . '</div>';
  echo '<div><strong>owner_prof_id:</strong> ' . (int)$owner_prof_id . ' | <strong>assigned_tutor_id(owner):</strong> ' . (int)$assigned_tutor_id . '</div>';
  echo '</div>';
}

/* ----------------------------- Variabile pentru partial ----------------------------- */
$STUDENT = $raw;
$GENERATION = (object)[
  'id'           => $generation_id,
  'professor_id' => $owner_prof_id,
  'name'         => $raw->gen_name ?? '',
  'year'         => $raw->gen_year ?? '',
  'level'        => $raw->gen_level ?? '',
  'school'       => $raw->gen_school ?? '',
];
?>

<div x-data="{ sidebarOpen: true }" class="<?php echo $main_classes; ?>">
  <?php include get_template_directory() . '/partials/dashboard-sidebar.php'; ?>

  <main :class="sidebarOpen ? 'w-full' : 'w-[calc(100%-4rem)]'" class="<?php echo $sidebar_classes; ?>">
    <?php include get_template_directory() . '/partials/dashboard-topbar.php'; ?>  

    <div class="<?php echo $panel_classes; ?>">
      <?php include get_template_directory() . '/partials/panou-raport-elev.php'; ?>
    

      <?php get_footer('blank'); ?>
