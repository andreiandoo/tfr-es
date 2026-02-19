<?php
/** Helpers & calcule pentru LIT.
 *  Depinde de: $wpdb, $student, $rowsRaw, $tbl_students, $tbl_results
 *  Necesită: raport-elev-helpers-common.php deja încărcat.
 */

/* ------------------------------------------------------------------ *
 *  SCHEME
 * ------------------------------------------------------------------ */
$LIT_SCHEMES = [
  'prescolar' => [
    'total_max' => 51,
    'per_max'   => [ 'lit_q1'=>22, 'lit_q2'=>20, 'lit_q3'=>3, 'lit_q4'=>5, 'lit_q5'=>1 ],
    'categoric' => [] // toate numerice aici
  ],
  'pregatitoare' => [
    'total_max' => 150,
    'per_max'   => [ 'lit_q7'=>6, 'lit_q8'=>93, 'lit_q9'=>12, 'lit_q10'=>22, 'lit_q11'=>12, 'lit_q12'=>5 ],
    'categoric' => []
  ],
  'primar_gimnaziu' => [
    'total_max' => 150,
    // numerice:
    'per_max'   => [
      'lit_q3'=>100, 'lit_q6'=>5, 'lit_q7'=>6, 'lit_q8'=>93,
      'lit_q9'=>12, 'lit_q10'=>22, 'lit_q11'=>12, 'lit_q12'=>5
    ],
    // categorice normalizate 0–100, folosind intervalul [-1..4]
    // (NU schimbăm normalizarea existentă; cerința de PP=-2/P=-1 e folosită doar la calcule de Δ, nu la procente)
    'categoric' => [
      'lit_q1'=>['min'=>-1,'max'=>4],
      'lit_q2'=>['min'=>-1,'max'=>4],
      'lit_q4'=>['min'=>-1,'max'=>4],
      'lit_q5'=>['min'=>-1,'max'=>4],
    ],
  ],
];

function lit_pick_scheme_key($class_label){
  $g = edus_grade_number_from_classlabel($class_label);
  if ($g === -1) return 'prescolar';
  if ($g === 0)  return 'pregatitoare';
  return 'primar_gimnaziu';
}

function lit_parse_score($row){
  $out = ['total'=>null,'breakdown'=>[]];
  $arr = maybe_unserialize($row->score ?? null);
  if (is_array($arr)){
    if (isset($arr['total']) && is_numeric($arr['total'])) $out['total'] = floatval($arr['total']);
    if (!empty($arr['breakdown']) && is_array($arr['breakdown'])){
      foreach ($arr['breakdown'] as $it){
        $out['breakdown'][] = [
          'name'  => $it['name']  ?? null,
          'label' => $it['label'] ?? ($it['name'] ?? ''),
          'value' => $it['value'] ?? null,
          'max'   => (isset($it['max']) && is_numeric($it['max'])) ? floatval($it['max']) : null,
        ];
      }
    }
  }
  return $out;
}

function lit_item_percent($item, $scheme){
  $name = $item['name']; $val = $item['value']; $max = $item['max'];
  if (is_numeric($val)){
    $v = floatval($val);
    $M = $max ?: ($scheme['per_max'][$name] ?? null);
    if ($M && $M > 0) return 100.0 * ($v / $M);
    return null;
  }
  $num = edus_level_string_to_num($val);
  if ($num !== null && isset($scheme['categoric'][$name])){
    $min = $scheme['categoric'][$name]['min']; $M = $scheme['categoric'][$name]['max'];
    $span = ($M - $min);
    if ($span > 0) return 100.0 * (($num - $min) / $span);
  }
  return null;
}

/* ------------------------------------------------------------------ *
 *  SELECTARE LIT T0/T1
 * ------------------------------------------------------------------ */
$rowsLIT = array_values(array_filter($rowsRaw ?? [], function($r){
  $mt = strtolower(trim($r->modul_type ?? ''));
  $m  = strtolower(trim($r->modul ?? ''));
  return ($mt==='literatie' || $mt==='lit' || str_starts_with($m,'literatie-') || str_starts_with($m,'lit-'));
}));

