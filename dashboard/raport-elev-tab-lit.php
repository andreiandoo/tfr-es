<?php
/** TAB UI — LIT (carduri 3 + tabele)
 *  Necesită helpers common & lit + variabilele expuse din helper.
 */

/* utilitare UI locale */
if (!function_exists('lit_color_badge')) {
  function lit_color_badge($val, $colorKey, $labelPrefix='Δ clasă'){
    if ($val===null) return '—';
    $cls = 'bg-gray-100 text-gray-700';
    if ($colorKey==='green')         $cls = 'bg-emerald-100 text-emerald-800 ring-1 ring-inset ring-emerald-200';
    elseif ($colorKey==='red')       $cls = 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-200';
    elseif ($colorKey==='red-strong')$cls = 'bg-red-600 text-white';
    $s  = ($val>0?'+':'').number_format($val,0);
    return '<span class="inline-flex items-center px-2 py-0.5 text-lg font-medium rounded '.$cls.'">'.$s.'</span>';
  }
}
if (!function_exists('lit_color_text_class')) {
  function lit_color_text_class($colorKey){
    if ($colorKey==='green') return 'text-white bg-emerald-700 rounded px-1.5 py-0.5';
    if ($colorKey==='red') return 'text-white bg-rose-600 rounded px-1.5 py-0.5'; //text-rose-700
    if ($colorKey==='red-strong') return 'text-white bg-rose-600 rounded px-1.5 py-0.5';
    return 'text-slate-800';
  }
}
if (!function_exists('lit_remedial_chip')) {
  function lit_remedial_chip($is){
    return $is ? '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-rose-600 text-white"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 2 22h20L12 2Zm0 6 1 8h-2l1-8Zm0 10.5a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"/></svg> Remedial</span>' : '';
  }
}
/* alias pt badge % (din helpers common) */
$P = function($pct,$size='sm'){ return pct_badge($pct,$size); };

if (!function_exists('badge_score')) {
  function badge_score($v){
    if ($v===null || $v==='') return '<span class="inline-block text-xs text-slate-400">—</span>';
    $txt = is_numeric($v) ? (string)(intval($v)) : esc_html((string)$v);
    return '<span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs">'.$txt.'</span>';
  }
}
$__lit_generation_id = (isset($student) && is_object($student) && isset($student->generation_id)) ? (int)$student->generation_id : 0;

/* nivel mediu -> etichetă */
if (!function_exists('lit_level_num_to_label')) {
  function lit_level_num_to_label($num){
    if ($num===null) return '—';
    $r = round($num);
    if ($r <= -1) return 'PP';
    if ($r === 0) return 'P';
    if ($r >= 1 && $r <= 4) return (string)$r;
    return (string)$r;
  }
}

/* Δ strict colorat (verde/roșu/gri) */
if (!function_exists('lit_delta_chip_strict')) {
  function lit_delta_chip_strict($v, $size = 'sm'){
    if ($v===null) return '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-600">—</span>';
    $cls = ($v>0)
      ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200'
      : (($v<0) ? 'bg-rose-100 text-rose-800 ring-1 ring-rose-200'
                : 'bg-slate-100 text-slate-700 ring-1 ring-slate-200');
    $txt = ($v>0?'+':'').(is_float($v)? (string)round($v,2) : (string)$v);
    $pad = $size==='sm' ? 'px-1.5 py-0.5 text-xs' : ($size==='md' ? 'px-2 py-0.5 text-sm' : 'px-2.5 py-1 text-base');
    return '<span class="inline-flex items-center font-semibold '.$pad.' rounded '.$cls.'">'.$txt.'</span>';
  }
}

/* map scara PP=-2, P=-1, 0..5 */
if (!function_exists('lit_map_level_strict_for_diff')) {
  function lit_map_level_strict_for_diff($raw){
    if ($raw===null || $raw==='') return null;
    $u = trim(strtoupper((string)$raw));
    if ($u==='PP') return -2;
    if ($u==='P')  return -1;
    if (is_numeric($u)) return (int)$u; // 0..5
    return null;
  }
}
?>

