<?php
/** Raport Generație — LIT (PDF)
 * Scope:
 *  - $GEN, $students ca la SEL
 *  - $rows (array) pentru LIT (t0/t1) — deoarece în query-ul din functions.php filtrăm deja pe modul_type=$module
 *  - variabile specifice LIT din helper-lit.php, dacă există
 */

if (!function_exists('rv')) {
  function rv($row, $key, $default = null) {
    if (is_array($row))  return array_key_exists($key, $row) ? $row[$key] : $default;
    if (is_object($row)) return isset($row->$key) ? $row->$key : $default;
    return $default;
  }
}
$fmtDate = function($ts) {
  if (!$ts) return '—';
  $t = is_numeric($ts) ? (int)$ts : strtotime((string)$ts);
  if (!$t) return '—';
  return date_i18n('d.m.Y', $t);
};

// HEADER ȘCOALĂ
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

// ULTIMUL LIT PER ELEV (fallback)
$rank = ['lit-t0'=>0,'t0'=>0,'lit-t1'=>2,'t1'=>2];
$latest_by_student = [];
if (!empty($rows)) {
  foreach ($rows as $r) {
    $sid = (int)rv($r,'student_id',0);
    $st  = strtolower((string)rv($r,'stage', rv($r,'modul','')));
    $cr  = rv($r,'created_at', null);
    $rk  = $rank[$st] ?? -1;
    if (!$sid || $rk < 0) continue;
    $key = sprintf('%02d-%010d', $rk, $cr ? strtotime((string)$cr) : 0);
    if (!isset($latest_by_student[$sid]) || $key > $latest_by_student[$sid]['__k']) {
      $arr = is_array($r) ? $r : (array)$r;
      $arr['__k'] = $key;
      $latest_by_student[$sid] = $arr;
    }
  }
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
    .page{width:100%; padding:24px}
    h1{font-size:20px; margin:0 0 8px}
    h2{font-size:14px; margin:16px 0 8px; border-bottom:1px solid #ddd; padding-bottom:4px}
    .muted{color:#666}
    .kv{border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-bottom:8px}
    .kv table{width:100%}
    .kv td{padding:4px 6px; vertical-align:top}
    .k{width:36%; color:#555}
    .v{width:64%}
    .kpi{display:inline-block; border:1px solid #e5e7eb; border-radius:8px; padding:10px 12px; margin:4px 8px 4px 0}
    .kpi .label{font-size:11px; color:#555}
    .kpi .val{font-size:16px; font-weight:700}
    table.t{width:100%; border-collapse:collapse; margin-top:6px}
    .t th,.t td{border:1px solid #e5e7eb; padding:6px 8px; text-align:left}
    .t th{background:#f7f7f7}
    .small{font-size:11px}
    .chip{display:inline-block; border:1px solid #ddd; border-radius:999px; padding:2px 8px; font-size:11px; color:#444}
  </style>
</head>
<body>
<div class="page">

  <h1>Raport Generație — LIT</h1>

  <!-- Header generație -->
  <div class="kv">
    <table>
      <tr><td class="k"><strong>ID generație</strong></td><td class="v"><?= (int)$GEN['id'] ?></td></tr>
      <tr><td class="k"><strong>Nume</strong></td><td class="v"><?= esc_html($GEN['name'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>An</strong></td><td class="v"><?= esc_html((string)($GEN['year'] ?? '—')) ?></td></tr>
      <tr><td class="k"><strong>Nivel</strong></td><td class="v"><span class="chip"><?= esc_html($GEN['level'] ?? '—') ?></span></td></tr>
      <tr><td class="k"><strong>Profesor</strong></td><td class="v"><?= esc_html($GEN['professor_name'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>Tutori</strong></td><td class="v"><?= esc_html($GEN['tutors_names'] ?? '—') ?></td></tr>
      <tr><td class="k"><strong>Nr. elevi</strong></td><td class="v"><?= (int)($GEN['total_students'] ?? 0) ?></td></tr>
      <tr><td class="k"><strong>Școala</strong></td><td class="v"><?= $school_block ?></td></tr>
    </table>
  </div>

  <!-- (Opțional) KPI LIT — dacă helper-ul tău expune ceva gen medii/delta, pune-le aici -->
  <?php if (isset($lit_kpi) && is_array($lit_kpi)): ?>
    <div class="kpi">
      <span class="label"><?= esc_html($lit_kpi['label'] ?? 'Indicator LIT') ?></span><br>
      <span class="val"><?= esc_html($lit_kpi['value'] ?? '—') ?></span>
    </div>
  <?php endif; ?>

  <!-- Elevi — ultimul LIT disponibil -->
  <h2>Elevi — ultimul LIT disponibil</h2>
  <table class="t small">
    <thead>
      <tr>
        <th>#</th>
        <th>Nume elev</th>
        <th>Etapă</th>
        <th>Completare</th>
        <th>Scor</th>
        <th>Data</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($latest_by_student) {
        $i=0;
        $rows_print = [];
        foreach ($latest_by_student as $sid=>$r) {
          $rows_print[] = ['sid'=>$sid, 'name'=>$GLOBALS['student_name'][$sid] ?? ('Elev #'.$sid), 'r'=>$r];
        }
        usort($rows_print, function($a,$b){ return strcasecmp($a['name'], $b['name']); });

        foreach ($rows_print as $item) {
          $i++;
          $sid = $item['sid'];
          $nm  = $item['name'];
          $r   = $item['r'];
          $st  = strtoupper((string)rv($r, 'stage', rv($r,'modul','—')));
          $c   = rv($r, 'completion', null);
          $s   = rv($r, 'score', null);
          $d   = rv($r, 'created_at', null);
          echo '<tr>';
          echo '<td>'.$i.'</td>';
          echo '<td>'.esc_html($nm).'</td>';
          echo '<td>'.esc_html($st).'</td>';
          echo '<td>'.($c !== null ? (round((float)$c).'%') : '—').'</td>';
          echo '<td>'.($s !== null ? number_format((float)$s,2) : '—').'</td>';
          echo '<td>'.$fmtDate($d).'</td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="6" class="muted">Nu sunt rezultate LIT de afișat.</td></tr>';
      }
      ?>
    </tbody>
  </table>

  <!-- Lista elevilor din generație -->
  <h2>Lista elevilor</h2>
  <table class="t small">
    <thead>
      <tr><th>#</th><th>Nume elev</th><th>Vârstă</th><th>Sex</th></tr>
    </thead>
    <tbody>
    <?php
    if (!empty($students)) {
      $i=0;
      foreach ($students as $s) {
        $i++;
        $name = trim(($s->last_name ?? '').' '.($s->first_name ?? ''));
        echo '<tr>';
        echo '<td>'.$i.'</td>';
        echo '<td>'.esc_html($name ?: '—').'</td>';
        echo '<td>'.esc_html(isset($s->age)? (string)$s->age : '—').'</td>';
        echo '<td>'.esc_html(isset($s->gender)? (string)$s->gender : '—').'</td>';
        echo '</tr>';
      }
    } else {
      echo '<tr><td colspan="4" class="muted">Nu sunt elevi în această generație.</td></tr>';
    }
    ?>
    </tbody>
  </table>

</div>
</body>
</html>