$lit_latest = ['t0'=>null,'t1'=>null];
$lit_key    = ['t0'=>null,'t1'=>null];

foreach ($rowsLIT as $r){
  $m  = strtolower(trim($r->modul ?? ''));
  $st = (strpos($m,'-t0')!==false) ? 't0' : ((strpos($m,'-t1')!==false) ? 't1' : null);
  if (!$st) continue;
  $k = row_order_key($r);
  if ($lit_key[$st]===null || $k > $lit_key[$st]){ $lit_key[$st]=$k; $lit_latest[$st]=$r; }
}

/* ------------------------------------------------------------------ *
 *  EXPUNE SCORUL BRUT din DB pentru T0/T1 (inclusiv 'remedial', 'dif_clasa' etc.)
 * ------------------------------------------------------------------ */
$lit_score_full = ['t0'=>[], 't1'=>[]];
foreach (['t0','t1'] as $st){
  $arr = [];
  if (!empty($lit_latest[$st]->score)){
    $tmp = @maybe_unserialize($lit_latest[$st]->score);
    if (is_array($tmp)) $arr = $tmp;
  }
  $lit_score_full[$st] = $arr;
}

/* ------------------------------------------------------------------ *
 *  SCHEMA + VALOARE CLASĂ
 * ------------------------------------------------------------------ */
$lit_scheme_key  = lit_pick_scheme_key($student->class_label ?? '');
$lit_scheme      = $LIT_SCHEMES[$lit_scheme_key];
$lit_grade_num   = edus_grade_number_from_classlabel($student->class_label ?? '');
$lit_class_value = max(0, min(8, ($lit_grade_num>=0 ? $lit_grade_num : 0))); // -1->0, 0->0, ..., 8->8

/* ------------------------------------------------------------------ *
 *  STATUS + COMPLETARE
 * ------------------------------------------------------------------ */
$lit_status = [
  't0' => $lit_latest['t0']->status ?? null,
  't1' => $lit_latest['t1']->status ?? null,
];
$lit_completion = [
  't0' => $lit_latest['t0'] ? intval($lit_latest['t0']->completion) : null,
  't1' => $lit_latest['t1'] ? intval($lit_latest['t1']->completion) : null,
];

/* ------------------------------------------------------------------ *
 *  TOTALURI & PROCENTE pe etape + colectare iteme brute
 * ------------------------------------------------------------------ */
$lit_stage = [
  't0' => ['total_points'=>null, 'total_pct'=>null, 'items'=>[], 'raw'=>[]],
  't1' => ['total_points'=>null, 'total_pct'=>null, 'items'=>[], 'raw'=>[]],
];

foreach (['t0','t1'] as $st){
  if (!$lit_latest[$st]) continue;
  $parsed = lit_parse_score($lit_latest[$st]);

  $tp = is_numeric($parsed['total']) ? floatval($parsed['total']) : null;
  if ($tp===null && !empty($parsed['breakdown'])){
    $sum=0; $had=false;
    foreach ($parsed['breakdown'] as $it){
      if (is_numeric($it['value'])) { $sum += floatval($it['value']); $had=true; }
    }
    if ($had) $tp = $sum;
  }

  $lit_stage[$st]['total_points'] = $tp;
  $lit_stage[$st]['total_pct']    = ($tp!==null) ? (100.0 * $tp / $lit_scheme['total_max']) : null;

  if (!empty($parsed['breakdown'])){
    foreach ($parsed['breakdown'] as $it){
      $name = $it['name'];
      $lit_stage[$st]['raw'][$name] = $it['value']; // păstrăm valoarea brută (PP/P/număr)
      $p = lit_item_percent($it, $lit_scheme);
      $lit_stage[$st]['items'][$name] = ['label'=>$it['label'], 'pct'=>$p];
    }
  }
}

/* Δ elev (total) + medie generală elev (folosită ca Scor Remedial) */
$lit_delta_total = (
  $lit_stage['t1']['total_pct']!==null && $lit_stage['t0']['total_pct']!==null
) ? ($lit_stage['t1']['total_pct'] - $lit_stage['t0']['total_pct']) : null;