<!-- ======================= -->
<!-- LIT — KPIs (3 carduri)  -->
<!-- ======================= -->
<section class="">
  <div class="grid items-stretch grid-cols-1 gap-4 lg:grid-cols-3">

    <!-- Card #1: T0 -->
    <div class="p-4 bg-white border rounded-xl">
      <div class="flex items-center justify-between">
        <div class="text-sm font-bold text-slate-800">T0 <?= lit_remedial_chip($lit_remedial['t0']) ?></div>
        <?= status_chip($lit_status['t0'] ?? null) ?>
      </div>

      <div class="mt-3 space-y-2 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Completare</span>
          <span class="font-semibold"><?= $lit_completion['t0']!==null ? intval($lit_completion['t0']).'%' : '—' ?></span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Nivel Acuratețe</span>
          <?php
            $acc0_val = $lit_levels['acc']['t0'] ?? null;
            $acc0_cls = lit_color_text_class($lit_dif_clasa['t0']['acc_color'] ?? null);
          ?>
          <span class="font-semibold <?= esc_attr($acc0_cls) ?>">
            <?= ($acc0_val!==null ? esc_html((string)$acc0_val) : '—') ?>
          </span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Nivel Comprehensiune</span>
          <?php
            $cmp0_val = $lit_levels['comp']['t0'] ?? null;
            $cmp0_cls = lit_color_text_class($lit_dif_clasa['t0']['comp_color'] ?? null);
          ?>
          <span class="font-semibold <?= esc_attr($cmp0_cls) ?>">
            <?= ($cmp0_val!==null ? esc_html((string)$cmp0_val) : '—') ?>
          </span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Remedial (dacă există)</span>
          <span class="flex items-center gap-2">
            <?php if (!empty($lit_remedial['t0'])): ?>
              <span class="text-[13px] font-semibold">
                <?= ($lit_stage['t0']['total_points']!==null? intval($lit_stage['t0']['total_points']).' / '.$lit_scheme['total_max'] : '—') ?>
              </span>
              <?= $P($lit_stage['t0']['total_pct'],'sm') ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Card #2: T1 -->
    <div class="p-4 bg-white border rounded-xl">
      <div class="flex items-center justify-between">
        <div class="text-sm font-bold text-slate-800">T1 <?= lit_remedial_chip($lit_remedial['t1']) ?></div>
        <?= status_chip($lit_status['t1'] ?? null) ?>
      </div>

      <div class="mt-3 space-y-2 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Completare</span>
          <span class="font-semibold"><?= $lit_completion['t1']!==null ? intval($lit_completion['t1']).'%' : '—' ?></span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Nivel Acuratețe</span>
          <?php
            $acc1_val = $lit_levels['acc']['t1'] ?? null;
            $acc1_cls = lit_color_text_class($lit_dif_clasa['t1']['acc_color'] ?? null);
          ?>
          <span class="font-semibold <?= esc_attr($acc1_cls) ?>">
            <?= ($acc1_val!==null ? esc_html((string)$acc1_val) : '—') ?>
          </span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Nivel Comprehensiune</span>
          <?php
            $cmp1_val = $lit_levels['comp']['t1'] ?? null;
            $cmp1_cls = lit_color_text_class($lit_dif_clasa['t1']['comp_color'] ?? null);
          ?>
          <span class="font-semibold <?= esc_attr($cmp1_cls) ?>">
            <?= ($cmp1_val!==null ? esc_html((string)$cmp1_val) : '—') ?>
          </span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Remedial (dacă există)</span>
          <span class="flex items-center gap-2">
            <?php if (!empty($lit_remedial['t1'])): ?>
              <span class="text-[13px] font-semibold">
                <?= ($lit_stage['t1']['total_points']!==null? intval($lit_stage['t1']['total_points']).' / '.$lit_scheme['total_max'] : '—') ?>
              </span>
              <?= $P($lit_stage['t1']['total_pct'],'sm') ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Card #3: Δ T1 − T0 -->
    <?php
      // Δ pe niveluri folosind scara strictă PP=-2, P=-1, 0..5
      $acc0_map = lit_map_level_strict_for_diff($lit_levels['acc']['t0'] ?? null);
      $acc1_map = lit_map_level_strict_for_diff($lit_levels['acc']['t1'] ?? null);
      $cmp0_map = lit_map_level_strict_for_diff($lit_levels['comp']['t0'] ?? null);
      $cmp1_map = lit_map_level_strict_for_diff($lit_levels['comp']['t1'] ?? null);
      $d_acc = ($acc0_map!==null && $acc1_map!==null) ? ($acc1_map - $acc0_map) : null;
      $d_cmp = ($cmp0_map!==null && $cmp1_map!==null) ? ($cmp1_map - $cmp0_map) : null;

      // procent remedial: medie aritmetică a procentelor la momentele în care e remedial
      $rem_pcts = [];
      if (!empty($lit_remedial['t0']) && $lit_stage['t0']['total_pct']!==null) $rem_pcts[] = $lit_stage['t0']['total_pct'];
      if (!empty($lit_remedial['t1']) && $lit_stage['t1']['total_pct']!==null) $rem_pcts[] = $lit_stage['t1']['total_pct'];
      $rem_pct_avg = !empty($rem_pcts) ? (array_sum($rem_pcts)/count($rem_pcts)) : null;

      // media pe gradul de completare (nou)
      $comp_vals = [];
      if ($lit_completion['t0']!==null) $comp_vals[] = (int)$lit_completion['t0'];
      if ($lit_completion['t1']!==null) $comp_vals[] = (int)$lit_completion['t1'];
      $completion_avg = !empty($comp_vals) ? (array_sum($comp_vals)/count($comp_vals)) : null;
    ?>
    <div class="p-4 bg-white border rounded-xl">
      <div class="flex items-center justify-between">
        <div class="text-sm font-bold text-slate-800">Δ T1−T0</div>
        <div class="text-xs text-slate-500">diferențe & medii</div>
      </div>

      <div class="mt-3 space-y-2 text-sm">
        <div class="flex items-center justify-between">
          <span class="text-slate-500">Δ Nivel Acuratețe</span>
          <span><?= lit_delta_chip_strict($d_acc,'md') ?></span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Δ Nivel Comprehensiune</span>
          <span><?= lit_delta_chip_strict($d_cmp,'md') ?></span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Remedial % (medie)</span>
          <span><?= $P($rem_pct_avg,'sm') ?></span>
        </div>

        <div class="flex items-center justify-between">
          <span class="text-slate-500">Completare (medie)</span>
          <span class="font-semibold"><?= $completion_avg!==null ? intval(round($completion_avg)).'%' : '—' ?></span>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ================================ -->
