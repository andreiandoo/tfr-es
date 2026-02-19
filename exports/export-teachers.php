<?php
// Export profesori CSV (full, fără "Profil")

if (!function_exists('es_send_csv')) {
  status_header(500);
  echo 'Lipsește funcția es_send_csv(). Verifică functions.php.';
  exit;
}

global $wpdb;

$tbl_users       = $wpdb->users;
$tbl_generations = $wpdb->prefix . 'edu_generations';
$tbl_students    = $wpdb->prefix . 'edu_students';
$tbl_schools     = $wpdb->prefix . 'edu_schools';
$tbl_cities      = $wpdb->prefix . 'edu_cities';
$tbl_counties    = $wpdb->prefix . 'edu_counties';

// ------- helpers locale (cu guard) -------
if (!function_exists('es_normalize_level_code')) {
  function es_normalize_level_code($raw){
    $c = strtolower(trim((string)$raw));
    if ($c === 'primar-mic' || $c === 'primar mare' || $c === 'primar-mare') $c = 'primar';
    if ($c === 'gimnaziu') $c = 'gimnazial';
    if ($c === 'preșcolar' || $c === 'prescolari' || $c === 'preșcolari') $c = 'prescolar';
    return in_array($c, ['prescolar','primar','gimnazial','liceu'], true) ? $c : ($c ?: '');
  }
}
if (!function_exists('es_level_label')) {
  function es_level_label($code){
    $map = ['prescolar'=>'Preșcolar','primar'=>'Primar','gimnazial'=>'Gimnazial','liceu'=>'Liceu'];
    $code = es_normalize_level_code($code);
    return $map[$code] ?? '—';
  }
}
if (!function_exists('es_format_dt')) {
  function es_format_dt($ts_or_str){
    if (!$ts_or_str) return '—';
    $ts = is_numeric($ts_or_str) ? (int)$ts_or_str : strtotime((string)$ts_or_str);
    if (!$ts) return '—';
    return date_i18n(get_option('date_format').' '.get_option('time_format'), $ts);
  }
}
if (!function_exists('es_user_fullname')) {
  function es_user_fullname($u){
    $name = trim(($u->first_name ?? '').' '.($u->last_name ?? ''));
    if ($name === '') $name = $u->display_name ?: $u->user_login;
    return $name;
  }
}

// ------- filtre din GET (identice cu pagina) -------
$s          = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$nivel      = isset($_GET['nivel']) ? sanitize_text_field(wp_unslash($_GET['nivel'])) : '';
$statut     = isset($_GET['statut']) ? sanitize_text_field(wp_unslash($_GET['statut'])) : '';
$gen_year   = isset($_GET['gen_year']) ? sanitize_text_field(wp_unslash($_GET['gen_year'])) : '';
$county_f   = isset($_GET['county']) ? sanitize_text_field(wp_unslash($_GET['county'])) : '';
$an_program = isset($_GET['an_program']) ? sanitize_text_field(wp_unslash($_GET['an_program'])) : '';
$rsoi       = isset($_GET['rsoi']) ? sanitize_text_field(wp_unslash($_GET['rsoi'])) : '';

// ------- permisiuni tutor (opțional) -------
$user     = wp_get_current_user();
$is_admin = current_user_can('manage_options');
$is_tutor = in_array('tutor', (array)($user->roles ?? []), true);

// ------- WP_User_Query: rol profesor, number=-1 -------
$meta_query = ['relation' => 'AND'];

if ($is_tutor && !$is_admin) {
  $meta_query[] = ['key'=>'assigned_tutor_id','value'=>(int)$user->ID,'compare'=>'=','type'=>'NUMERIC'];
}
if ($statut !== '') {
  $meta_query[] = [
    'relation'=>'OR',
    ['key'=>'user_status_profesor','value'=>$statut,'compare'=>'='],
    ['key'=>'statut_prof','value'=>$statut,'compare'=>'='],
    ['key'=>'statut','value'=>$statut,'compare'=>'='],
  ];
}
if ($an_program !== '') {
  $meta_query[] = ['key'=>'an_program','value'=>$an_program,'compare'=>'='];
}
if ($rsoi !== '') {
  $meta_query[] = ['key'=>'segment_rsoi','value'=>$rsoi,'compare'=>'='];
}