$lit_overall_student_pct = avg_non_null([
  $lit_stage['t0']['total_pct'],
  $lit_stage['t1']['total_pct']
]);

/* ------------------------------------------------------------------ *
 *  AGREGARE GENERAȚIE + MEDIA DOAR PENTRU ELEVII REMEDIAL
 *  (Păstrăm calculele existente; în UI nu mai afișăm partea de generație pentru
 *  cardul Remedial, conform noilor cerințe)
 * ------------------------------------------------------------------ */
$lit_gen = [
  't0' => ['total_pct_sum'=>0,'total_pct_cnt'=>0, 'items'=>[]],
  't1' => ['total_pct_sum'=>0,'total_pct_cnt'=>0, 'items'=>[]],
];

$lit_gen_remedial = [
  't0' => ['total_pct_sum'=>0,'total_pct_cnt'=>0],
  't1' => ['total_pct_sum'=>0,'total_pct_cnt'=>0],
];

if (!empty($student->generation_id)){
  $peer_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT id FROM {$tbl_students} WHERE generation_id=%d",
    $student->generation_id
  ));

  if ($peer_ids){
    $peer_ids = array_map('intval',$peer_ids);
    $in = implode(',', array_fill(0, count($peer_ids), '%d'));

    $rowsGen = $wpdb->get_results($wpdb->prepare(
      "SELECT r.modul, r.score, s.class_label
       FROM {$tbl_results} r
       JOIN {$tbl_students} s ON s.id=r.student_id
       WHERE (LOWER(r.modul_type)='literatie' OR LOWER(r.modul_type)='lit'
              OR LOWER(r.modul) LIKE 'literatie-%' OR LOWER(r.modul) LIKE 'lit-%')
         AND r.student_id IN ($in)",
      ...$peer_ids
    ));

    foreach ($rowsGen as $gr){
      $stage = (strpos(strtolower($gr->modul),'-t0')!==false) ? 't0'
            : ((strpos(strtolower($gr->modul),'-t1')!==false) ? 't1' : null);
      if (!$stage) continue;

      $sk  = lit_pick_scheme_key($gr->class_label ?? '');
      $sch = $LIT_SCHEMES[$sk];

      $parsed = lit_parse_score($gr);

      // total % pe elev
      $tp = (isset($parsed['total']) && is_numeric($parsed['total'])) ? floatval($parsed['total']) : null;
      if ($tp!==null){
        $pct_total = 100.0 * $tp / $sch['total_max'];
        // media pe toți elevii generației
        $lit_gen[$stage]['total_pct_sum'] += $pct_total;
        $lit_gen[$stage]['total_pct_cnt'] += 1;

        // media DOAR pe elevii remedial
        $is_remedial = false;
        $arr_full = maybe_unserialize($gr->score ?? null);
        if (is_array($arr_full) && !empty($arr_full['remedial'])) $is_remedial = true;
        if (!$is_remedial && $sk === 'primar_gimnaziu'){
          // fallback: acuratețe PP/P => remedial
          $acc_raw = null;
          foreach ($parsed['breakdown'] as $it) {
            if (($it['name'] ?? '') === 'lit_q2') { $acc_raw = $it['value'] ?? null; break; }
          }
          $acc = strtoupper(trim((string)$acc_raw));
          if ($acc === 'PP' || $acc === 'P') $is_remedial = true;
        }
        if ($is_remedial){
          $lit_gen_remedial[$stage]['total_pct_sum'] += $pct_total;
          $lit_gen_remedial[$stage]['total_pct_cnt'] += 1;
        }
      }

      // per-item % (pt alte tabele)
      foreach ($parsed['breakdown'] as $it){
        $pct = lit_item_percent($it, $sch);
        if ($pct===null) continue;
        $n = $it['name'];
        if (!isset($lit_gen[$stage]['items'][$n])) {
          $lit_gen[$stage]['items'][$n] = ['sum'=>0,'cnt'=>0,'label'=>$it['label']];
        }
        $lit_gen[$stage]['items'][$n]['sum'] += $pct;
        $lit_gen[$stage]['items'][$n]['cnt'] += 1;
      }
    }
  }
}