<!-- LIT — Rezultate pe itemi (core)  -->
<!-- ================================ -->
<section class="p-4 mt-4 bg-white border rounded-2xl">
  <h3 class="px-4 mb-4 text-lg font-semibold">Rezultate pe itemi</h3>
  <div class="overflow-x-auto">
    <table class="min-w-[700px] w-full text-sm">
      <thead>
        <tr class="text-slate-700">
          <th class="px-3 py-2 text-left align-bottom">Întrebare</th>
          <th class="px-3 py-2 text-center align-bottom">T0</th>
          <th class="px-3 py-2 text-center align-bottom">T1</th>
          <th class="px-3 py-2 text-center align-bottom">Δ T1−T0</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php
        // ordine pentru itemii „core”
        $order = ['lit_q1','lit_q2','lit_q3','lit_q4','lit_q5','lit_q6'];
        foreach($order as $k):
          $rowLabel = $lit_focus['t0'][$k]['label'] ?? ($lit_focus['t1'][$k]['label'] ?? $k);
          $s0_raw = $lit_focus['t0'][$k]['raw'] ?? null;
          $s1_raw = $lit_focus['t1'][$k]['raw'] ?? null;

          // Δ: pentru q1, q2, q4, q5 folosim scara PP=-2,P=-1,0..5; altfel Δ numeric
          $dT = null;
          if (in_array($k, ['lit_q1','lit_q2','lit_q4','lit_q5'], true)) {
            $v0 = lit_map_level_strict_for_diff($s0_raw);
            $v1 = lit_map_level_strict_for_diff($s1_raw);
            $dT = ($v0!==null && $v1!==null) ? ($v1 - $v0) : null;
          } else {
            $dT = (is_numeric($s0_raw) && is_numeric($s1_raw)) ? (float)$s1_raw - (float)$s0_raw : null;
          }
        ?>
        <tr>
          <td class="px-3 py-2 font-medium text-slate-900"><?= esc_html($rowLabel) ?></td>
          <td class="px-3 py-2 text-center"><?= ($s0_raw!==null && $s0_raw!=='') ? esc_html((string)$s0_raw) : '—' ?></td>
          <td class="px-3 py-2 text-center"><?= ($s1_raw!==null && $s1_raw!=='') ? esc_html((string)$s1_raw) : '—' ?></td>
          <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($dT,'sm') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <!-- TOTAL eliminat conform cerinței -->
    </table>
  </div>
  <p class="px-2 mt-2 text-xs text-slate-500">
    Diferența (Δ) este calculată numeric; pentru
    <strong>Lista de cuvinte</strong>, <strong>Acuratețe citire</strong>,
    <strong>Comprehensiune citire</strong> și <strong>Comprehensiune audiere</strong>
    se folosește scara: PP = -2, P = -1, 0..5.
  </p>
