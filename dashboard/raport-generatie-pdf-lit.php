<?php
/** Raport Generație — LIT (PDF) — paritate cu raport-generatie-tab-lit.php
 *
 * Variabile așteptate (din helper-lit + wrapper):
 *  - $GEN, $students, $rowsRaw (stdClass[]), $GLOBALS['student_name']
 *  - $gen_lit_stage['t0'|'t1'] cu: students, completion_avg, status_counts, levels,
 *      items_core_raw, items, remedial_count, remedial_points_sum, remedial_max_sum,
 *      total_pct_avg_remedial
 *  - $gen_lit_total_pct_remedial, $gen_lit_overall_pct_remedial, $gen_lit_delta_total_remedial
 */

if (!defined('ABSPATH')) exit;

// ---------- utilitare PDF (fără dependență de Tailwind) ----------
if (!function_exists('rv')) {
  function rv($row, $key, $default = null) {
    if (is_array($row))  return array_key_exists($key, $row) ? $row[$key] : $default;
    if (is_object($row)) return isset($row->$key) ? $row->$key : $default;
    return $default;
  }
}
$fmt2  = function($v){ return is_numeric($v) ? number_format((float)$v, 2) : '—'; };
$fmtPct = function($v){ return is_numeric($v) ? number_format((float)$v, 2).'%' : '—'; };

// scheme de culori pentru badge-uri în PDF (clase CSS inline definite mai jos)
$pctBadgeClass = function($pct){
  if (!is_numeric($pct)) return 'b-na';
  $v = max(0, min(100, (float)$pct));
  if ($v >= 80) return 'b-green';
  if ($v >= 60) return 'b-lime';
  if ($v >= 40) return 'b-yellow';
  return 'b-red';
};
$pctBadge = function($pct) use ($pctBadgeClass, $fmtPct){
  $cls = $pctBadgeClass($pct);
  return '<span class="pill '.$cls.'">'.$fmtPct($pct).'</span>';
};
$deltaChip = function($d) use ($fmt2){
  if (!is_numeric($d)) return '<span class="pill b-na">—</span>';
  $cls = ($d > 0) ? 'b-green' : (($d < 0) ? 'b-red' : 'b-zero');
  $sign = ($d > 0 ? '+' : '');
  return '<span class="pill '.$cls.'">'.$sign.$fmt2($d).'</span>';
};
$levelBadge = function($val, $colorKey = null) use ($fmt2){
  if (!is_numeric($val)) return '—';
  $cls = 'b-zero';
  if ($colorKey === 'green')      $cls = 'b-green';
  elseif ($colorKey === 'red')    $cls = 'b-red';
  $sign = ($val > 0 ? '+' : '');
  return '<span class="pill pill-bold">'.$sign.$fmt2($val).'</span>';
};

// fallback dacă helper-ul nu a fost încărcat
$gen_lit_stage = isset($gen_lit_stage) && is_array($gen_lit_stage) ? $gen_lit_stage : [
  't0'=>['students'=>0,'completion_avg'=>null,'status_counts'=>['draft'=>0,'final'=>0],'levels'=>['acc'=>['delta_avg'=>null],'comp'=>['delta_avg'=>null]],'items'=>[],'items_core_raw'=>[],'remedial_count'=>0,'remedial_points_sum'=>null,'remedial_max_sum'=>null,'total_pct_avg_remedial'=>null],
  't1'=>['students'=>0,'completion_avg'=>null,'status_counts'=>['draft'=>0,'final'=>0],'levels'=>['acc'=>['delta_avg'=>null],'comp'=>['delta_avg'=>null]],'items'=>[],'items_core_raw'=>[],'remedial_count'=>0,'remedial_points_sum'=>null,'remedial_max_sum'=>null,'total_pct_avg_remedial'=>null],
];
$gen_lit_overall_pct_remedial = $gen_lit_overall_pct_remedial ?? null;
$gen_lit_delta_total_remedial = $gen_lit_delta_total_remedial ?? null;

$total_students = (int)($GEN['total_students'] ?? (is_iterable($students ?? null) ? count($students) : 0));

