<?php
/** Raport Generație — SEL (PDF) — v2
 * Presupune (din raport-generatie-helper-sel.php):
 *  - $GEN, $students (stdClass[]), $student_name (id=>nume)
 *  - $SEL_CHAPTERS  (LISTĂ DE ETICHETE în ordinea afișării, exact ca în tab)
 *  - $sel_t0_avg_chapters, $sel_ti_avg_chapters, $sel_t1_avg_chapters  (map [ETICHETĂ]=>float|null)
 *  - $sel_stage_overall_avg ['t0'=>float|null,'ti'=>...,'t1'=>...]
 *  - $sel_overall_allStages_avg (float|null)
 *  - $sel_gen_chapter_avg_allStages (map [ETICHETĂ]=>float|null)
 *  - $SEL_perStage[sid]['sel-t0'|'sel-ti'|'sel-t1']['row'] (row cu 'results' JSON per elev)
 *  - opțional: $sel_completion_allStages (0..100)
 */

// ============ utilitare tolerante ============
if (!function_exists('rv')) {
  function rv($row, $key, $default = null) {
    if (is_array($row))  return array_key_exists($key, $row) ? $row[$key] : $default;
    if (is_object($row)) return isset($row->$key) ? $row->$key : $default;
    return $default;
  }
}
if (!function_exists('edus_avg3_non_null')) {
  function edus_avg3_non_null($a,$b,$c){
    $vals=[]; if($a!==null && $a!=='') $vals[]=(float)$a;
    if($b!==null && $b!=='') $vals[]=(float)$b;
    if($c!==null && $c!=='') $vals[]=(float)$c;
    return count($vals) ? array_sum($vals)/count($vals) : null;
  }
}
// Parser fallback: mapează rezultatele la ETICHETELE capitolelor (nu la sluguri)
if (!function_exists('edus_sel_parse_chapter_map')) {
  function edus_sel_parse_chapter_map($row, $chapterLabels){
    $map = [];
    $json = rv($row,'results', null);
    $data = null;
    if (is_string($json) && $json !== '') {
      $tmp = json_decode($json, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) $data = $tmp;
    }
    foreach ((array)$chapterLabels as $label) {
      $labelKey = is_string($label) ? $label : (string)$label;
      $v = null;
      if (is_array($data)) {
        // acceptăm fie direct cheie cu numele etichetei, fie structuri cu ['avg']
        if (array_key_exists($labelKey, $data)) {
          $raw = $data[$labelKey];
          $v = is_array($raw) && array_key_exists('avg',$raw) ? $raw['avg'] : $raw;
        } else {
          // fallback: încearcă cheie “curățată” (fără diacritice/spații) dacă ai folosit alt format în JSON
          $norm = preg_replace('/\s+/', ' ', trim(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$labelKey)));
          foreach ($data as $k=>$val) {
            $kn = preg_replace('/\s+/', ' ', trim(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',(string)$k)));
            if ($kn === $norm) {
              $v = is_array($val) && array_key_exists('avg',$val) ? $val['avg'] : $val; break;
            }
          }
        }
      }
      $map[$labelKey] = is_numeric($v) ? (float)$v : null;
    }
    return $map;
  }
}
if (!function_exists('score_class')) {
  function score_class($v){
    if ($v===null || $v==='') return 'score-na';
    $x = (float)$v;
    if ($x < 1.5)  return 'score-red';
    if ($x < 2.0)  return 'score-orange';
    if ($x < 2.5)  return 'score-yellow';
    if ($x < 2.75) return 'score-lime';
    if ($x < 3.0)  return 'score-lime-strong';
    return 'score-green';
  }
}
if (!function_exists('delta_class')) {
  function delta_class($d){
    if ($d===null || $d==='') return 'delta-na';
    $x=(float)$d;
    if ($x > 0) return 'delta-pos';
    if ($x < 0) return 'delta-neg';
    return 'delta-zero';
  }
}
if (!function_exists('fmt2')) {
  function fmt2($v){ return is_numeric($v) ? number_format((float)$v, 2) : '—'; }
}
if (!function_exists('fmt_delta')) {
  function fmt_delta($d){ return ($d===null || $d==='') ? '—' : ((float)$d>0?'+':'').number_format((float)$d,2); }
}
$fmtDate = function($ts) {
  if (!$ts) return '—';
  $t = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
  if (!$t) return '—';
  return date_i18n('d.m.Y', $t);
};

// ============ bloc școală ============
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

// KPI rezumat
$completion_pct = isset($sel_completion_allStages) && is_numeric($sel_completion_allStages) ? round((float)$sel_completion_allStages) : null;
$overall_mean   = isset($sel_overall_allStages_avg) && is_numeric($sel_overall_allStages_avg) ? (float)$sel_overall_allStages_avg : null;

// ============ CSS (fără flex) ============
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Raport Generație — SEL</title>
<style>
  *{box-sizing:border-box}
  html,body{font-family: DejaVu Sans, Arial, sans-serif; color:#111; font-size:12px; line-height:1.35}
  .page{padding:22px}

  h1{font-size:20px; margin:0 0 8px}
  h2{font-size:15px; margin:16px 0 8px}
  .muted{color:#666}
  .chip{display:inline-block; border:1px solid #d1d5db; border-radius:999px; padding:2px 8px; font-size:11px; color:#444}

  /* Header generație */
  .kv{border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:14px}
  .kv table{width:100%; border-collapse:separate; border-spacing:0}
  .kv td{padding:5px 6px; vertical-align:top}
  .kv .k{width:32%; color:#555}
  .kv .v{width:68%}

  .cards { font-size: 0; margin: 0 -8px; }
  .cards .card {
    display: inline-block;
    vertical-align: top;
    width: 33.333%;
    padding: 0 8px 16px;
    box-sizing: border-box;
    font-size: 12px;
  }
  .cards .card > .label { font-size: 11px; color: #555; margin-bottom: 2px; display:block; }
  .cards .card > .val   { font-size: 18px; font-weight: 700; display:block; }
  .clearfix { display:block; clear:both; }

  /* look-ul cardului propriu-zis */
  .cards .card {
    /* wrapper-ul deja are padding; adăugăm în interior containerul vizual */
  }
  .cards .card::before {
    content:"";
    display:block;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding-top:0; /* doar pentru border; nu modifică layout-ul */
  }
  .cards .card > .label,
  .cards .card > .val {
    position: relative;
    z-index: 1;
    margin-left: 10px;
    margin-right: 10px;
    margin-top: 8px;
  }

  /* Culori scor */
  .score-na{background:#e5e7eb; color:#374151; border-color:#d1d5db}
  .score-red{background:#dc2626; color:#fff; border-color:#b91c1c}
  .score-orange{background:#fb923c; color:#fff; border-color:#f97316}
  .score-yellow{background:#fde047; color:#1f2937; border-color:#facc15}
  .score-lime{background:#a3e635; color:#1f2937; border-color:#84cc16}
  .score-lime-strong{background:#65a30d; color:#fff; border-color:#4d7c0f}
  .score-green{background:#22c55e; color:#fff; border-color:#16a34a}

  /* Badge delta */
  .badge{display:inline-block; padding:2px 6px; border-radius:8px; font-size:11px; margin-left:4px}
  .delta-pos{background:#dcfce7; color:#166534; border:1px solid #86efac}
  .delta-neg{background:#fee2e2; color:#991b1b; border:1px solid #fecaca}
  .delta-zero{background:#f3f4f6; color:#374151; border:1px solid #e5e7eb}
  .delta-na{background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe}

  /* Tabele */
  table.t{width:100%; border-collapse:collapse; margin-top:6px}
  .t th,.t td{border:1px solid #e5e7eb; padding:6px 8px; text-align:left; vertical-align:middle}
  .t th{background:#f8fafc; font-weight:600}
  .pill{display:inline-block; padding:3px 8px; border-radius:8px; border:1px solid rgba(0,0,0,.05); font-weight:700}
  .small{font-size:10px}
  .mg-b{margin-bottom:25px}
  .text-center {text-align:center}

  /* Elevi */
  .student{margin:12px 0 8px; font-size:14px; font-weight:600}
  .student a{color:#0ea5e9; text-decoration:none}
  .student a:hover{text-decoration:underline}
  .subtle{color:#0ea5e9; font-weight:500; font-size:12px; margin-left:4px}
</style>
</head>
<body>
<div class="page">

  <!-- (1) DETALII GENERAȚIE -->
  <h1>Raport SEL Generația <b>"<?= esc_html($GEN['name'] ?? '—') ?>"</b> <small>#<?= (int)$GEN['id'] ?></small></h1>
  <div class="kv">
    <table>
      <tr><td class="k"><strong>An</strong></td><td class="v"><?= esc_html((string)($GEN['year'] ?? '—')) ?></td></tr>
      <tr><td class="k"><strong>Nivel</strong></td><td class="v"><span class="chip"><?= esc_html($GEN['level'] ?? '—') ?></span></td></tr>
      <tr><td class="k"><strong>Profesor</strong></td><td class="v"><?= esc_html($GEN['professor_name'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>Tutori</strong></td><td class="v"><?= esc_html($GEN['tutors_names'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>Nr. elevi</strong></td><td class="v"><?= (int)($GEN['total_students'] ?? 0) ?></td></tr>
      <tr><td class="k"><strong>Școala</strong></td><td class="v"><?= $school_block ?></td></tr>
    </table>
  </div>

  <!-- (2) SEL — REZUMAT: carduri în grilă 3/linie -->
  <h2>Rezumat pe generație</h2>
  <div class="cards">
    <?php
      $cardItems = [];
      $cardItems[] = ['label'=>'Grad completare', 'value'=>($completion_pct!==null?$completion_pct.'%':'—'), 'cls'=>''];
      $cardItems[] = ['label'=>'Media generală (toate etapele)', 'value'=>fmt2($overall_mean), 'cls'=>''];
      if (!empty($SEL_CHAPTERS) && is_array($SEL_CHAPTERS)) {
        foreach ($SEL_CHAPTERS as $cap) {
          $v = $sel_gen_chapter_avg_allStages[$cap] ?? null;
          $cardItems[] = ['label'=>(string)$cap, 'value'=>fmt2($v), 'cls'=>score_class($v)];
        }
      }
      foreach ($cardItems as $it): ?>
        <div class="card <?= esc_attr($it['cls']) ?>">
          <div class="label"><?= esc_html($it['label']) ?></div>
          <div class="val"><?= $it['value'] ?></div>
        </div>
    <?php endforeach; ?>
    <div class="clearfix"></div>
  </div>

  <!-- (3) SEL — Medii pe capitole pe etape (TRANS­PUS: coloane=T0,Ti,T1,Medie) -->
  <h2>Medii pe capitole pe etape</h2>
  <table class="t small mg-b">
    <thead>
      <tr>
        <th>Etapă / Capitol</th>
        <th class="text-center">T0</th>
        <th class="text-center">Ti</th>
        <th class="text-center">T1</th>
        <th class="text-center">Medie</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($SEL_CHAPTERS)): ?>
        <?php foreach ($SEL_CHAPTERS as $cap): ?>
          <?php
            $v0 = $sel_t0_avg_chapters[$cap] ?? null;
            $vi = $sel_ti_avg_chapters[$cap] ?? null;
            $v1 = $sel_t1_avg_chapters[$cap] ?? null;
            $vg = $sel_gen_chapter_avg_allStages[$cap] ?? null;
          ?>
          <tr>
            <td><strong><?= esc_html((string)$cap) ?></strong></td>
            <td class="text-center"><span class="pill <?= esc_attr(score_class($v0)) ?>"><?= fmt2($v0) ?></span></td>
            <td class="text-center"><span class="pill <?= esc_attr(score_class($vi)) ?>"><?= fmt2($vi) ?></span></td>
            <td class="text-center"><span class="pill <?= esc_attr(score_class($v1)) ?>"><?= fmt2($v1) ?></span></td>
            <td class="text-center"><span class="pill <?= esc_attr(score_class($vg)) ?>"><?= fmt2($vg) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php
          $o0 = $sel_stage_overall_avg['t0'] ?? null;
          $oi = $sel_stage_overall_avg['ti'] ?? null;
          $o1 = $sel_stage_overall_avg['t1'] ?? null;
          $og = $sel_overall_allStages_avg ?? null;
        ?>
        <tr>
          <td><strong>Media generală</strong></td>
          <td class="text-center"><span class="pill"><?= fmt2($o0) ?></span></td>
          <td class="text-center"><span class="pill"><?= fmt2($oi) ?></span></td>
          <td class="text-center"><span class="pill"><?= fmt2($o1) ?></span></td>
          <td class="text-center"><span class="pill"><?= fmt2($og) ?></span></td>
        </tr>
      <?php else: ?>
        <tr><td colspan="5" class="muted">Nu există capitole pentru afișare.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- (4) SEL — Rezultate individuale (pe capitole) -->
  <h2>Rezultate individuale (pe capitole)</h2>
  <?php if (!empty($students)): ?>
    <?php foreach ($students as $s):
      $sid   = (int)$s->id;
      $name  = $student_name[$sid] ?? trim(($s->last_name ?? '').' '.($s->first_name ?? '')) ?: ('Elev #'.$sid);

      // Hărți scoruri pe ETICHETE pentru T0/Ti/T1 (folosind parserul fallback dacă e cazul)
      $labels = (array)$SEL_CHAPTERS;
      $t0 = !empty($SEL_perStage[$sid]['sel-t0']['row']) ? edus_sel_parse_chapter_map($SEL_perStage[$sid]['sel-t0']['row'], $labels) : array_fill_keys($labels, null);
      $ti = !empty($SEL_perStage[$sid]['sel-ti']['row']) ? edus_sel_parse_chapter_map($SEL_perStage[$sid]['sel-ti']['row'], $labels) : array_fill_keys($labels, null);
      $t1 = !empty($SEL_perStage[$sid]['sel-t1']['row']) ? edus_sel_parse_chapter_map($SEL_perStage[$sid]['sel-t1']['row'], $labels) : array_fill_keys($labels, null);
    ?>
      <div class="student">
        <a href="<?= esc_url(home_url('/panou/raport/elev/'.$sid)) ?>"><?= esc_html($name) ?>
        <span class="subtle">– vezi raportul elevului</span></a>
      </div>

      <table class="t small mg-b">
        <thead>
          <tr>
            <th>Capitol</th>
            <th class="text-center">T0</th>
            <th class="text-center">Ti</th>
            <th class="text-center">T1</th>
            <th class="text-center">Medie</th>
            <th class="text-center">Ti−T0</th>
            <th class="text-center">T1−Ti</th>
            <th class="text-center">T1−T0</th>
            <th class="text-center">Medie gen / capitol</th>
            <th class="text-center">elev vs gen</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($labels)): ?>
            <?php foreach ($labels as $cap):
              $a = $t0[$cap] ?? null;
              $b = $ti[$cap] ?? null;
              $c = $t1[$cap] ?? null;

              $stud_cap_avg = edus_avg3_non_null($a,$b,$c);
              $gen_cap_avg  = $sel_gen_chapter_avg_allStages[$cap] ?? null;

              $d_ti_t0 = ($b!==null && $a!==null) ? ($b - $a) : null;
              $d_t1_ti = ($c!==null && $b!==null) ? ($c - $b) : null;
              $d_t1_t0 = ($c!==null && $a!==null) ? ($c - $a) : null;
              $d_vs_gen = ($stud_cap_avg!==null && $gen_cap_avg!==null) ? ($stud_cap_avg - $gen_cap_avg) : null;
            ?>
              <tr>
                <td><strong><?= esc_html((string)$cap) ?></strong></td>
                <td class="text-center"><span class="pill <?= esc_attr(score_class($a)) ?>"><?= fmt2($a) ?></span></td>
                <td class="text-center"><span class="pill <?= esc_attr(score_class($b)) ?>"><?= fmt2($b) ?></span></td>
                <td class="text-center"><span class="pill <?= esc_attr(score_class($c)) ?>"><?= fmt2($c) ?></span></td>
                <td class="text-center"><?= fmt2($stud_cap_avg) ?></td>
                <td class="text-center"><span class="badge <?= esc_attr(delta_class($d_ti_t0)) ?>"><?= fmt_delta($d_ti_t0) ?></span></td>
                <td class="text-center"><span class="badge <?= esc_attr(delta_class($d_t1_ti)) ?>"><?= fmt_delta($d_t1_ti) ?></span></td>
                <td class="text-center"><span class="badge <?= esc_attr(delta_class($d_t1_t0)) ?>"><?= fmt_delta($d_t1_t0) ?></span></td>
                <td class="text-center"><?= fmt2($gen_cap_avg) ?></td>
                <td class="text-center"><span class="badge <?= esc_attr(delta_class($d_vs_gen)) ?>"><?= fmt_delta($d_vs_gen) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="10" class="muted">Nu există capitole pentru afișare.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="muted">Nu există elevi în această generație.</p>
  <?php endif; ?>

</div>
</body>
</html>