$lit_gen_total_pct = [
  't0' => ($lit_gen['t0']['total_pct_cnt']>0) ? ($lit_gen['t0']['total_pct_sum']/$lit_gen['t0']['total_pct_cnt']) : null,
  't1' => ($lit_gen['t1']['total_pct_cnt']>0) ? ($lit_gen['t1']['total_pct_sum']/$lit_gen['t1']['total_pct_cnt']) : null,
];
$lit_gen_overall_pct = avg_non_null([$lit_gen_total_pct['t0'],$lit_gen_total_pct['t1']]);
$lit_gen_delta_total = ($lit_gen_total_pct['t0']!==null && $lit_gen_total_pct['t1']!==null)
  ? ($lit_gen_total_pct['t1'] - $lit_gen_total_pct['t0'])
  : null;

$lit_delta_student_vs_gen = [
  't0' => ($lit_stage['t0']['total_pct']!==null && $lit_gen_total_pct['t0']!==null) ? ($lit_stage['t0']['total_pct'] - $lit_gen_total_pct['t0']) : null,
  't1' => ($lit_stage['t1']['total_pct']!==null && $lit_gen_total_pct['t1']!==null) ? ($lit_stage['t1']['total_pct'] - $lit_gen_total_pct['t1']) : null,
];

/* --- Media de generație pentru REMEDIAL doar (pt cardul „Scor Remedial”) --- */
$lit_gen_total_pct_remedial = [
  't0' => ($lit_gen_remedial['t0']['total_pct_cnt']>0) ? ($lit_gen_remedial['t0']['total_pct_sum']/$lit_gen_remedial['t0']['total_pct_cnt']) : null,
  't1' => ($lit_gen_remedial['t1']['total_pct_cnt']>0) ? ($lit_gen_remedial['t1']['total_pct_sum']/$lit_gen_remedial['t1']['total_pct_cnt']) : null,
];
$lit_gen_overall_pct_remedial = avg_non_null([$lit_gen_total_pct_remedial['t0'],$lit_gen_total_pct_remedial['t1']]);
$lit_delta_overall_vs_gen_remedial = ($lit_overall_student_pct!==null && $lit_gen_overall_pct_remedial!==null)
  ? ($lit_overall_student_pct - $lit_gen_overall_pct_remedial)
  : null;

/* ------------------------------------------------------------------ *
 *  FOCUS ITEME cerute în raport
 * ------------------------------------------------------------------ */
$lit_focus_keys = [
  'lit_q1' => 'Lista de cuvinte',
  'lit_q2' => 'Acuratețe citire',
  'lit_q3' => 'CCPM (0–100)',
  'lit_q4' => 'Comprehensiune citire',
  'lit_q5' => 'Comprehensiune audiere',
  'lit_q6' => 'Scris (număr cuvinte corecte)',
];

$lit_focus = ['t0'=>[], 't1'=>[]];
foreach (['t0','t1'] as $st){
  foreach ($lit_focus_keys as $k=>$label){
    $raw = $lit_stage[$st]['raw'][$k] ?? null;
    $pct = $lit_stage[$st]['items'][$k]['pct'] ?? null;
    $lit_focus[$st][$k] = ['label'=>$label, 'raw'=>$raw, 'pct'=>$pct];
  }
}

/* ------------------------------------------------------------------ *
 *  REMEDIAL (primar/gimnaziu) — ia întâi din DB, apoi fallback din răspuns
 * ------------------------------------------------------------------ */
$lit_remedial = ['t0'=>false,'t1'=>false];
if ($lit_scheme_key === 'primar_gimnaziu'){
  foreach (['t0','t1'] as $st){
    if (!empty($lit_score_full[$st]['remedial'])) { $lit_remedial[$st] = true; continue; }
    $acc = strtoupper(trim((string)($lit_stage[$st]['raw']['lit_q2'] ?? '')));
    if ($acc === 'PP' || $acc === 'P') $lit_remedial[$st] = true;
  }
}