// ---------- calcule pentru cardul Δ T1−T0 & Medii (ca în tab) ----------
$acc0  = $gen_lit_stage['t0']['levels']['acc']['delta_avg']  ?? null;
$comp0 = $gen_lit_stage['t0']['levels']['comp']['delta_avg'] ?? null;
$acc1  = $gen_lit_stage['t1']['levels']['acc']['delta_avg']  ?? null;
$comp1 = $gen_lit_stage['t1']['levels']['comp']['delta_avg'] ?? null;
$accColor0  = ($acc0  === null ? null : ($acc0  < 0 ? 'red' : 'green'));
$compColor0 = ($comp0 === null ? null : ($comp0 < 0 ? 'red' : 'green'));
$accColor1  = ($acc1  === null ? null : ($acc1  < 0 ? 'red' : 'green'));
$compColor1 = ($comp1 === null ? null : ($comp1 < 0 ? 'red' : 'green'));
$dAcc  = ($acc0  !== null && $acc1  !== null) ? ((float)$acc1  - (float)$acc0)  : null;
$dComp = ($comp0 !== null && $comp1 !== null) ? ((float)$comp1 - (float)$comp0) : null;
$pillacc   = ($dAcc  === null ? null : ($dAcc  < 0 ? 'red' : 'green'));
$pillcompp = ($dComp === null ? null : ($dComp < 0 ? 'red' : 'green'));

// completare % per etapă (raportat la total elevi din generație)
$compl_t0_pct = ($total_students > 0) ? (intval($gen_lit_stage['t0']['students']) / $total_students * 100) : null;
$compl_t1_pct = ($total_students > 0) ? (intval($gen_lit_stage['t1']['students']) / $total_students * 100) : null;
$dComplNew    = ($compl_t0_pct !== null && $compl_t1_pct !== null) ? ($compl_t1_pct - $compl_t0_pct) : null;
$avgComplNew  = ($compl_t0_pct !== null && $compl_t1_pct !== null) ? (($compl_t0_pct + $compl_t1_pct) / 2) : ($compl_t0_pct ?? $compl_t1_pct);

// ---------- bloc școală ----------
$school = $GEN['school'] ?? [];
$school_lines = [];
if (!empty($school)) {
  $school_lines[] = $school['name'] ?? '';
  $school_lines[] = trim(implode(', ', array_filter([$school['city_name'] ?? '', $school['county'] ?? ''])));
  $meta = [];
  foreach (['regiune_tfr'=>'Regiune TFR','statut'=>'Statut','medie_irse'=>'Medie IRSE','scor_irse'=>'Scor IRSE','mediu'=>'Mediu','location'=>'Adresă','superior_location'=>'Inspectorat'] as $k=>$label) {
    if (!empty($school[$k])) $meta[] = "<strong>{$label}:</strong> ".esc_html((string)$school[$k]);
  }
  if ($meta) $school_lines[] = implode(' • ', $meta);
}
$school_block = $school_lines ? implode('<br>', array_map('wp_kses_post', $school_lines)) : '—';

// ---------- tabel elevi (replicat din tab) ----------
if (!function_exists('rg_maybe_unserialize')) {
  function rg_maybe_unserialize($v){
    if (is_array($v) || is_object($v)) return $v;
    if (!is_string($v)) return $v;
    $v2 = trim($v); if ($v2==='') return $v;
    if (preg_match('/^[adObis]:/', $v2) || $v2==='N;') { $u=@unserialize($v2); if ($u!==false || $v2==='b:0;') return $u; }
    return $v;
  }
}

$rowsRaw = isset($rowsRaw) ? (array)$rowsRaw : [];
$rowsLIT = array_values(array_filter($rowsRaw, function($r){
  $mt = strtolower(trim($r->modul_type ?? ''));
  $m  = strtolower(trim($r->modul ?? ''));
  return ($mt==='literatie' || $mt==='lit' || strpos($m,'literatie-')===0 || strpos($m,'lit-')===0);
}));

