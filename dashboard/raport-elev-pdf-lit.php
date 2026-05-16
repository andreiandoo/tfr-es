<?php
/** Raport Elev — LIT (PDF) — paritate cu raport-elev-tab-lit.php
 *
 * Variabile așteptate (din helper-elev-lit.php + wrapper):
 *  - $student, $teacherName, $generationLabel, $generationYear, $generationLevel, $schoolDisplay
 *  - $lit_scheme, $lit_scheme_key
 *  - $lit_status, $lit_completion
 *  - $lit_stage['t0'|'t1'] cu total_points/total_pct/raw/items
 *  - $lit_levels (acc/comp cu t0/t1)
 *  - $lit_dif_clasa['t0'|'t1'] cu acc/comp/acc_color/comp_color
 *  - $lit_remedial (t0/t1)
 *  - $lit_focus (t0/t1) cu lit_q1..lit_q6 (label/raw/pct)
 *  - $lit_score_full (t0/t1) - opțional, pt itemii condiționali
 *  - $lit_overall_student_pct, $lit_delta_total
 */
if (!defined('ABSPATH')) exit;

$fmt2 = function($v){ return is_numeric($v) ? number_format((float)$v, 2) : '—'; };
$pctBadge = function($p) use ($fmt2){
  if (!is_numeric($p)) return '<span class="pill b-na">—</span>';
  $v = max(0, min(100, (float)$p));
  $cls = 'b-green';
  if ($v < 10) $cls = 'b-red';
  elseif ($v < 25) $cls = 'b-orange';
  elseif ($v < 50) $cls = 'b-yellow';
  elseif ($v < 75) $cls = 'b-lime';
  return '<span class="pill '.$cls.'">'.number_format($v, 0).'%</span>';
};
$deltaChip = function($d) use ($fmt2){
  if (!is_numeric($d)) return '<span class="pill b-na">—</span>';
  $cls = ($d > 0) ? 'b-green' : (($d < 0) ? 'b-red' : 'b-zero');
  $sign = ($d > 0 ? '+' : '');
  return '<span class="pill '.$cls.'">'.$sign.$fmt2($d).'</span>';
};
$statusPill = function($s){
  $s = strtolower(trim((string)$s));
  if ($s === '') return '<span class="pill b-na">—</span>';
  if ($s === 'final') return '<span class="pill b-green">FINAL</span>';
  if ($s === 'draft') return '<span class="pill b-yellow">DRAFT</span>';
  return '<span class="pill b-zero">'.strtoupper($s).'</span>';
};
$levelCell = function($val, $colorKey){
  if ($val === null || $val === '') return '<span class="muted">—</span>';
  $cls = 'b-zero';
  if ($colorKey === 'green')      $cls = 'b-green';
  elseif ($colorKey === 'red')    $cls = 'b-red';
  elseif ($colorKey === 'red-strong') $cls = 'b-red-strong';
  return '<span class="pill '.$cls.'">'.esc_html((string)$val).'</span>';
};

// mapare strictă pentru calcule Δ pe niveluri (PP=-2, P=-1, 0..5)
if (!function_exists('elev_pdf_lit_level_strict')) {
  function elev_pdf_lit_level_strict($raw){
    if ($raw === null || $raw === '') return null;
    $u = strtoupper(trim((string)$raw));
    if ($u === 'PP') return -2;
    if ($u === 'P')  return -1;
    if (is_numeric($u)) return (int)$u;
    return null;
  }
}

$studentName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));
if ($studentName === '') $studentName = 'Elev #'.(int)$student->id;

// Δ niveluri (T1−T0) — pe scara strictă
$acc0_m = elev_pdf_lit_level_strict($lit_levels['acc']['t0'] ?? null);
$acc1_m = elev_pdf_lit_level_strict($lit_levels['acc']['t1'] ?? null);
$cmp0_m = elev_pdf_lit_level_strict($lit_levels['comp']['t0'] ?? null);
$cmp1_m = elev_pdf_lit_level_strict($lit_levels['comp']['t1'] ?? null);
$d_acc  = ($acc0_m !== null && $acc1_m !== null) ? ($acc1_m - $acc0_m) : null;
$d_cmp  = ($cmp0_m !== null && $cmp1_m !== null) ? ($cmp1_m - $cmp0_m) : null;