</section>

<?php
// === PRELUARE DATE PENTRU TABELUL SUPLIMENTAR (condiționale) ===
$lit_t0_score  = $lit_score_full['t0'] ?? [];
$lit_t1_score  = $lit_score_full['t1'] ?? [];
$generation_id = $__lit_generation_id;

// Split „core” vs „condiționali”
$litTables  = edus_lit_split_items_for_tables($lit_t0_score, $lit_t1_score, $generation_id);
$condItems  = $litTables['cond'] ?? [];
$condTotals = $litTables['totals']['cond'] ?? ['t0_sum'=>null,'t1_sum'=>null,'gen_sum'=>null,'t1_t0_delta'=>null];

// Flag remedial
$isRemedialT0 = !empty($lit_remedial['t0']);
$isRemedialT1 = !empty($lit_remedial['t1']);

// Arată secțiunea dacă e Remedial pe oricare moment SAU dacă există răspunsuri pe itemii condiționali
$showCond = $isRemedialT0 || $isRemedialT1 || !empty(array_filter($condItems, function($i){
  return (isset($i['t0']['value']) && $i['t0']['value']!==null) || (isset($i['t1']['value']) && $i['t1']['value']!==null);
}));
?>

<?php if ($showCond): ?>
  <section class="p-4 mt-4 bg-white border rounded-2xl">
    <div class="flex items-center justify-between mb-3">
      <h3 class="px-2 text-lg font-semibold">Rezultate Evaluare Remedială</h3>
      <div class="text-xs">
        <?php if ($isRemedialT0): ?>
          <span class="inline-flex items-center px-2 py-0.5 text-[11px] rounded bg-amber-100 text-amber-800 ring-1 ring-amber-200 mr-1">T0: Remedial</span>
        <?php endif; ?>
        <?php if ($isRemedialT1): ?>
          <span class="inline-flex items-center px-2 py-0.5 text-[11px] rounded bg-amber-100 text-amber-800 ring-1 ring-amber-200">T1: Remedial</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm min-w-[880px]">
        <thead>
          <tr class="text-slate-700">
            <th class="px-3 py-2 text-left align-bottom">Întrebare</th>
            <th class="px-3 py-2 text-center align-bottom">T0</th>
            <th class="px-3 py-2 text-center align-bottom">T1</th>
            <th class="px-3 py-2 text-center align-bottom">Δ T1−T0</th>
            <th class="px-3 py-2 text-center align-bottom">Gen. (medie)</th>
            <th class="px-3 py-2 text-center align-bottom">Δ T0−Gen</th>
            <th class="px-3 py-2 text-center align-bottom">Δ T1−Gen</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php foreach ($condItems as $it):
            $label = $it['label'];
            $v0    = $it['t0']['value'];  $p0=$it['t0']['pct'];
            $v1    = $it['t1']['value'];  $p1=$it['t1']['pct'];
            $gavg  = $it['gen']['avg'];   $gp=$it['gen']['pct'];
            $d10   = $it['delta']['t1_t0'];
            $d0g   = $it['delta']['t0_gen'];
            $d1g   = $it['delta']['t1_gen'];
          ?>
          <tr>
            <td class="px-3 py-2 font-medium text-slate-900"><?= esc_html($label) ?></td>

            <td class="px-3 py-2 text-center">
              <?php
                if ($v0===null) echo '—';
                else {
                  echo is_numeric($v0) ? badge_score($v0) : '<span class="inline-flex px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-sm">'.esc_html($v0).'</span>';
                  if ($p0!==null) echo '<div class="text-[11px] text-slate-500">'.intval(round($p0)).'%</div>';
                }
              ?>
            </td>

            <td class="px-3 py-2 text-center">
              <?php
                if ($v1===null) echo '—';
                else {
                  echo is_numeric($v1) ? badge_score($v1) : '<span class="inline-flex px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-sm">'.esc_html($v1).'</span>';
                  if ($p1!==null) echo '<div class="text-[11px] text-slate-500">'.intval(round($p1)).'%</div>';
                }
              ?>
            </td>

            <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($d10) ?></td>

            <td class="px-3 py-2 text-center">
              <?php
                if ($gavg===null) echo '—';
                else {
                  echo badge_score($gavg);
                  if ($gp!==null) echo '<div class="text-[11px] text-slate-500">'.intval(round($gp)).'%</div>';
                }
              ?>
            </td>

            <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($d0g) ?></td>
            <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($d1g) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>

        <tfoot>
          <tr class="font-semibold bg-slate-50">
            <td class="px-3 py-2 text-slate-700">TOTAL itemi condiționali</td>
            <td class="px-3 py-2 text-center"><?php echo $condTotals['t0_sum'] ?? null;?></td>
            <td class="px-3 py-2 text-center"><?php echo $condTotals['t1_sum'] ?? null;?></td>
            <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($condTotals['t1_t0_delta'] ?? null) ?></td>
            <td class="px-3 py-2 text-center"><?php echo round($condTotals['gen_sum'] ?? null, 2);?></td>
            <?php
              $d0g_tot = (isset($condTotals['t0_sum'],$condTotals['gen_sum']) && $condTotals['t0_sum']!==null && $condTotals['gen_sum']!==null)
                ? ($condTotals['t0_sum'] - $condTotals['gen_sum']) : null;
              $d1g_tot = (isset($condTotals['t1_sum'],$condTotals['gen_sum']) && $condTotals['t1_sum']!==null && $condTotals['gen_sum']!==null)
                ? ($condTotals['t1_sum'] - $condTotals['gen_sum']) : null;
            ?>
            <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($d0g_tot) ?></td>
            <td class="px-3 py-2 text-center"><?= lit_delta_chip_strict($d1g_tot) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <p class="px-2 mt-2 text-xs text-slate-500">
      Acești itemi apar când elevul este în <strong>Remedial</strong> (Acuratețe = PP/P) și sunt incluși în totalul recalculat pentru primar/gimnaziu.
    </p>
  </section>
<?php endif; ?>


<section class=""></section>
<section class=""></section>