/* ------------------------------------------------------------------ *
 *  Diferențe față de valoarea clasei (nu pentru preșcolar)
 * ------------------------------------------------------------------ */
function _lit_level_numeric($raw){ return edus_level_string_to_num($raw); }

$lit_dif_clasa = [
  't0' => ['comp'=>null,'acc'=>null,'comp_color'=>null,'acc_color'=>null],
  't1' => ['comp'=>null,'acc'=>null,'comp_color'=>null,'acc_color'=>null],
];

$apply_diff = ($lit_scheme_key !== 'prescolar');

if ($apply_diff){
  foreach (['t0','t1'] as $st){
    $comp_raw = $lit_stage[$st]['raw']['lit_q4'] ?? null; // Comprehensiune citire
    $acc_raw  = $lit_stage[$st]['raw']['lit_q2'] ?? null; // Acuratețe citire

    $comp_lvl = _lit_level_numeric($comp_raw); // -1..4 sau null
    $acc_lvl  = _lit_level_numeric($acc_raw);

    $comp_d = ($comp_lvl!==null) ? ($comp_lvl - $lit_class_value) : null;
    $acc_d  = ($acc_lvl !==null) ? ($acc_lvl  - $lit_class_value) : null;

    // culori:
    // - comprehensiune: <0 roșu, ≥0 verde
    // - acuratețe: <0 roșu, ≥0 verde, iar dacă Acuratețe < Comprehensiune => roșu accentuat
    $comp_color = ($comp_d===null) ? null : (($comp_d<0)?'red':'green');
    $acc_color  = ($acc_d===null)  ? null : (($acc_d < 0)?'red':'green');
    if ($comp_lvl!==null && $acc_lvl!==null && $acc_lvl < $comp_lvl) $acc_color = 'red-strong';

    $lit_dif_clasa[$st] = [
      'comp' => $comp_d,
      'acc'  => $acc_d,
      'comp_color' => $comp_color,
      'acc_color'  => $acc_color,
    ];
  }
}

/* --- Medii pe calcule (nu pe raw) pentru Nivel Acuratețe/Comprehensiune (T0/T1) --- */
$lit_levels = [
  'acc'  => ['t0'=>$lit_stage['t0']['raw']['lit_q2'] ?? null, 't1'=>$lit_stage['t1']['raw']['lit_q2'] ?? null, 'avg_num'=>null],
  'comp' => ['t0'=>$lit_stage['t0']['raw']['lit_q4'] ?? null, 't1'=>$lit_stage['t1']['raw']['lit_q4'] ?? null, 'avg_num'=>null],
];
// păstrăm și media numerică a nivelurilor (informativ)
$acc_nums = array_filter([edus_level_string_to_num($lit_levels['acc']['t0']), edus_level_string_to_num($lit_levels['acc']['t1'])], function($v){ return $v!==null; });
$cmp_nums = array_filter([edus_level_string_to_num($lit_levels['comp']['t0']), edus_level_string_to_num($lit_levels['comp']['t1'])], function($v){ return $v!==null; });
$lit_levels['acc']['avg_num']  = !empty($acc_nums) ? array_sum($acc_nums)/count($acc_nums) : null;
$lit_levels['comp']['avg_num'] = !empty($cmp_nums) ? array_sum($cmp_nums)/count($cmp_nums) : null;

// medie pe Δ față de clasă (asta se cere în cardurile sus – păstrăm pentru alte zone unde e folosit)
$lit_levels_delta_avg = [
  'acc'  => avg_non_null([$lit_dif_clasa['t0']['acc'],  $lit_dif_clasa['t1']['acc']]),
  'comp' => avg_non_null([$lit_dif_clasa['t0']['comp'], $lit_dif_clasa['t1']['comp']]),
];
$lit_levels_delta_avg_color = [
  'acc'  => ($lit_levels_delta_avg['acc']===null ? null : ($lit_levels_delta_avg['acc']<0 ? 'red' : 'green')),
  'comp' => ($lit_levels_delta_avg['comp']===null ? null : ($lit_levels_delta_avg['comp']<0 ? 'red' : 'green')),
];

