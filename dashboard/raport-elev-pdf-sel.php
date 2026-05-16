<?php
/** Raport Elev — SEL (PDF) — paritate cu raport-elev-tab-sel.php
 *
 * Variabile așteptate (din helper-elev-sel.php + wrapper-ul edu_render_student_pdf_html):
 *  - $student (stdClass cu first_name/last_name/age/gender/class_label/generation_id...)
 *  - $teacherName, $generationLabel, $generationYear, $generationLevel, $schoolDisplay
 *  - $SEL_CHAPTERS
 *  - $map_t0/$map_ti/$map_t1 (per capitol -> float|null)
 *  - $gen_t0_avg/$gen_ti_avg/$gen_t1_avg/$gen_all_avg
 *  - $delta_ti_t0, $delta_t1_ti, $delta_t1_t0
 *  - $sel_total_t0, $sel_total_ti, $sel_total_t1, $sel_total_overall
 *  - $completion, $status, $stud_cap_avg
 *  - $footer_stud_*, $footer_gen_*, $footer_delta_*, $footer_stud_avg_cap, $footer_gen_avg_cap
 *  - $delta_total_ti_t0, $delta_total_t1_ti, $delta_total_t1_t0
 */
if (!defined('ABSPATH')) exit;

// utilitare PDF (nu mai folosim Tailwind)
$fmt2 = function($v){ return is_numeric($v) ? number_format((float)$v, 2) : '—'; };
$scoreBadge = function($v) use ($fmt2){
  if (!is_numeric($v)) return '<span class="pill b-na">—</span>';
  $x = (float)$v;
  $cls = 'b-green';
  if ($x < 1.5)  $cls = 'b-red';
  elseif ($x < 2)    $cls = 'b-orange';
  elseif ($x < 2.5)  $cls = 'b-yellow';
  elseif ($x < 2.75) $cls = 'b-lime';
  elseif ($x < 3)    $cls = 'b-lime-strong';
  return '<span class="pill '.$cls.'">'.$fmt2($v).'</span>';
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

$studentName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));
if ($studentName === '') $studentName = 'Elev #'.(int)$student->id;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Raport Elev — SEL</title>
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

  /* carduri rezumat: tabel de 4 coloane */
  table.cards{width:100%; border-collapse:separate; border-spacing:6px 0; margin-bottom:10px}
  table.cards > tbody > tr > td{
    width:25%; vertical-align:top;
    border:1px solid #e5e7eb; border-radius:10px; padding:8px 10px;
  }
  .card-h{display:block; font-weight:700; font-size:12px; color:#0f172a; margin-bottom:4px}
  table.kpi-rows{width:100%; border-collapse:collapse; font-size:11px}
  table.kpi-rows td{padding:2px 0}
  table.kpi-rows td.lbl{color:#64748b; width:55%}
  table.kpi-rows td.val{text-align:right; font-weight:600}

  /* tabele de date */
  table.t{width:100%; border-collapse:collapse; margin-top:4px}
  .t th,.t td{border:1px solid #e5e7eb; padding:4px 6px; text-align:left; vertical-align:middle}
  .t th{background:#f8fafc; font-weight:600; font-size:10.5px}
  .t td{font-size:10.5px}
  .text-center{text-align:center}
  .small{font-size:10px}
  .mg-b{margin-bottom:12px}

  /* fundal-uri pentru grupul "Medie / capitol" */
  .t .col-mean{background:#f1f5f9}

  /* pill-uri */
  .pill{display:inline-block; padding:1px 6px; border-radius:7px; font-size:10.5px; font-weight:700; border:1px solid rgba(0,0,0,.05)}
  .b-na{background:#e5e7eb; color:#374151}
  .b-zero{background:#f3f4f6; color:#374151}
  .b-red{background:#dc2626; color:#fff; border-color:#b91c1c}
  .b-orange{background:#fb923c; color:#fff; border-color:#f97316}
  .b-yellow{background:#fde047; color:#1f2937; border-color:#facc15}
  .b-lime{background:#a3e635; color:#1f2937; border-color:#84cc16}
  .b-lime-strong{background:#65a30d; color:#fff; border-color:#4d7c0f}
  .b-green{background:#22c55e; color:#fff; border-color:#16a34a}
</style>
</head>
<body>
<div class="page">

  <h1>Raport SEL — <?= esc_html($studentName) ?></h1>

  <!-- Detalii elev / generație / școală -->
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

  <!-- Rezumat compact: 4 carduri (T0, Ti, T1, Media generală) -->
  <h2>Rezumat etape</h2>
  <table class="cards">
    <tr>
      <!-- T0 -->
      <td>
        <span class="card-h">T0 &nbsp; <?= $statusPill($status['t0'] ?? null) ?></span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Completare</td>
            <td class="val"><?= isset($completion['t0']) && $completion['t0']!==null ? intval($completion['t0']).'%' : '—' ?></td>
          </tr>
          <tr>
            <td class="lbl">Media etapă</td>
            <td class="val"><?= $scoreBadge($sel_total_t0) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ → Ti</td>
            <td class="val"><?= $deltaChip(($sel_total_ti!==null && $sel_total_t0!==null) ? $sel_total_ti - $sel_total_t0 : null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ → T1</td>
            <td class="val"><?= $deltaChip(($sel_total_t1!==null && $sel_total_t0!==null) ? $sel_total_t1 - $sel_total_t0 : null) ?></td>
          </tr>
        </table>
      </td>

      <!-- Ti -->
      <td>
        <span class="card-h">Ti &nbsp; <?= $statusPill($status['ti'] ?? null) ?></span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Completare</td>
            <td class="val"><?= isset($completion['ti']) && $completion['ti']!==null ? intval($completion['ti']).'%' : '—' ?></td>
          </tr>
          <tr>
            <td class="lbl">Media etapă</td>
            <td class="val"><?= $scoreBadge($sel_total_ti) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ vs T0</td>
            <td class="val"><?= $deltaChip(($sel_total_ti!==null && $sel_total_t0!==null) ? $sel_total_ti - $sel_total_t0 : null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ → T1</td>
            <td class="val"><?= $deltaChip(($sel_total_t1!==null && $sel_total_ti!==null) ? $sel_total_t1 - $sel_total_ti : null) ?></td>
          </tr>
        </table>
      </td>

      <!-- T1 -->
      <td>
        <span class="card-h">T1 &nbsp; <?= $statusPill($status['t1'] ?? null) ?></span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Completare</td>
            <td class="val"><?= isset($completion['t1']) && $completion['t1']!==null ? intval($completion['t1']).'%' : '—' ?></td>
          </tr>
          <tr>
            <td class="lbl">Media etapă</td>
            <td class="val"><?= $scoreBadge($sel_total_t1) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ vs Ti</td>
            <td class="val"><?= $deltaChip($delta_total_t1_ti ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ vs T0</td>
            <td class="val"><?= $deltaChip($delta_total_t1_t0 ?? null) ?></td>
          </tr>
        </table>
      </td>

      <!-- Media generală -->
      <td>
        <span class="card-h">Media generală</span>
        <table class="kpi-rows">
          <tr>
            <td class="lbl">Toate etapele</td>
            <td class="val"><?= $scoreBadge($sel_total_overall) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ Ti − T0</td>
            <td class="val"><?= $deltaChip($delta_total_ti_t0 ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ T1 − Ti</td>
            <td class="val"><?= $deltaChip($delta_total_t1_ti ?? null) ?></td>
          </tr>
          <tr>
            <td class="lbl">Δ T1 − T0</td>
            <td class="val"><?= $deltaChip($delta_total_t1_t0 ?? null) ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Tabel pe capitole -->
  <h2>Rezultate pe capitole</h2>
  <table class="t small mg-b">
    <thead>
      <tr>
        <th rowspan="2">Capitol</th>
        <th class="text-center" colspan="3">Etapa T0</th>
        <th class="text-center" colspan="3">Etapa Ti</th>
        <th class="text-center" colspan="3">Etapa T1</th>
        <th class="text-center" colspan="3">Δ între etape</th>
        <th class="text-center col-mean" colspan="2">Medie / capitol</th>
      </tr>
      <tr>
        <th class="text-center">Elev</th><th class="text-center">Δ</th><th class="text-center">Gen.</th>
        <th class="text-center">Elev</th><th class="text-center">Δ</th><th class="text-center">Gen.</th>
        <th class="text-center">Elev</th><th class="text-center">Δ</th><th class="text-center">Gen.</th>
        <th class="text-center">Ti−T0</th><th class="text-center">T1−Ti</th><th class="text-center">T1−T0</th>
        <th class="text-center col-mean">Elev</th><th class="text-center col-mean">Gen.</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($SEL_CHAPTERS as $cap):
        $v0s = $map_t0[$cap] ?? null;
        $vis = $map_ti[$cap] ?? null;
        $v1s = $map_t1[$cap] ?? null;
        $v0g = $gen_t0_avg[$cap] ?? null;
        $vig = $gen_ti_avg[$cap] ?? null;
        $v1g = $gen_t1_avg[$cap] ?? null;
        $d01  = $delta_ti_t0[$cap] ?? null;
        $di1  = $delta_t1_ti[$cap] ?? null;
        $d0_1 = $delta_t1_t0[$cap] ?? null;
        $stud_avg = isset($stud_cap_avg[$cap]) ? $stud_cap_avg[$cap] : null;
        $gen_avg  = $gen_all_avg[$cap] ?? null;
        $d_t0_sg = ($v0s !== null && $v0g !== null) ? $v0s - $v0g : null;
        $d_ti_sg = ($vis !== null && $vig !== null) ? $vis - $vig : null;
        $d_t1_sg = ($v1s !== null && $v1g !== null) ? $v1s - $v1g : null;
      ?>
      <tr>
        <td><strong><?= esc_html($cap) ?></strong></td>
        <td class="text-center"><?= $scoreBadge($v0s) ?></td>
        <td class="text-center"><?= $deltaChip($d_t0_sg) ?></td>
        <td class="text-center"><?= $scoreBadge($v0g) ?></td>

        <td class="text-center"><?= $scoreBadge($vis) ?></td>
        <td class="text-center"><?= $deltaChip($d_ti_sg) ?></td>
        <td class="text-center"><?= $scoreBadge($vig) ?></td>

        <td class="text-center"><?= $scoreBadge($v1s) ?></td>
        <td class="text-center"><?= $deltaChip($d_t1_sg) ?></td>
        <td class="text-center"><?= $scoreBadge($v1g) ?></td>

        <td class="text-center"><?= $deltaChip($d01) ?></td>
        <td class="text-center"><?= $deltaChip($di1) ?></td>
        <td class="text-center"><?= $deltaChip($d0_1) ?></td>

        <td class="text-center col-mean"><?= $scoreBadge($stud_avg) ?></td>
        <td class="text-center col-mean"><?= $scoreBadge($gen_avg) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td><strong>Medie</strong></td>
        <td class="text-center"><?= $scoreBadge($footer_stud_t0 ?? null) ?></td>
        <td class="text-center"><?= $deltaChip($footer_delta_t0 ?? null) ?></td>
        <td class="text-center"><?= $scoreBadge($footer_gen_t0 ?? null) ?></td>

        <td class="text-center"><?= $scoreBadge($footer_stud_ti ?? null) ?></td>
        <td class="text-center"><?= $deltaChip($footer_delta_ti ?? null) ?></td>
        <td class="text-center"><?= $scoreBadge($footer_gen_ti ?? null) ?></td>

        <td class="text-center"><?= $scoreBadge($footer_stud_t1 ?? null) ?></td>
        <td class="text-center"><?= $deltaChip($footer_delta_t1 ?? null) ?></td>
        <td class="text-center"><?= $scoreBadge($footer_gen_t1 ?? null) ?></td>

        <td class="text-center"><?= $deltaChip($delta_total_ti_t0 ?? null) ?></td>
        <td class="text-center"><?= $deltaChip($delta_total_t1_ti ?? null) ?></td>
        <td class="text-center"><?= $deltaChip($delta_total_t1_t0 ?? null) ?></td>

        <td class="text-center col-mean"><?= $scoreBadge($footer_stud_avg_cap ?? null) ?></td>
        <td class="text-center col-mean"><?= $scoreBadge($footer_gen_avg_cap ?? null) ?></td>
      </tr>
    </tfoot>
  </table>
  <p class="small muted">
    Mediile generației/clasei sunt calculate dinamic pe fiecare etapă (T0/Ti/T1) și pe ansamblu, pentru toți elevii din aceeași generație.
  </p>

</div>
</body>
</html>