$latest_lit = ['t0'=>[],'t1'=>[]];
$ids_set = [];
foreach ($rowsLIT as $r) {
  $sid = intval($r->student_id ?? 0); if (!$sid) continue;
  $m = strtolower(trim($r->modul ?? ''));
  $st = (strpos($m,'-t0')!==false) ? 't0' : ((strpos($m,'-t1')!==false) ? 't1' : null);
  if (!$st) continue;
  $k = (!empty($r->updated_at) ? strtotime($r->updated_at)
       : (!empty($r->created_at) ? strtotime($r->created_at)
       : intval($r->id ?? 0)));
  if (!isset($latest_lit[$st][$sid]) || $k > $latest_lit[$st][$sid]['key']) {
    $latest_lit[$st][$sid] = ['key'=>$k, 'row'=>$r];
  }
  $ids_set[$sid] = true;
}
$student_ids = array_keys($ids_set);

// nume + clasă din DB
global $wpdb;
$student_map = [];
if (!empty($student_ids)) {
  $tbl_students = $wpdb->prefix.'edu_students';
  $in = implode(',', array_fill(0, count($student_ids), '%d'));
  $srows = $wpdb->get_results($wpdb->prepare(
    "SELECT id, first_name, last_name, class_label FROM {$tbl_students} WHERE id IN ($in)",
    ...$student_ids
  ));
  foreach ($srows as $s) {
    $nm = trim(($s->first_name ?? '').' '.($s->last_name ?? ''));
    if ($nm === '') $nm = 'Elev #'.intval($s->id);
    $student_map[intval($s->id)] = ['name'=>$nm, 'class_label'=>(string)($s->class_label ?? '—')];
  }
}

$rows_students = [];
foreach ($student_ids as $sid) {
  $t0 = $latest_lit['t0'][$sid]['row'] ?? null;
  $t1 = $latest_lit['t1'][$sid]['row'] ?? null;

  $acc0r = $comp0r = $acc1r = $comp1r = null;

  if ($t0 && !empty($t0->score)) {
    $arr = rg_maybe_unserialize($t0->score);
    if (is_array($arr) && !empty($arr['breakdown'])) {
      foreach ($arr['breakdown'] as $it) {
        if (($it['name'] ?? '')==='lit_q2') $acc0r  = $it['value'] ?? null;
        if (($it['name'] ?? '')==='lit_q4') $comp0r = $it['value'] ?? null;
      }
    }
  }
  if ($t1 && !empty($t1->score)) {
    $arr = rg_maybe_unserialize($t1->score);
    if (is_array($arr) && !empty($arr['breakdown'])) {
      foreach ($arr['breakdown'] as $it) {
        if (($it['name'] ?? '')==='lit_q2') $acc1r  = $it['value'] ?? null;
        if (($it['name'] ?? '')==='lit_q4') $comp1r = $it['value'] ?? null;
      }
    }
  }

  $n_acc0  = function_exists('edus_level_string_to_num') ? edus_level_string_to_num($acc0r)  : null;
  $n_comp0 = function_exists('edus_level_string_to_num') ? edus_level_string_to_num($comp0r) : null;
  $n_acc1  = function_exists('edus_level_string_to_num') ? edus_level_string_to_num($acc1r)  : null;
  $n_comp1 = function_exists('edus_level_string_to_num') ? edus_level_string_to_num($comp1r) : null;

  $cl = $student_map[$sid]['class_label'] ?? '';
  $grade_num = function_exists('edus_grade_number_from_classlabel') ? edus_grade_number_from_classlabel($cl) : 0;
  $class_value = max(0, min(8, ($grade_num >= 0 ? $grade_num : 0)));

  $d_acc0  = ($n_acc0  !== null) ? ($n_acc0  - $class_value) : null;
  $d_comp0 = ($n_comp0 !== null) ? ($n_comp0 - $class_value) : null;
  $d_acc1  = ($n_acc1  !== null) ? ($n_acc1  - $class_value) : null;
  $d_comp1 = ($n_comp1 !== null) ? ($n_comp1 - $class_value) : null;

  $rows_students[] = [
    'id'   => $sid,
    'name' => $student_map[$sid]['name'] ?? ('Elev #'.$sid),
    'class_label' => $student_map[$sid]['class_label'] ?? '—',
    'acc0' => $d_acc0,  'comp0' => $d_comp0,
    'acc1' => $d_acc1,  'comp1' => $d_comp1,
    'd_acc'  => ($d_acc0!==null  && $d_acc1!==null)  ? ($d_acc1  - $d_acc0)  : null,
    'd_comp' => ($d_comp0!==null && $d_comp1!==null) ? ($d_comp1 - $d_comp0) : null,
  ];
}
usort($rows_students, function($a,$b){ return strcasecmp($a['name'],$b['name']); });