// medie remedial %
$rem_pcts = [];
if (!empty($lit_remedial['t0']) && isset($lit_stage['t0']['total_pct']) && $lit_stage['t0']['total_pct'] !== null) {
  $rem_pcts[] = $lit_stage['t0']['total_pct'];
}
if (!empty($lit_remedial['t1']) && isset($lit_stage['t1']['total_pct']) && $lit_stage['t1']['total_pct'] !== null) {
  $rem_pcts[] = $lit_stage['t1']['total_pct'];
}
$rem_pct_avg = !empty($rem_pcts) ? (array_sum($rem_pcts) / count($rem_pcts)) : null;

// medie completare
$comp_vals = [];
if (isset($lit_completion['t0']) && $lit_completion['t0'] !== null) $comp_vals[] = (int)$lit_completion['t0'];
if (isset($lit_completion['t1']) && $lit_completion['t1'] !== null) $comp_vals[] = (int)$lit_completion['t1'];
$completion_avg = !empty($comp_vals) ? (array_sum($comp_vals) / count($comp_vals)) : null;

// itemi condiționali — folosim helper-ul din helper-lit dacă există
$cond_rows = [];
$cond_totals = ['t0_sum'=>null,'t1_sum'=>null,'gen_sum'=>null,'t1_t0_delta'=>null];
$show_cond = false;
if (function_exists('edus_lit_split_items_for_tables')) {
  $litT0 = $lit_score_full['t0'] ?? [];
  $litT1 = $lit_score_full['t1'] ?? [];
  $gen_id_for_cond = (isset($student) && is_object($student) && isset($student->generation_id)) ? (int)$student->generation_id : 0;
  $split = edus_lit_split_items_for_tables($litT0, $litT1, $gen_id_for_cond);
  $cond_rows   = $split['cond']   ?? [];
  $cond_totals = $split['totals']['cond'] ?? $cond_totals;
}
$is_remedial_t0 = !empty($lit_remedial['t0']);
$is_remedial_t1 = !empty($lit_remedial['t1']);
$show_cond = $is_remedial_t0 || $is_remedial_t1 || !empty(array_filter($cond_rows, function($i){
  return (isset($i['t0']['value']) && $i['t0']['value']!==null) || (isset($i['t1']['value']) && $i['t1']['value']!==null);
}));

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Raport Elev — LIT</title>
<style>
  *{box-sizing:border-box}
  html,body{font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size:11.5px; line-height:1.35}
  .page{padding:18px}
  h1{font-size:18px; margin:0 0 6px}
  h2{font-size:14px; margin:14px 0 6px}
  .muted{color:#666}
  .chip{display:inline-block; border:1px solid #d1d5db; border-radius:999px; padding:2px 8px; font-size:11px; color:#444}

  .kv{border:1px solid #e5e7eb; border-radius:10px; padding:10px 12px; margin-bottom:12px}
  .kv table{width:100%; border-collapse:separate; border-spacing:0}
  .kv td{padding:3px 6px; vertical-align:top}
  .kv .k{width:18%; color:#555}
  .kv .v{width:32%}

  /* 3 carduri KPI */
  table.cards{width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:10px}
  table.cards > tbody > tr > td{
    width:33.33%; vertical-align:top;
    border:1px solid #e5e7eb; border-radius:10px; padding:8px 10px;
  }
  .card-h{display:block; font-weight:700; font-size:12px; color:#0f172a; margin-bottom:4px}
  table.kpi-rows{width:100%; border-collapse:collapse; font-size:11px}
  table.kpi-rows td{padding:2px 0}
  table.kpi-rows td.lbl{color:#64748b; width:55%}
  table.kpi-rows td.val{text-align:right; font-weight:600}

  table.t{width:100%; border-collapse:collapse; margin-top:4px}
  .t th,.t td{border:1px solid #e5e7eb; padding:4px 6px; text-align:left; vertical-align:middle; font-size:10.5px}
  .t th{background:#f8fafc; font-weight:600}
  .text-center{text-align:center}
  .small{font-size:10px}
  .mg-b{margin-bottom:12px}

  .pill{display:inline-block; padding:1px 6px; border-radius:7px; font-size:10.5px; font-weight:700; border:1px solid rgba(0,0,0,.05)}
  .b-na{background:#e5e7eb; color:#374151}
  .b-zero{background:#f3f4f6; color:#374151}
  .b-red{background:#fee2e2; color:#991b1b; border-color:#fecaca}
  .b-red-strong{background:#dc2626; color:#fff; border-color:#b91c1c}
  .b-orange{background:#fb923c; color:#fff; border-color:#f97316}
  .b-yellow{background:#fef3c7; color:#92400e; border-color:#fde68a}
  .b-lime{background:#dcfce7; color:#166534; border-color:#86efac}
  .b-green{background:#bbf7d0; color:#065f46; border-color:#86efac}

  .rem-tag{display:inline-block; padding:1px 6px; border-radius:6px; font-size:10px; background:#dc2626; color:#fff; margin-left:6px}
</style>
</head>
<body>
<div class="page">

  <h1>Raport LIT — <?= esc_html($studentName) ?></h1>

  <!-- Detalii elev -->
  <div class="kv">
    <table>
      <tr>
        <td class="k"><strong>Generație</strong></td>
        <td class="v"><?= esc_html((string)$generationLabel) ?></td>
        <td class="k"><strong>Profesor</strong></td>
        <td class="v"><?= esc_html((string)$teacherName) ?></td>
      </tr>
      <tr>
        <td class="k"><strong>An</strong></td>
        <td class="v"><?= esc_html((string)($generationYear ?? '—')) ?></td>
        <td class="k"><strong>Nivel</strong></td>
        <td class="v"><span class="chip"><?= esc_html((string)($generationLevel ?? '—')) ?></span></td>
      </tr>
      <tr>
        <td class="k"><strong>Clasă</strong></td>
        <td class="v"><?= esc_html((string)($student->class_label ?: '—')) ?></td>
        <td class="k"><strong>Vârstă</strong></td>
        <td class="v"><?= esc_html((string)($student->age ?: '—')) ?> ani</td>
      </tr>
      <tr>
        <td class="k"><strong>Sex</strong></td>
        <td class="v"><?= esc_html((string)($student->gender ?: '—')) ?></td>
        <td class="k"><strong>Școală</strong></td>
        <td class="v"><?= esc_html((string)$schoolDisplay) ?></td>
      </tr>
    </table>
  </div>

  <!-- 3 carduri KPI (T0, T1, Δ T1−T0) -->
  <h2>Rezumat pe etape</h2>
  <table class="cards">
    <tr>
      <!-- T0 -->
      <td>
        <span class="card-h">
          T0 <?= $is_remedial_t0 ? '<span class="rem-tag">Remedial</span>' : '' ?>
          &nbsp; <?= $statusPill($lit_status['t0'] ?? null) ?>
        </span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Completare</td>
            <td class="val"><?= isset($lit_completion['t0']) && $lit_completion['t0']!==null ? intval($lit_completion['t0']).'%' : '—' ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Acuratețe</td>
            <td class="val"><?= $levelCell($lit_levels['acc']['t0'] ?? null, $lit_dif_clasa['t0']['acc_color'] ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Comprehensiune</td>
            <td class="val"><?= $levelCell($lit_levels['comp']['t0'] ?? null, $lit_dif_clasa['t0']['comp_color'] ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Remedial (puncte)</td>
            <td class="val">
              <?php
                if ($is_remedial_t0) {
                  $tp = $lit_stage['t0']['total_points'] ?? null;
                  $tm = (int)($lit_scheme['total_max'] ?? 0);
                  if ($tp !== null) echo intval($tp).' / '.$tm.' &nbsp; ';
                  echo $pctBadge($lit_stage['t0']['total_pct'] ?? null);
                } else {
                  echo '—';
                }
              ?>
            </td>
          </tr>
        </table>
      </td>

      <!-- T1 -->
      <td>
        <span class="card-h">
          T1 <?= $is_remedial_t1 ? '<span class="rem-tag">Remedial</span>' : '' ?>
          &nbsp; <?= $statusPill($lit_status['t1'] ?? null) ?>
        </span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Completare</td>
            <td class="val"><?= isset($lit_completion['t1']) && $lit_completion['t1']!==null ? intval($lit_completion['t1']).'%' : '—' ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Acuratețe</td>
            <td class="val"><?= $levelCell($lit_levels['acc']['t1'] ?? null, $lit_dif_clasa['t1']['acc_color'] ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Nivel Comprehensiune</td>
            <td class="val"><?= $levelCell($lit_levels['comp']['t1'] ?? null, $lit_dif_clasa['t1']['comp_color'] ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Remedial (puncte)</td>
            <td class="val">
              <?php
                if ($is_remedial_t1) {
                  $tp = $lit_stage['t1']['total_points'] ?? null;
                  $tm = (int)($lit_scheme['total_max'] ?? 0);
                  if ($tp !== null) echo intval($tp).' / '.$tm.' &nbsp; ';
                  echo $pctBadge($lit_stage['t1']['total_pct'] ?? null);
                } else {
                  echo '—';
                }
              ?>
            </td>
          </tr>
        </table>
      </td>

      <!-- Δ T1 − T0 -->
      <td>
        <span class="card-h">Δ T1 − T0 &amp; Medii</span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Δ Nivel Acuratețe</td>
            <td class="val"><?= $deltaChip($d_acc) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ Nivel Comprehensiune</td>
            <td class="val"><?= $deltaChip($d_cmp) ?></td>
          </tr>
          <tr>
            <td class="lbl">Remedial % (medie)</td>
            <td class="val"><?= $pctBadge($rem_pct_avg) ?></td>
          </tr>
          <tr>
            <td class="lbl">Completare (medie)</td>
            <td class="val"><?= $completion_avg !== null ? intval(round($completion_avg)).'%' : '—' ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Rezultate pe itemi (q1..q6) -->
  <h2>Rezultate pe itemi</h2>
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
        $order = ['lit_q1','lit_q2','lit_q3','lit_q4','lit_q5','lit_q6'];
        foreach ($order as $k):
          $rowLabel = $lit_focus['t0'][$k]['label'] ?? ($lit_focus['t1'][$k]['label'] ?? $k);
          $s0_raw = $lit_focus['t0'][$k]['raw'] ?? null;
          $s1_raw = $lit_focus['t1'][$k]['raw'] ?? null;

          $dT = null;
          if (in_array($k, ['lit_q1','lit_q2','lit_q4','lit_q5'], true)) {
            $v0 = elev_pdf_lit_level_strict($s0_raw);
            $v1 = elev_pdf_lit_level_strict($s1_raw);
            $dT = ($v0 !== null && $v1 !== null) ? ($v1 - $v0) : null;
          } else {
            $dT = (is_numeric($s0_raw) && is_numeric($s1_raw)) ? ((float)$s1_raw - (float)$s0_raw) : null;
          }
      ?>
      <tr>
        <td><strong><?= esc_html($rowLabel) ?></strong></td>
        <td class="text-center"><?= ($s0_raw!==null && $s0_raw!=='') ? esc_html((string)$s0_raw) : '—' ?></td>
        <td class="text-center"><?= ($s1_raw!==null && $s1_raw!=='') ? esc_html((string)$s1_raw) : '—' ?></td>
        <td class="text-center"><?= $deltaChip($dT) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p class="small muted">
    Diferența (Δ) este calculată numeric; pentru <strong>Lista de cuvinte</strong>, <strong>Acuratețe citire</strong>,
    <strong>Comprehensiune citire</strong> și <strong>Comprehensiune audiere</strong> se folosește scara: PP=−2, P=−1, 0..5.
  </p>

  <?php if ($show_cond && !empty($cond_rows)): ?>
  <!-- Itemi condiționali (evaluare remedială) -->
  <h2>
    Rezultate evaluare remedială
    <?php if ($is_remedial_t0): ?><span class="pill b-yellow">T0: Remedial</span><?php endif; ?>
    <?php if ($is_remedial_t1): ?><span class="pill b-yellow">T1: Remedial</span><?php endif; ?>
  </h2>
  <table class="t small mg-b">
    <thead>
      <tr>
        <th>Întrebare</th>
        <th class="text-center">T0</th>
        <th class="text-center">T1</th>
        <th class="text-center">Δ T1−T0</th>
        <th class="text-center">Gen. (medie)</th>
        <th class="text-center">Δ T0−Gen</th>
        <th class="text-center">Δ T1−Gen</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cond_rows as $it):
        $label = $it['label'] ?? '';
        $v0 = $it['t0']['value'] ?? null; $p0 = $it['t0']['pct'] ?? null;
        $v1 = $it['t1']['value'] ?? null; $p1 = $it['t1']['pct'] ?? null;
        $gv = $it['gen']['avg'] ?? null;  $gp = $it['gen']['pct'] ?? null;
        $d10 = $it['delta']['t1_t0'] ?? null;
        $d0g = $it['delta']['t0_gen'] ?? null;
        $d1g = $it['delta']['t1_gen'] ?? null;
      ?>
      <tr>
        <td><strong><?= esc_html((string)$label) ?></strong></td>
        <td class="text-center">
          <?php
            if ($v0 === null) echo '—';
            else {
              echo esc_html((string)$v0);
              if ($p0 !== null) echo '<div class="small muted">'.intval(round($p0)).'%</div>';
            }
          ?>
        </td>
        <td class="text-center">
          <?php
            if ($v1 === null) echo '—';
            else {
              echo esc_html((string)$v1);
              if ($p1 !== null) echo '<div class="small muted">'.intval(round($p1)).'%</div>';
            }
          ?>
        </td>
        <td class="text-center"><?= $deltaChip($d10) ?></td>
        <td class="text-center">
          <?php
            if ($gv === null) echo '—';
            else {
              echo number_format((float)$gv, 2);
              if ($gp !== null) echo '<div class="small muted">'.intval(round($gp)).'%</div>';
            }
          ?>
        </td>
        <td class="text-center"><?= $deltaChip($d0g) ?></td>
        <td class="text-center"><?= $deltaChip($d1g) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td><strong>TOTAL itemi condiționali</strong></td>
        <td class="text-center"><?= $cond_totals['t0_sum'] !== null ? number_format((float)$cond_totals['t0_sum'], 2) : '—' ?></td>
        <td class="text-center"><?= $cond_totals['t1_sum'] !== null ? number_format((float)$cond_totals['t1_sum'], 2) : '—' ?></td>
        <td class="text-center"><?= $deltaChip($cond_totals['t1_t0_delta'] ?? null) ?></td>
        <td class="text-center"><?= $cond_totals['gen_sum'] !== null ? number_format((float)$cond_totals['gen_sum'], 2) : '—' ?></td>
        <?php
          $d0g_tot = (isset($cond_totals['t0_sum'], $cond_totals['gen_sum']) && $cond_totals['t0_sum']!==null && $cond_totals['gen_sum']!==null)
            ? ($cond_totals['t0_sum'] - $cond_totals['gen_sum']) : null;
          $d1g_tot = (isset($cond_totals['t1_sum'], $cond_totals['gen_sum']) && $cond_totals['t1_sum']!==null && $cond_totals['gen_sum']!==null)
            ? ($cond_totals['t1_sum'] - $cond_totals['gen_sum']) : null;
        ?>
        <td class="text-center"><?= $deltaChip($d0g_tot) ?></td>
        <td class="text-center"><?= $deltaChip($d1g_tot) ?></td>
      </tr>
    </tfoot>
  </table>
  <p class="small muted">
    Acești itemi apar când elevul este în <strong>Remedial</strong> (Acuratețe = PP/P) și sunt incluși în totalul recalculat pentru primar/gimnaziu.
  </p>
  <?php endif; ?>

</div>
</body>
</html>