/* ------------------------------------------------------------------ *
 *  Map elev/gen pe fiecare întrebare (procente) pentru tabelul de bază
 * ------------------------------------------------------------------ */
$lit_questions_map = [];
foreach (['t0','t1'] as $st){
  foreach (($lit_stage[$st]['items'] ?? []) as $name=>$row){
    if (!isset($lit_questions_map[$name])){
      $lit_questions_map[$name] = ['label'=>$row['label'],'s_t0'=>null,'g_t0'=>null,'s_t1'=>null,'g_t1'=>null];
    }
    $lit_questions_map[$name]['s_'.$st] = $row['pct'];
  }
}
foreach (['t0','t1'] as $st){
  foreach (($lit_gen[$st]['items'] ?? []) as $name=>$agg){
    $avg = ($agg['cnt']>0) ? ($agg['sum']/$agg['cnt']) : null;
    if (!isset($lit_questions_map[$name])){
      $lit_questions_map[$name] = ['label'=>$agg['label'],'s_t0'=>null,'g_t0'=>null,'s_t1'=>null,'g_t1'=>null];
    }
    $lit_questions_map[$name]['g_'.$st] = $avg;
  }
}

/* ====================================================================== *
 *  EXPUNERE UTILITARĂ: separă „core” vs „condiționali” + medii de generație
 * ====================================================================== */