$diffCell = function($v) use ($fmt2) {
  if (!is_numeric($v)) return '<span class="muted">—</span>';
  $cls = ($v < 0) ? 'b-red' : 'b-green';
  $sign = ($v > 0 ? '+' : '');
  return '<span class="pill '.$cls.'">'.$sign.(string)$v.'</span>';
};

// itemi condiționali — afișăm secțiunea doar dacă există măcar un n>0
$cond = ['lit_q7','lit_q8','lit_q9','lit_q10','lit_q11','lit_q12'];
$has_cond = false;
foreach ($cond as $k) {
  $n0 = intval($gen_lit_stage['t0']['items'][$k]['n'] ?? 0);
  $n1 = intval($gen_lit_stage['t1']['items'][$k]['n'] ?? 0);
  if ($n0 > 0 || $n1 > 0) { $has_cond = true; break; }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Raport Generație — LIT</title>
<style>
  *{box-sizing:border-box}
  html,body{font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size:12px; line-height:1.35}
  .page{padding:22px}
  h1{font-size:20px; margin:0 0 8px}
  h2{font-size:15px; margin:16px 0 8px}
  h3{font-size:13px; margin:12px 0 6px}
  .muted{color:#666}
  .chip{display:inline-block; border:1px solid #d1d5db; border-radius:999px; padding:2px 8px; font-size:11px; color:#444}

  .kv{border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:14px}
  .kv table{width:100%; border-collapse:separate; border-spacing:0}
  .kv td{padding:5px 6px; vertical-align:top}
  .kv .k{width:32%; color:#555}
  .kv .v{width:68%}

  /* carduri KPI: 3 coloane lățime egală via tabel (dompdf nu suportă flex/grid bine) */
  table.cards{width:100%; border-collapse:separate; border-spacing:8px 0; margin:6px 0 12px}
  table.cards > tbody > tr > td{
    width:33.33%; vertical-align:top;
    border:1px solid #e5e7eb; border-radius:10px; padding:10px 12px;
  }
  .card-h{display:block; font-weight:700; font-size:13px; color:#0f172a; margin-bottom:6px}
  .card-sub{display:block; font-size:11px; color:#475569; margin-bottom:8px}
  table.kpi-rows{width:100%; border-collapse:collapse; font-size:11.5px}
  table.kpi-rows td{padding:3px 0}
  table.kpi-rows td.lbl{color:#64748b; width:55%}
  table.kpi-rows td.val{text-align:right; font-weight:600}

  /* tabele */
  table.t{width:100%; border-collapse:collapse; margin-top:6px}
  .t th,.t td{border:1px solid #e5e7eb; padding:6px 8px; text-align:left; vertical-align:middle}
  .t th{background:#f8fafc; font-weight:600}
  .text-center{text-align:center}
  .small{font-size:10.5px}
  .mg-b{margin-bottom:18px}

  /* pill-uri */
  .pill{display:inline-block; padding:2px 7px; border-radius:8px; border:1px solid rgba(0,0,0,.05); font-size:11px; font-weight:600}
  .pill-bold{font-size:12px}
  .b-na    {background:#e5e7eb; color:#374151}
  .b-zero  {background:#f3f4f6; color:#374151}
  .b-red   {background:#fee2e2; color:#991b1b}
  .b-yellow{background:#fef3c7; color:#92400e}
  .b-lime  {background:#dcfce7; color:#166534}
  .b-green {background:#bbf7d0; color:#065f46}

  /* student link culoare */
  .stud-link{color:#0ea5e9; text-decoration:none; font-weight:600}
</style>
</head>
<body>
<div class="page">

  <h1>Raport LIT Generația <b>"<?= esc_html($GEN['name'] ?? '—') ?>"</b> <small>#<?= (int)$GEN['id'] ?></small></h1>

  <!-- (1) DETALII GENERAȚIE -->
  <div class="kv">
    <table>
      <tr><td class="k"><strong>An</strong></td><td class="v"><?= esc_html((string)($GEN['year'] ?? '—')) ?></td></tr>
      <tr><td class="k"><strong>Nivel</strong></td><td class="v"><span class="chip"><?= esc_html($GEN['level'] ?? '—') ?></span></td></tr>
      <tr><td class="k"><strong>Profesor</strong></td><td class="v"><?= esc_html($GEN['professor_name'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>Tutori</strong></td><td class="v"><?= esc_html($GEN['tutors_names'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>Nr. elevi</strong></td><td class="v"><?= (int)$total_students ?></td></tr>
      <tr><td class="k"><strong>Școala</strong></td><td class="v"><?= $school_block ?></td></tr>
    </table>
  </div>

  <!-- (2) KPIs — 3 carduri (T0, T1, Δ T1−T0 & Medii) -->
  <h2>Rezumat pe etape</h2>
  <table class="cards">
    <tr>
      <!-- T0 -->
      <td>
        <span class="card-h">T0</span>
        <span class="card-sub">
          draft <strong><?= (int)($gen_lit_stage['t0']['status_counts']['draft'] ?? 0) ?></strong> /
          final <strong><?= (int)($gen_lit_stage['t0']['status_counts']['final'] ?? 0) ?></strong>
        </span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Elevi cu rezultate</td>
            <td class="val"><?= (int)($gen_lit_stage['t0']['students'] ?? 0) ?> / <?= (int)$total_students ?></td>
          </tr>
          <tr>
            <td class="lbl">Grad completare</td>
            <td class="val">
              <?php
                $t0s = (int)($gen_lit_stage['t0']['students'] ?? 0);
                echo $total_students > 0 ? number_format(($t0s / $total_students) * 100, 2).'%' : '—';
              ?>
            </td>
          </tr>
          <tr>
            <td class="lbl">Nivel Acuratețe</td>
            <td class="val"><?= $levelBadge($acc0, $accColor0) ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Comprehensiune</td>
            <td class="val"><?= $levelBadge($comp0, $compColor0) ?></td>
          </tr>
          <tr>
            <td class="lbl">Elevi remedial</td>
            <td class="val">
              <?php
                $t0_rem  = (int)($gen_lit_stage['t0']['remedial_count'] ?? 0);
                $t0_eval = (int)($gen_lit_stage['t0']['students'] ?? 0);
                echo $t0_rem.' / '.$t0_eval;
                if ($t0_eval > 0) echo ' · '.number_format(($t0_rem / $t0_eval) * 100, 2).'%';
              ?>
            </td>
          </tr>
          <tr>
            <td class="lbl">Remedial (puncte)</td>
            <td class="val">
              <?php
                $rp   = $gen_lit_stage['t0']['remedial_points_sum'] ?? null;
                $rm   = $gen_lit_stage['t0']['remedial_max_sum']    ?? null;
                $ravg = $gen_lit_stage['t0']['total_pct_avg_remedial'] ?? null;
                if ($rp !== null && $rm !== null) echo intval(round($rp)).' / '.intval(round($rm)).' · ';
                echo $pctBadge($ravg);
              ?>
            </td>
          </tr>
        </table>
      </td>

      <!-- T1 -->
      <td>
        <span class="card-h">T1</span>
        <span class="card-sub">
          draft <strong><?= (int)($gen_lit_stage['t1']['status_counts']['draft'] ?? 0) ?></strong> /
          final <strong><?= (int)($gen_lit_stage['t1']['status_counts']['final'] ?? 0) ?></strong>
        </span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Elevi cu rezultate</td>
            <td class="val"><?= (int)($gen_lit_stage['t1']['students'] ?? 0) ?> / <?= (int)$total_students ?></td>
          </tr>
          <tr>
            <td class="lbl">Grad completare</td>
            <td class="val">
              <?php
                $t1s = (int)($gen_lit_stage['t1']['students'] ?? 0);
                echo $total_students > 0 ? number_format(($t1s / $total_students) * 100, 2).'%' : '—';
              ?>
            </td>
          </tr>
          <tr>
            <td class="lbl">Nivel Acuratețe</td>
            <td class="val"><?= $levelBadge($acc1, $accColor1) ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Comprehensiune</td>
            <td class="val"><?= $levelBadge($comp1, $compColor1) ?></td>
          </tr>
          <tr>
            <td class="lbl">Elevi remedial</td>
            <td class="val">
              <?php
                $t1_rem  = (int)($gen_lit_stage['t1']['remedial_count'] ?? 0);
                $t1_eval = (int)($gen_lit_stage['t1']['students'] ?? 0);
                echo $t1_rem.' / '.$t1_eval;
                if ($t1_eval > 0) echo ' · '.number_format(($t1_rem / $t1_eval) * 100, 2).'%';
              ?>
            </td>
          </tr>
          <tr>
            <td class="lbl">Remedial (puncte)</td>
            <td class="val">
              <?php
                $rp1   = $gen_lit_stage['t1']['remedial_points_sum'] ?? null;
                $rm1   = $gen_lit_stage['t1']['remedial_max_sum']    ?? null;
                $r1avg = $gen_lit_stage['t1']['total_pct_avg_remedial'] ?? null;
                if ($rp1 !== null && $rm1 !== null) echo intval(round($rp1)).' / '.intval(round($rm1)).' · ';
                echo $pctBadge($r1avg);
              ?>
            </td>
          </tr>
        </table>
      </td>

      <!-- Δ T1 − T0 & Medii -->
      <td>
        <span class="card-h">Δ T1 − T0 &amp; Medii</span>
        <span class="card-sub">Diferențe între etape</span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Medie Completare</td>
            <td class="val">
              <?= $deltaChip($dComplNew) ?>
              &nbsp;<?= $avgComplNew !== null ? number_format($avgComplNew, 2).'%' : '—' ?>
            </td>
          </tr>
          <tr>
            <td class="lbl">Nivel Acuratețe</td>
            <td class="val"><?= $levelBadge($dAcc, $pillacc) ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Comprehensiune</td>
            <td class="val"><?= $levelBadge($dComp, $pillcompp) ?></td>
          </tr>
          <tr>
            <td class="lbl">Remedial (medie %)</td>
            <td class="val">
              <?= $deltaChip($gen_lit_delta_total_remedial) ?>
              &nbsp;<?= $pctBadge($gen_lit_overall_pct_remedial) ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- (3) REZULTATE PE ITEMI (medii raw) -->
  <h2>Rezultate pe itemi (medii)</h2>
  <table class="t small mg-b">
    <thead>
      <tr>
        <th>Întrebare</th>
        <th class="text-center">T0</th>
        <th class="text-center">T1</th>
        <th class="text-center">Δ T1−T0</th>
      </tr>
    </thead>
    <tbody>
      <?php
        if (!function_exists('gen_lit_level_num_to_label_pp2')) {
          function gen_lit_level_num_to_label_pp2($num){
            if ($num===null) return '—';
            $r = round($num);
            if ($r <= -2) return 'PP';
            if ($r === -1) return 'P';
            return (string)$r;
          }
        }
        $order = ['lit_q1','lit_q2','lit_q3','lit_q4','lit_q5','lit_q6'];
        foreach ($order as $k):
          $row0 = $gen_lit_stage['t0']['items_core_raw'][$k] ?? null;
          $row1 = $gen_lit_stage['t1']['items_core_raw'][$k] ?? null;
          $lab  = $row0['label'] ?? ($row1['label'] ?? strtoupper($k));
          $is_level = ($row0['is_level'] ?? $row1['is_level'] ?? false);

          if ($is_level) {
            $v0 = $row0['avg_num'] ?? null;
            $v1 = $row1['avg_num'] ?? null;
            $d  = (is_numeric($v0) && is_numeric($v1)) ? ($v1 - $v0) : null;
            $t0 = ($row0 && !empty($row0['avg_label'])) ? $row0['avg_label']
                  : (is_numeric($v0) ? gen_lit_level_num_to_label_pp2($v0) : '—');
            $t1 = ($row1 && !empty($row1['avg_label'])) ? $row1['avg_label']
                  : (is_numeric($v1) ? gen_lit_level_num_to_label_pp2($v1) : '—');
          } else {
            $v0 = $row0['avg_raw'] ?? null;
            $v1 = $row1['avg_raw'] ?? null;
            $d  = (is_numeric($v0) && is_numeric($v1)) ? ($v1 - $v0) : null;
            $t0 = is_numeric($v0) ? number_format($v0, 2) : '—';
            $t1 = is_numeric($v1) ? number_format($v1, 2) : '—';
          }
      ?>
      <tr>
        <td><strong><?= esc_html($lab) ?></strong></td>
        <td class="text-center"><?= esc_html((string)$t0) ?></td>
        <td class="text-center"><?= esc_html((string)$t1) ?></td>
        <td class="text-center"><?= $deltaChip($d) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p class="small muted mg-b">
    Pentru itemii pe scală (PP, P, 0…5) am folosit: PP=<strong>−2</strong>, P=<strong>−1</strong>. Diferența (Δ) este calculată numeric.
  </p>

  <?php if ($has_cond): ?>
  <!-- (4) REZULTATE EVALUARE EMERGENTĂ / REMEDIAL -->
  <h2>Rezultate evaluare emergentă / remedial</h2>
  <table class="t small mg-b">
    <thead>
      <tr>
        <th>Întrebare</th>
        <th class="text-center">T0 (medie %)</th>
        <th class="text-center">T1 (medie %)</th>
        <th class="text-center">Δ T1−T0</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cond as $k):
        $lab = $gen_lit_stage['t0']['items'][$k]['label'] ?? ($gen_lit_stage['t1']['items'][$k]['label'] ?? strtoupper($k));
        $p0  = $gen_lit_stage['t0']['items'][$k]['avg_pct'] ?? null;
        $p1  = $gen_lit_stage['t1']['items'][$k]['avg_pct'] ?? null;
        $d   = (is_numeric($p0) && is_numeric($p1)) ? ($p1 - $p0) : null;
      ?>
      <tr>
        <td><strong><?= esc_html($lab) ?></strong></td>
        <td class="text-center"><?= $pctBadge($p0) ?></td>
        <td class="text-center"><?= $pctBadge($p1) ?></td>
        <td class="text-center"><?= $deltaChip($d) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p class="small muted mg-b">
    Aceste întrebări apar doar pentru elevii în <strong>Remedial</strong> și sunt mediate la nivelul generației.
  </p>
  <?php endif; ?>

  <!-- (5) ELEVI — Nivel Acuratețe & Comprehensiune (T0, T1, Δ) -->
  <h2>Elevi — Nivel Acuratețe &amp; Comprehensiune (T0, T1, Δ)</h2>
  <table class="t small mg-b">
    <thead>
      <tr>
        <th rowspan="2">Nume și prenume elev</th>
        <th rowspan="2">Clasa elevului</th>
        <th class="text-center" colspan="2">T0</th>
        <th class="text-center" colspan="2">T1</th>
        <th class="text-center" colspan="2">Δ T1−T0</th>
      </tr>
      <tr>
        <th class="text-center">Acuratețe</th>
        <th class="text-center">Comprehensiune</th>
        <th class="text-center">Acuratețe</th>
        <th class="text-center">Comprehensiune</th>
        <th class="text-center">Acuratețe</th>
        <th class="text-center">Comprehensiune</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($rows_students)): ?>
        <?php foreach ($rows_students as $row): ?>
          <tr>
            <td><span class="stud-link"><?= esc_html($row['name']) ?></span></td>
            <td><?= esc_html($row['class_label']) ?></td>
            <td class="text-center"><?= $diffCell($row['acc0']) ?></td>
            <td class="text-center"><?= $diffCell($row['comp0']) ?></td>
            <td class="text-center"><?= $diffCell($row['acc1']) ?></td>
            <td class="text-center"><?= $diffCell($row['comp1']) ?></td>
            <td class="text-center"><?= $deltaChip($row['d_acc']) ?></td>
            <td class="text-center"><?= $deltaChip($row['d_comp']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="muted text-center">Nu există elevi cu rezultate LIT în această generație.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <p class="small muted">
    Valorile reprezintă diferența între nivelul evaluat (PP=−1, P=0, apoi 1…4) și nivelul clasei. Negativ = roșu, zero sau pozitiv = verde.
  </p>

</div>
</body>
</html>