$args = [
  'role'       => 'profesor',
  'number'     => -1,
  'orderby'    => 'display_name',
  'order'      => 'ASC',
  'meta_query' => $meta_query,
];

if ($s !== '') {
  $args['search']         = '*'.esc_attr($s).'*';
  $args['search_columns'] = ['user_login','user_nicename','user_email','display_name'];
}

$user_query = new WP_User_Query($args);
$all_prof   = $user_query->get_results(); // array<WP_User>

// ------- preluări asociate (generații, elevi, județe) -------
$prof_ids = array_map(fn($u)=>(int)$u->ID, $all_prof);

$gens_by_prof   = [];
$students_count = [];
$counties_by_prof = [];

// generații
if ($prof_ids) {
  $in = implode(',', array_fill(0, count($prof_ids), '%d'));
  $gens = $wpdb->get_results($wpdb->prepare("
    SELECT id, professor_id, name, level, year
    FROM {$tbl_generations}
    WHERE professor_id IN ($in)
    ORDER BY year DESC, id DESC
  ", ...$prof_ids));
  foreach ($gens as $g) {
    $pid = (int)$g->professor_id;
    $gens_by_prof[$pid] ??= [];
    $gens_by_prof[$pid][] = $g;
  }

  // elevi per profesor
  $sc = $wpdb->get_results($wpdb->prepare("
    SELECT professor_id, COUNT(*) AS total
    FROM {$tbl_students}
    WHERE professor_id IN ($in)
    GROUP BY professor_id
  ", ...$prof_ids));
  foreach ($sc as $row) {
    $students_count[(int)$row->professor_id] = (int)$row->total;
  }

  // județe din școlile asignate
  $school_ids_all = [];
  foreach ($prof_ids as $pid) {
    $sids = get_user_meta($pid, 'assigned_school_ids', true);
    if (is_array($sids)) {
      foreach ($sids as $sid) { $sid=(int)$sid; if ($sid>0) $school_ids_all[$sid]=true; }
    }
  }
  $school_ids_all = array_keys($school_ids_all);

  $county_by_school = [];
  if ($school_ids_all) {
    $in2 = implode(',', array_fill(0, count($school_ids_all), '%d'));
    $rows = $wpdb->get_results($wpdb->prepare("
      SELECT s.id AS school_id, j.name AS county_name
      FROM {$tbl_schools} s
      LEFT JOIN {$tbl_cities} c  ON s.city_id = c.id
      LEFT JOIN {$tbl_counties} j ON c.county_id = j.id
      WHERE s.id IN ($in2)
    ", ...$school_ids_all));
    foreach ($rows as $r) {
      $county_by_school[(int)$r->school_id] = (string)$r->county_name;
    }
  }

  foreach ($prof_ids as $pid) {
    $sids = get_user_meta($pid, 'assigned_school_ids', true);
    $set = [];
    if (is_array($sids)) {
      foreach ($sids as $sid) {
        $sid = (int)$sid;
        if ($sid>0 && isset($county_by_school[$sid])) {
          $nm = trim((string)$county_by_school[$sid]);
          if ($nm !== '') $set[$nm] = true;
        }
      }
    }
    $counties_by_prof[$pid] = array_keys($set);
  }
}

// ------- filtre manuale suplimentare (nivel, an generație, județ) -------
$filtered = $all_prof;

// nivel
if ($nivel !== '') {
  $nivel_code = es_normalize_level_code($nivel);
  $filtered = array_values(array_filter($filtered, function($u) use ($nivel_code){
    $raw = get_user_meta((int)$u->ID, 'nivel_predare', true);
    if (is_array($raw)) {
      foreach ($raw as $rv) if (es_normalize_level_code($rv) === $nivel_code) return true;
      return false;
    }
    return es_normalize_level_code($raw) === $nivel_code;
  }));
}

// an generație
if ($gen_year !== '') {
  $filtered = array_values(array_filter($filtered, function($u) use ($gens_by_prof, $gen_year){
    $pid = (int)$u->ID;
    if (empty($gens_by_prof[$pid])) return false;
    foreach ($gens_by_prof[$pid] as $g) {
      if ((string)$g->year === (string)$gen_year) return true;
    }
    return false;
  }));
}

// județ
if ($county_f !== '') {
  $filtered = array_values(array_filter($filtered, function($u) use ($counties_by_prof, $county_f){
    $pid = (int)$u->ID;
    $ctys = $counties_by_prof[$pid] ?? [];
    return in_array($county_f, $ctys, true);
  }));
}

// sortare finală
usort($filtered, fn($a,$b)=> strcasecmp($a->display_name, $b->display_name));

// ------- pregătim CSV -------
$headers = [
  'ID','Nume','Email','Cod SLF','Statut','Nivel predare',
  'An program','RSOI','Teach','Materie','Județ(e)',
  '#Elevi','Generații (id·nivel·an)',
  'Ultima activitate','Înregistrare','Tutor'
];

$rows = [];
foreach ($filtered as $u) {
  $pid  = (int)$u->ID;
  $name = trim(($u->first_name ?: $u->display_name).' '.($u->last_name ?: ''));
  if ($name === '') $name = $u->display_name ?: $u->user_login;

  $cod     = get_user_meta($pid, 'cod_slf', true);
  $nivel_v = get_user_meta($pid, 'nivel_predare', true);
  $mat     = get_user_meta($pid, 'materia_predata', true);
  $an_prog = get_user_meta($pid, 'an_program', true);
  $rsoi_v  = get_user_meta($pid, 'segment_rsoi', true);
  $teach_v = get_user_meta($pid, 'generatie', true);

  $stat = get_user_meta($pid, 'user_status_profesor', true);
  if ($stat==='') $stat = get_user_meta($pid, 'statut_prof', true);
  if ($stat==='') $stat = get_user_meta($pid, 'statut', true);

  // tutor
  $tid   = (int) get_user_meta($pid, 'assigned_tutor_id', true);
  $tname = '—';
  if ($tid > 0) {
    $tu = get_userdata($tid);
    if ($tu) $tname = es_user_fullname($tu);
  }

  // județe
  $ctys = $counties_by_prof[$pid] ?? [];
  $cty_str = $ctys ? implode('; ', $ctys) : '';

  // elevi
  $elevi = (int)($students_count[$pid] ?? 0);

  // generații
  $gen_bits = [];
  if (!empty($gens_by_prof[$pid])) {
    foreach ($gens_by_prof[$pid] as $g) {
      $gen_bits[] = '#'.$g->id.'·'.es_level_label($g->level).'·'.$g->year;
    }
  }

  // activitate / înregistrare
  $last = get_user_meta($pid,'last_activity',true);
  if (!$last) $last = get_user_meta($pid,'last_login',true);
  if (!$last) $last = get_user_meta($pid,'last_seen',true);
  if (!$last) $last = strtotime($u->user_registered);

  $reg_ts = $u->user_registered ? strtotime($u->user_registered) : 0;

  $rows[] = [
    $pid,
    $name,
    $u->user_email,
    $cod ?: '',
    $stat ?: '',
    es_level_label($nivel_v),
    $an_prog ?: '',
    $rsoi_v ?: '',
    $teach_v ?: '',
    $mat ?: '',
    $cty_str,
    $elevi,
    implode(' | ', $gen_bits),
    es_format_dt($last),
    es_format_dt($reg_ts),
    $tname,
  ];
}

// trimite CSV (fără col. "Profil")
$filename = 'profesori_' . date('Y-m-d_His') . '.csv';
es_send_csv($filename, $headers, $rows);