if (!function_exists('edus_lit_split_items_for_tables')) {
  function edus_lit_split_items_for_tables($litT0, $litT1, $generation_id) {
    global $wpdb;
    $core_keys = ['lit_q1','lit_q2','lit_q3','lit_q4','lit_q5','lit_q6'];
    $cond_keys = ['lit_q7','lit_q8','lit_q9','lit_q10','lit_q11','lit_q12'];

    $breakdown_by_name = function($score) {
      $bd = isset($score['breakdown']) && is_array($score['breakdown']) ? $score['breakdown'] : [];
      $out = [];
      foreach ($bd as $it) { if (!empty($it['name'])) $out[$it['name']] = $it; }
      return $out;
    };
    $bd0 = $breakdown_by_name($litT0 ?: []);
    $bd1 = $breakdown_by_name($litT1 ?: []);

    $norm = function($it) {
      $label = $it['label'] ?? '';
      $max   = isset($it['max']) && is_numeric($it['max']) ? (float)$it['max'] : null;
      $val   = $it['value'] ?? null;
      $pct   = null;
      if (is_numeric($val)) {
        $val = (float)$val;
        if ($max && $max > 0) $pct = ($val / $max) * 100.0;
      }
      return [$val, $max, $pct, $label];
    };

    $gen_avg_for = function($key) use ($wpdb, $generation_id) {
      if (!$generation_id) return [null, null, null];
      $students_table = $wpdb->prefix.'edu_students';
      $results_table  = $wpdb->prefix.'edu_results';

      $peer_ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$students_table} WHERE generation_id=%d", $generation_id));
      if (!$peer_ids) return [null,null,null];
      $in = implode(',', array_fill(0, count($peer_ids), '%d'));

      $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT score FROM {$results_table}
         WHERE (LOWER(modul_type)='literatie' OR LOWER(modul_type)='lit')
           AND student_id IN ($in)", ...$peer_ids
      ));
      $sum = 0.0; $cnt = 0; $max = null;
      foreach ($rows as $r) {
        $arr = @maybe_unserialize($r->score);
        if (!is_array($arr) || empty($arr['breakdown'])) continue;
        foreach ($arr['breakdown'] as $it) {
          if (!is_array($it) || ($it['name']??'') !== $key) continue;
          if (isset($it['max']) && is_numeric($it['max'])) $max = (float)$it['max'];
          if (isset($it['value']) && is_numeric($it['value'])) { $sum += (float)$it['value']; $cnt++; }
        }
      }
      if ($cnt === 0) return [null,$max,null];
      $avg = $sum / $cnt;
      $pct = ($max && $max>0) ? ($avg/$max)*100.0 : null;
      return [$avg,$max,$pct];
    };

    $numDelta = function($a, $b) { return (is_numeric($a) && is_numeric($b)) ? ((float)$b - (float)$a) : null; };

    $build = function($keys) use ($bd0,$bd1,$norm,$gen_avg_for,$numDelta) {
      $out = []; $t0_sum=0.0; $t1_sum=0.0; $gen_sum=0.0;
      $had_t0=false; $had_t1=false; $had_gen=false;

      foreach ($keys as $k) {
        [$v0,$m0,$p0,$lab] = isset($bd0[$k]) ? $norm($bd0[$k]) : [null,null,null,''];
        [$v1,$m1,$p1,/*lab1*/] = isset($bd1[$k]) ? $norm($bd1[$k]) : [null,null,null,$lab];

        [$gavg,$gmax,$gpct] = $gen_avg_for($k);

        $out[] = [
          'key'   => $k,
          'label' => $lab,
          't0'    => ['value'=>$v0,'max'=>$m0,'pct'=>$p0],
          't1'    => ['value'=>$v1,'max'=>$m1,'pct'=>$p1],
          'gen'   => ['avg'=>$gavg,'max'=>$gmax,'pct'=>$gpct],
          'delta' => [
            't1_t0'   => $numDelta($v0,$v1),
            'pct'     => $numDelta($p0,$p1),
            't0_gen'  => $numDelta($gavg,$v0)===null? null : ($v0 - $gavg),
            't1_gen'  => $numDelta($gavg,$v1)===null? null : ($v1 - $gavg),
          ],
        ];

        if (is_numeric($v0)) { $t0_sum += $v0; $had_t0=true; }
        if (is_numeric($v1)) { $t1_sum += $v1; $had_t1=true; }
        if (is_numeric($gavg)) { $gen_sum += $gavg; $had_gen=true; }
      }

      return [
        $out,
        $had_t0 ? $t0_sum : null,
        $had_t1 ? $t1_sum : null,
        $had_gen ? $gen_sum : null
      ];
    };

    [$core,$core_t0,$core_t1,$core_gen] = $build($core_keys);
    [$cond,$cond_t0,$cond_t1,$cond_gen] = $build($cond_keys);

    return [
      'core'   => $core,
      'cond'   => $cond,
      'totals' => [
        'core' => [
          't0_sum' => $core_t0,
          't1_sum' => $core_t1,
          'gen_sum'=> $core_gen,
          't1_t0_delta' => (is_numeric($core_t0) && is_numeric($core_t1)) ? ($core_t1 - $core_t0) : null
        ],
        'cond' => [
          't0_sum' => $cond_t0,
          't1_sum' => $cond_t1,
          'gen_sum'=> $cond_gen,
          't1_t0_delta' => (is_numeric($cond_t0) && is_numeric($cond_t1)) ? ($cond_t1 - $cond_t0) : null
        ],
      ],
    ];
  }
}

/* ====================================================================== *
 *  Variabile expuse pentru UI (raport-elev-tab-lit.php)
 * ====================================================================== *
 *  - $lit_scheme_key, $lit_scheme, $lit_class_value
 *  - $lit_status, $lit_completion
 *  - $lit_stage (total_points/total_pct/items/raw)
 *  - $lit_overall_student_pct, $lit_delta_total
 *  - $lit_gen_total_pct, $lit_gen_overall_pct, $lit_gen_delta_total, $lit_delta_student_vs_gen
 *  - $lit_gen_total_pct_remedial, $lit_gen_overall_pct_remedial, $lit_delta_overall_vs_gen_remedial
 *  - $lit_questions_map
 *  - $lit_focus
 *  - $lit_remedial (t0/t1)
 *  - $lit_dif_clasa
 *  - $lit_score_full
 *  - $lit_levels (acc/comp cu t0,t1 și avg_num)
 *  - $lit_levels_delta_avg, $lit_levels_delta_avg_color   <-- NOU
 * ====================================================================== */
