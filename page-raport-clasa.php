<?php
/* Template Name: Raport Generație */
include get_template_directory() . '/partials/logged-styles.php';

/** === Context user === */
$current_user = wp_get_current_user();
$uid          = (int) ($current_user->ID ?? 0);
$roles        = (array) ($current_user->roles ?? []);
$is_admin     = current_user_can('manage_options');
$is_tutor     = in_array('tutor', $roles, true);
$is_prof      = in_array('profesor', $roles, true);
$debug        = !empty($_GET['debug']);

/** === DB === */
global $wpdb;
$tbl_generations = $wpdb->prefix . 'edu_generations';
$tbl_students    = $wpdb->prefix . 'edu_students';

/** === Parametru din rewrite: generation_id (cu fallback-uri prietenoase) === */
$generation_id = (int)(
    get_query_var('generation_id')
 ?: ($_GET['generation_id'] ?? 0)
 ?: get_query_var('gen')
 ?: ($_GET['gen'] ?? 0)
);
if ($generation_id <= 0) {
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">Generație invalidă (lipsește ID-ul).</p></div>';
  get_footer('blank');
  return;
}

/** === Citește generația === */
$generation = $wpdb->get_row(
  $wpdb->prepare("SELECT * FROM {$tbl_generations} WHERE id = %d LIMIT 1", $generation_id)
);
if (!$generation) {
  status_header(404);
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">Generația nu a fost găsită.</p></div>';
  get_footer('blank');
  return;
}

$owner_id = (int) ($generation->professor_id ?? 0);
$assigned_tutor_id = (int) get_user_meta($owner_id, 'assigned_tutor_id', true);

/** === Verifică permisiuni: ADMIN || (PROF & owner) || (TUTOR & alocat) === */
$can_view = false;
$viewer_mode = 'UNKNOWN';

if ($is_admin) {
  $can_view = true;
  //$viewer_mode = 'ADMIN';
  $viewer_mode = 'TUTOR';
} elseif ($is_prof && $uid === $owner_id) {
  $can_view = true;
  $viewer_mode = 'PROF';
} elseif ($is_tutor && $assigned_tutor_id === $uid) {
  $can_view = true;
  $viewer_mode = 'TUTOR';
}

if (!$can_view) {
  status_header(403);
  echo '<div class="max-w-5xl p-6 mx-auto"><p class="text-red-600">Nu ai permisiuni pentru această generație.</p></div>';
  get_footer('blank');
  return;
}

/** (Opțional) statistici rapide */
$students_count = (int) $wpdb->get_var(
  $wpdb->prepare("SELECT COUNT(*) FROM {$tbl_students} WHERE generation_id = %d", $generation_id)
);

/** === Variabile disponibile în partial === */
$GEN = [
  'id'        => $generation_id,
  'name'      => $generation->name ?? '',
  'school'    => $generation->school ?? '',
  'year'      => $generation->year ?? '',
  'owner_id'  => $owner_id,
  'students'  => $students_count,
  'raw'       => $generation,
];
$VIEW = [
  'mode'      => $viewer_mode,          // 'ADMIN' | 'PROF' | 'TUTOR'
  'can_view'  => $can_view,             // true
  'can_edit'  => ($viewer_mode === 'ADMIN' || $viewer_mode === 'PROF'), // dacă vei avea acțiuni editabile în raport
];

if ($debug) {
  echo '<div class="max-w-5xl p-4 mx-auto my-4 text-xs rounded-xl bg-slate-900 text-slate-100">';
  echo '<div><strong>Mode:</strong> ' . esc_html($viewer_mode) . '</div>';
  echo '<div><strong>logged_user_id:</strong> ' . (int)$uid . ' | <strong>logged_roles:</strong> ' . esc_html(implode(',', $roles)) . '</div>';
  echo '<div><strong>owner_professor_id:</strong> ' . (int)$owner_id . ' | <strong>assigned_tutor_id (owner):</strong> ' . (int)$assigned_tutor_id . '</div>';
  echo '<div><strong>generation_id:</strong> ' . (int)$generation_id . ' | <strong>students_count:</strong> ' . (int)$students_count . '</div>';
  echo '<div><strong>can_edit:</strong> ' . ($VIEW['can_edit'] ? 'DA' : 'NU') . '</div>';
  echo '</div>';
}
?>

<div x-data="{ sidebarOpen: true }" class="<?php echo $main_classes; ?>">
  <?php include get_template_directory() . '/partials/dashboard-sidebar.php'; ?>

  <!-- Conținut principal -->
  <main :class="sidebarOpen ? 'w-full' : 'w-[calc(100%-4rem)]'" class="<?php echo $sidebar_classes; ?>">
    <?php include get_template_directory() . '/partials/dashboard-topbar.php'; ?>  

    <div class="<?php echo $panel_classes; ?>">
      <?php
        include get_template_directory() . '/partials/panou-raport-generatie.php';
      ?>

      <?php get_footer('blank'); ?>
