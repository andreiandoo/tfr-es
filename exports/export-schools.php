<?php
// Export CSV pentru Manager Școli (full, fără "Acțiuni")
if (!defined('ABSPATH')) exit;

if (!function_exists('es_send_csv')) {
  status_header(500);
  echo 'Lipsește es_send_csv() în functions.php.';
  exit;
}

global $wpdb;
$tbl_schools  = $wpdb->prefix . 'edu_schools';
$tbl_cities   = $wpdb->prefix . 'edu_cities';
$tbl_counties = $wpdb->prefix . 'edu_counties';

// === Filtre primite din URL (trimise de butonul Export) ===
$s       = isset($_GET['s'])      ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$county  = isset($_GET['county']) ? (int)$_GET['county'] : 0;
$city    = isset($_GET['city'])   ? (int)$_GET['city']   : 0;

// === WHERE dinamic ===
$where  = 'WHERE 1=1';
$params = [];

if ($s !== '') {
  $like = '%' . $wpdb->esc_like($s) . '%';
  // căutăm în: denumire, scurt, cod, județ, oraș, comună/sat
  $where .= " AND (
    s.name LIKE %s OR s.short_name LIKE %s OR CAST(s.cod AS CHAR) LIKE %s
    OR ct.name LIKE %s OR c.name LIKE %s OR v.name LIKE %s
  )";
  array_push($params, $like, $like, $like, $like, $like, $like);
}
if ($county > 0) {
  $where .= " AND ct.id = %d";
  $params[] = $county;
}
if ($city > 0) {
  $where .= " AND c.id = %d";
  $params[] = $city;
}

// === Query complet (fără LIMIT) ===
$sql = "
  SELECT s.*,
         c.name  AS city_name,
         c.id    AS city_id,
         c.parent_city_id,
         ct.id   AS county_id,
         ct.name AS county_name,
         v.name  AS village_name
  FROM {$tbl_schools} s
  JOIN {$tbl_cities}   c  ON s.city_id = c.id
  JOIN {$tbl_counties} ct ON c.county_id = ct.id
  LEFT JOIN {$tbl_cities} v ON s.village_id = v.id
  {$where}
  ORDER BY ct.name, c.name, s.name
";

$rows = !empty($params) ? $wpdb->get_results($wpdb->prepare($sql, $params))
                        : $wpdb->get_results($sql);

// === Headere CSV (fără col. "Acțiuni") ===
$headers = [
  'ID', 'Cod SIIIR', 'Denumire', 'Denumire scurtă',
  'Județ', 'Oraș', 'Comună/Sat', 'Mediu',
  'Regiune TFR', 'Statut',
  'Medie IRSE', 'Scor IRSE', 'Strategică',
  'Index vulnerabilitate TFR', 'Număr elevi SIIIR'
];

// === Rânduri
$out = [];
foreach ($rows as $r) {
  // mediu: folosește câmpul stocat sau deduce (fallback)
  $mediu = $r->mediu ?: (( (int)$r->village_id > 0 || (int)$r->parent_city_id > 0 ) ? 'rural' : 'urban');
  $out[] = [
    (int)$r->id,
    ($r->cod !== null ? (int)$r->cod : ''),
    (string)$r->name,
    ($r->short_name ?: ''),
    (string)$r->county_name,
    (string)$r->city_name,
    ($r->village_name ?: ''),
    $mediu,
    ($r->regiune_tfr ?: ''),
    ($r->statut ?: ''),
    ($r->medie_irse !== null ? (float)$r->medie_irse : ''),
    ($r->scor_irse  !== null ? (float)$r->scor_irse  : ''),
    ((int)$r->strategic === 1 ? 'Da' : 'Nu'),
    ($r->index_vulnerabilitate_tfr !== null ? (float)$r->index_vulnerabilitate_tfr : ''),
    ($r->numar_elevi_siiir !== null ? (int)$r->numar_elevi_siiir : ''),
  ];
}

// === Trimite CSV
$filename = 'scoli_' . date('Y-m-d_His') . '.csv';
es_send_csv($filename, $headers, $out);
