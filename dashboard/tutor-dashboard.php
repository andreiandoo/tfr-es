<?php
// ——— Access control: doar Tutor sau Admin
$current_user = wp_get_current_user();
$uid          = (int) ($current_user->ID ?? 0);
$roles        = (array) ($current_user->roles ?? []);
$is_admin     = current_user_can('manage_options');
$is_tutor     = in_array('tutor', $roles, true);

$debug = !empty($_GET['debug']);

// ——— Pentru admin: poți vizualiza dashboard-ul unui tutor anume: ?tutor_id=##
$view_tutor_id = $is_admin ? (int)($_GET['tutor_id'] ?? 0) : $uid;
if ($is_admin && !$view_tutor_id) {
  $view_tutor_id = 0;
}

// ——— DB tables
global $wpdb;
$tbl_users       = $wpdb->users;
$tbl_umeta       = $wpdb->usermeta;
$tbl_generations = $wpdb->prefix . 'edu_generations';
$tbl_students    = $wpdb->prefix . 'edu_students';
$tbl_schools     = $wpdb->prefix . 'edu_schools';
$tbl_cities      = $wpdb->prefix . 'edu_cities';
$tbl_counties    = $wpdb->prefix . 'edu_counties';
$tbl_results     = $wpdb->prefix . 'edu_results';

// ——— Helpers
function td_level_label($code) {
  $map = ['prescolar'=>'Preșcolar','primar'=>'Primar','gimnazial'=>'Gimnazial','liceu'=>'Liceu','clasa-pregatitoare'=>'Clasa pregătitoare'];
  $c = strtolower(trim((string)$code));
  if ($c === 'gimnaziu') $c = 'gimnazial';
  if ($c === 'primar-mic' || $c === 'primar-mare') $c = 'primar';
  return $map[$c] ?? '—';
}
function td_fmt($ts_or_str){
  if (!$ts_or_str) return '—';
  $ts = is_numeric($ts_or_str) ? (int)$ts_or_str : strtotime($ts_or_str);
  if (!$ts) return '—';
  return date_i18n(get_option('date_format').' '.get_option('time_format'), $ts);
}
function td_prof_name($u) {
  $name = trim(($u->first_name ?? '').' '.($u->last_name ?? ''));
  if ($name === '') $name = $u->display_name ?: $u->user_login;
  return $name;
}
function td_initials($first, $last, $display='') {
  $ini = '';
  if ($first || $last) {
    if ($first) $ini .= mb_strtoupper(mb_substr($first,0,1));
    if ($last)  $ini .= mb_strtoupper(mb_substr($last,0,1));
  } else {
    $parts = preg_split('/\s+/', trim($display ?: '' ));
    if ($parts && $parts[0]) $ini .= mb_strtoupper(mb_substr($parts[0],0,1));
    if ($parts && count($parts)>1) $ini .= mb_strtoupper(mb_substr(end($parts),0,1));
  }
  return mb_substr($ini,0,2);
}
// culoare progres în funcție de procent
function td_progress_color($pct){
  $p = (int)$pct;
  if ($p >= 75) return 'bg-emerald-500';
  if ($p >= 50) return 'bg-sky-500';
  if ($p >= 25) return 'bg-amber-500';
  if ($p > 0)   return 'bg-rose-500';
  return 'bg-slate-300';
}
// badge status rezultate
function td_status_badge($status){
  $s = strtolower(trim((string)$status));
  if ($s === 'final')  return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">final</span>';
  if ($s === 'draft')  return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-amber-50 text-amber-800 ring-1 ring-amber-200">draft</span>';
  return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium bg-slate-100 text-slate-700 ring-1 ring-slate-200">'.esc_html($status ?: '—').'</span>';
}
// parsează modulul (afisare ca: [TIP] pill, apoi T0/TI/T1, apoi nivel frumos)
function td_pretty_modul($type, $slug){
  $type = strtoupper(trim($type)); // LIT / SEL
  $slug = strtolower(trim((string)$slug));
  // detect stage
  $stage = '';
  if (preg_match('/-(t0|t1|ti)\b/', $slug, $m)) $stage = strtoupper($m[1]);

  // detect nivel/etaj
  $level = '—';
  if (strpos($slug,'clasa-pregatitoare') !== false) {
    $level = 'Clasa pregătitoare';
  } else {
    foreach (['prescolar','primar','gimnazial','gimnaziu','liceu','primar-mic','primar-mare'] as $probe) {
      if (strpos($slug, $probe) !== false) { $level = td_level_label($probe); break; }
    }
  }

  $pill = '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset '.($type==='LIT' ? 'bg-indigo-50 text-indigo-700 ring-indigo-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200').'">'.$type.'</span>';

  return [$pill, ($stage ?: '—'), $level];
}

// ——— 1) Profesori gestionați de tutorul curent (sau selectat de admin)
$managed_professors = [];
if ($view_tutor_id) {
  $user_query = new WP_User_Query([
    'role'       => 'profesor',
    'number'     => -1,
    'meta_query' => [
      [
        'key'     => 'assigned_tutor_id',
        'value'   => $view_tutor_id,
        'compare' => '=',
        'type'    => 'NUMERIC',
      ]
    ],
    'orderby' => 'display_name',
    'order'   => 'ASC',
  ]);
  $managed_professors = $user_query->get_results(); // array WP_User
}
$prof_ids = array_map(fn($u)=>(int)$u->ID, $managed_professors);

// map rapid: prof_id => nume (pt. tabelul cu rezultate)
$prof_name_by_id = [];
foreach ($managed_professors as $u) {
  $prof_name_by_id[(int)$u->ID] = td_prof_name($u);
}

// ——— 2) Generații & elevi
$gens = $students = [];
$level_counts_g = ['prescolar'=>0,'primar'=>0,'gimnazial'=>0,'liceu'=>0];
$level_counts_s = ['prescolar'=>0,'primar'=>0,'gimnazial'=>0,'liceu'=>0];
$students_total = 0;

if (!empty($prof_ids)) {
  $in = implode(',', array_fill(0, count($prof_ids), '%d'));

  // Generații
  $gens = $wpdb->get_results($wpdb->prepare("
    SELECT id, professor_id, name, level, year, created_at
    FROM {$tbl_generations}
    WHERE professor_id IN ($in)
    ORDER BY year DESC, id DESC
  ", ...$prof_ids));

  foreach ($gens as $g) {
    $lvl = strtolower(trim((string)$g->level));
    if ($lvl === 'gimnaziu') $lvl = 'gimnazial';
    if ($lvl === 'primar-mic' || $lvl === 'primar-mare') $lvl = 'primar';
    if (isset($level_counts_g[$lvl])) $level_counts_g[$lvl]++;
  }

  // Elevi
  $students = $wpdb->get_results($wpdb->prepare("
    SELECT id, professor_id, generation_id, first_name, last_name, class_label, gender, age
    FROM {$tbl_students}
    WHERE professor_id IN ($in)
  ", ...$prof_ids));

  foreach ($students as $s) {
    $students_total++;
  }

  // Nivel elevi = din generația asociată
  if (!empty($students)) {
    $gen_ids = array_values(array_unique(array_map(fn($s)=>(int)$s->generation_id, $students)));
    $lvl_by_gen = [];
    if ($gen_ids) {
      $in2 = implode(',', array_fill(0, count($gen_ids), '%d'));
      $rows = $wpdb->get_results($wpdb->prepare("
        SELECT id, level FROM {$tbl_generations}
        WHERE id IN ($in2)
      ", ...$gen_ids));
      foreach ($rows as $r) $lvl_by_gen[(int)$r->id] = strtolower(trim((string)$r->level));
    }
    foreach ($students as $s) {
      $lvl = $lvl_by_gen[(int)$s->generation_id] ?? '';
      if ($lvl === 'gimnaziu') $lvl = 'gimnazial';
      if ($lvl === 'primar-mic' || $lvl === 'primar-mare') $lvl = 'primar';
      if (isset($level_counts_s[$lvl])) $level_counts_s[$lvl]++;
    }
  }
}

// ——— 3) Școli distincte
$school_ids_all = [];
if (!empty($prof_ids)) {
  foreach ($prof_ids as $pid) {
    $sids = get_user_meta($pid, 'assigned_school_ids', true);
    if (is_array($sids)) {
      foreach ($sids as $sid) {
        $sid = (int)$sid;
        if ($sid > 0) $school_ids_all[$sid] = true;
      }
    }
  }
}
$schools_total = count($school_ids_all);

// ——— 4) Ultimii profesori activi
$last_active = [];
foreach ($managed_professors as $u) {
  $pid   = (int)$u->ID;
  // IMPORTANT: folosim meta „reală” de activitate; dacă nu există, abia atunci cădem pe data înregistrării
  $last_login    = get_user_meta($pid,'last_login', true);    // setat de hook-ul tău wp_login
  $last_activity = get_user_meta($pid,'last_activity', true); // dacă îl folosești
  $last_seen     = get_user_meta($pid,'last_seen', true);     // dacă îl folosești

  $cand = [];
  foreach ([$last_activity, $last_login, $last_seen] as $v) {
    if ($v !== '' && $v !== null) {
      $cand[] = is_numeric($v) ? (int)$v : (int)strtotime($v);
    }
  }
  $last_ts = !empty($cand) ? max($cand) : (int)strtotime($u->user_registered);

  $last_active[] = [
    'id'    => $pid,
    'name'  => td_prof_name($u),
    'email' => $u->user_email,
    'first' => $u->first_name,
    'lastn' => $u->last_name,
    'disp'  => $u->display_name,
    'last'  => $last_ts,
  ];
}
usort($last_active, fn($a,$b)=> ($b['last']<=>$a['last']));
$last_active = array_slice($last_active, 0, 8);

// ——— 5) Ultimele rezultate SEL/LIT
$latest_results = [];
if (!empty($students)) {
  $student_ids = array_values(array_unique(array_map(fn($s)=>(int)$s->id,$students)));
  if ($student_ids) {
    $in3 = implode(',', array_fill(0, count($student_ids), '%d'));
    $rows = $wpdb->get_results($wpdb->prepare("
      SELECT r.id, r.student_id, r.modul_type, r.modul, r.status, r.completion, r.created_at
      FROM {$tbl_results} r
      WHERE r.student_id IN ($in3)
      ORDER BY r.created_at DESC
      LIMIT 12
    ", ...$student_ids));

    if ($rows) {
      // map elev->nume + gen
      $smap = [];
      foreach ($students as $s) {
        $smap[(int)$s->id] = [
          'name' => trim(($s->first_name ?: '').' '.($s->last_name ?: '')),
          'gen'  => (int)$s->generation_id,
        ];
      }
      // map gen->profesor
      $pid_by_gen = [];
      if (!empty($gens)) {
        foreach ($gens as $g) $pid_by_gen[(int)$g->id] = (int)$g->professor_id;
      }
      foreach ($rows as $r) {
        $sid   = (int)$r->student_id;
        $sinfo = $smap[$sid] ?? ['name'=>'Elev #'.$sid,'gen'=>0];
        $pid   = $pid_by_gen[$sinfo['gen']] ?? 0;
        $latest_results[] = [
          'student'    => $sinfo['name'],
          'prof_id'    => $pid,
          'type'       => strtoupper(trim($r->modul_type)),
          'modul'      => (string)$r->modul,
          'status'     => (string)$r->status,
          'completion' => (int)$r->completion,
          'created'    => $r->created_at,
        ];
      }
    }
  }
}

// ——— 6) Profesori fără generații / generații fără elevi
$prof_fara_gen  = [];
$gen_fara_elevi = [];
if (!empty($managed_professors)) {
  $gens_by_prof = [];
  foreach ($gens as $g) {
    $gens_by_prof[(int)$g->professor_id][] = $g->id;
  }
  foreach ($managed_professors as $u) {
    $pid = (int)$u->ID;
    if (empty($gens_by_prof[$pid])) $prof_fara_gen[] = $u;
  }

  if (!empty($gens)) {
    $cnt_by_gen = [];
    foreach ($students as $s) {
      $gid = (int)$s->generation_id;
      $cnt_by_gen[$gid] = ($cnt_by_gen[$gid] ?? 0) + 1;
    }
    foreach ($gens as $g) {
      if (empty($cnt_by_gen[(int)$g->id])) $gen_fara_elevi[] = $g;
    }
  }
}

$full_name = trim(($current_user->first_name ?? '') . ' ' . ($current_user->last_name ?? ''));
if ($full_name === '') { $full_name = $current_user->display_name ?: $current_user->user_login; }
?>
<section class="px-6 pb-8">

    <!-- Header & Context -->
    <div class="flex flex-col items-start justify-between gap-4 py-6 md:flex-row md:items-center">
        <div>
          <h1 class="text-2xl font-semibold tracking-tight">Bun venit, <?php echo esc_html($full_name); ?> 👋</h1>
          <p class="mt-1 text-sm text-slate-600">
              Overview pentru profesorii pe care îi gestionezi, generațiile și activitatea recentă.
          </p>
        </div>

        <div class="flex items-center gap-2">
        <a href="<?php echo esc_url( home_url('/panou/profesori') ); ?>"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white shadow-sm rounded-xl bg-emerald-600 hover:bg-emerald-700">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4h18v2H3zM3 9h18v2H3zM3 14h18v2H3zM3 19h18v2H3z"/></svg>
            Lista profesori
        </a>
        <?php if ($view_tutor_id): ?>
            <a href="<?php echo esc_url( home_url('/panou/tutor/'.$view_tutor_id) ); ?>"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium bg-white border shadow-sm rounded-xl border-slate-300 hover:bg-slate-50">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5.33 0-8 2.667-8 6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1c0-3.333-2.67-6-8-6Z"/></svg>
            Profilul meu
            </a>
        <?php endif; ?>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="text-xs font-medium text-slate-500">Profesori gestionați</div>
        <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo (int)count($managed_professors); ?></div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="text-xs font-medium text-slate-500">Generații</div>
        <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo (int)count($gens); ?></div>
        <div class="mt-2 text-xs text-slate-600">
            <?php if ($gens): ?>
            <?php
                $bits = [];
                foreach ($level_counts_g as $k=>$v) if ($v>0) $bits[] = td_level_label($k).": ".$v;
                echo esc_html(implode(' • ', $bits));
            ?>
            <?php else: ?>
            —
            <?php endif; ?>
        </div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="text-xs font-medium text-slate-500">Elevi (total)</div>
        <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo (int)$students_total; ?></div>
        <div class="mt-2 text-xs text-slate-600">
            <?php if ($students_total): ?>
            <?php
                $bits = [];
                foreach ($level_counts_s as $k=>$v) if ($v>0) $bits[] = td_level_label($k).": ".$v;
                echo esc_html(implode(' • ', $bits));
            ?>
            <?php else: ?>
            —
            <?php endif; ?>
        </div>
        </div>

        <div class="p-4 bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="text-xs font-medium text-slate-500">Școli unice</div>
        <div class="mt-2 text-2xl font-semibold text-slate-900"><?php echo (int)$schools_total; ?></div>
        </div>
    </div>

    <!-- Două coloane: Activitate & Rezultate -->
    <div class="grid grid-cols-1 gap-6">

        <!-- Ultimii profesori activi (acum folosește corect last_login / last_activity / last_seen) -->
        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
        <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase text-slate-800">Ultimii profesori activi</h2>
        <?php if ($last_active): ?>
            <ul class="divide-y divide-slate-100">
            <?php foreach ($last_active as $row): ?>
                <?php
                $u = get_userdata($row['id']);
                $avatar_id = $u ? get_user_meta($u->ID, 'profile_image', true) : null;
                $avatar    = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : null;
                $ini       = td_initials($row['first'], $row['lastn'], $row['disp']);
                ?>
                <li class="flex items-center justify-between gap-3 py-3">
                <div class="flex items-center min-w-0 gap-3">
                    <?php if ($avatar): ?>
                    <img src="<?php echo esc_url($avatar); ?>" class="object-cover rounded-full w-9 h-9" alt="">
                    <?php else: ?>
                    <span class="inline-flex items-center justify-center text-[11px] font-bold text-white rounded-full shadow-sm w-9 h-9 bg-gradient-to-br from-slate-600 to-slate-800">
                        <?php echo esc_html($ini); ?>
                    </span>
                    <?php endif; ?>
                    <div class="min-w-0">
                    <a href="<?php echo esc_url( home_url('/panou/profesor/'.$row['id']) ); ?>"
                        class="block font-medium truncate text-slate-900 hover:text-emerald-700"><?php echo esc_html($row['name']); ?></a>
                    <div class="text-xs truncate text-slate-500"><?php echo esc_html($row['email']); ?></div>
                    </div>
                </div>
                <div class="text-xs font-medium text-slate-600">
                    <?php echo esc_html( td_fmt($row['last']) ); ?>
                </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-sm text-slate-500">Nicio activitate recenta.</p>
        <?php endif; ?>
        </div>

        <!-- Ultimele rezultate SEL/LIT -->
        <div class="">
          <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase text-slate-800">Ultimele rezultate înregistrate</h2>
          <?php if ($latest_results): ?>
              <div class="overflow-hidden border rounded-xl border-slate-200">
                <table class="w-full overflow-hidden text-sm bg-white border rounded-xl border-slate-200">
                    <thead class="bg-sky-800">
                      <tr class="text-white">
                          <th class="px-3 py-2 text-left">Elev</th>
                          <th class="px-3 py-2 text-left">Modul</th>
                          <th class="px-3 py-2 text-left">Status</th>
                          <th class="px-3 py-2 text-left">Completare</th>
                          <th class="px-3 py-2 text-left">Profesor</th>
                          <th class="px-3 py-2 text-left">Data</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      <?php foreach ($latest_results as $r): ?>
                          <?php
                          // Modul: pill LIT/SEL + T0/TI/T1 + nivel
                          [$pill, $stage, $lvl] = td_pretty_modul($r['type'], $r['modul']);

                          // Status: badge colorat (draft=portocaliu, final=verde)
                          $status_badge = td_status_badge($r['status']);

                          // Completare: bară colorată după procent
                          $pct   = max(0, min(100, (int)$r['completion']));
                          $pcls  = td_progress_color($pct);

                          // Profesor: nume în loc de ID
                          $pname = $r['prof_id'] && isset($prof_name_by_id[$r['prof_id']]) ? $prof_name_by_id[$r['prof_id']] : '—';
                          ?>
                          <tr class="odd:bg-white even:bg-slate-50">
                            <td class="px-3 py-2 text-slate-900"><?php echo esc_html($r['student']); ?></td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center gap-2">
                                <?php echo $pill; ?>
                                <span class="text-xs font-medium text-slate-700"><?php echo esc_html($stage); ?></span>
                                <span class="text-xs text-slate-500">,</span>
                                <span class="text-xs text-slate-700"><?php echo esc_html($lvl); ?></span>
                                </div>
                            </td>
                            <td class="px-3 py-2"><?php echo $status_badge; ?></td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                <div class="w-28 h-1.5 bg-slate-200 rounded">
                                    <div class="h-1.5 rounded <?php echo esc_attr($pcls); ?>" style="width: <?php echo $pct; ?>%"></div>
                                </div>
                                <span class="text-xs text-slate-700"><?php echo $pct; ?>%</span>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <?php if ($r['prof_id'] && $pname !== '—'): ?>
                                <a href="<?php echo esc_url( home_url('/panou/profesor/'.$r['prof_id']) ); ?>"
                                    class="font-medium text-slate-800 hover:text-emerald-700"><?php echo esc_html($pname); ?></a>
                                <?php else: ?>
                                <span class="text-slate-500">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 text-slate-700"><?php echo esc_html(td_fmt($r['created'])); ?></td>
                          </tr>
                      <?php endforeach; ?>
                    </tbody>
                </table>
              </div>
          <?php else: ?>
              <p class="text-sm text-slate-500">Nu există rezultate înregistrate recent.</p>
          <?php endif; ?>
        </div>

    </div>

    <!-- Liste rapide -->
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-2">

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
        <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase text-slate-800">Profesori fără generații</h2>
        <?php if ($prof_fara_gen): ?>
            <ul class="space-y-2">
            <?php foreach ($prof_fara_gen as $u): ?>
                <li class="flex items-center justify-between gap-3 px-3 py-2 border rounded-lg border-slate-200">
                <span class="font-medium text-slate-800"><?php echo esc_html(td_prof_name($u)); ?></span>
                <a href="<?php echo esc_url( home_url('/panou/profesor/'.$u->ID) ); ?>"
                    class="text-xs font-medium text-emerald-700 hover:underline">Vezi profil</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-sm text-slate-500">Toți profesorii au cel puțin o generație.</p>
        <?php endif; ?>
        </div>

        <div class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
        <h2 class="mb-3 text-sm font-semibold tracking-wide uppercase text-slate-800">Generații fără elevi</h2>
        <?php if ($gen_fara_elevi): ?>
            <ul class="space-y-2">
            <?php foreach ($gen_fara_elevi as $g): ?>
                <li class="flex items-center justify-between gap-3 px-3 py-2 border rounded-lg border-slate-200">
                <span class="text-slate-800">
                    <strong>#<?php echo (int)$g->id; ?></strong>
                    <span class="mx-1 text-slate-400">•</span>
                    <?php echo esc_html(td_level_label($g->level)); ?>
                    <span class="mx-1 text-slate-400">•</span>
                    <?php echo esc_html($g->year); ?>
                </span>
                <a href="<?php echo esc_url( home_url('/panou/generatia/'.$g->id) ); ?>"
                    class="text-xs font-medium text-emerald-700 hover:underline">Deschide</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-sm text-slate-500">Toate generațiile au elevi înregistrați.</p>
        <?php endif; ?>
        </div>

    </div>

    <?php if ($debug): ?>
        <div class="p-4 mx-auto mt-6 text-xs border border-yellow-200 max-w-7xl bg-yellow-50 rounded-2xl">
        <strong>DEBUG</strong>
        <pre><?php echo esc_html(print_r([
            'viewer'         => $is_admin ? 'ADMIN' : 'TUTOR',
            'uid'            => $uid,
            'view_tutor_id'  => $view_tutor_id,
            'professors'     => count($managed_professors),
            'generations'    => count($gens),
            'students_total' => $students_total,
            'schools_total'  => $schools_total,
        ], true)); ?></pre>
        </div>
    <?php endif; ?>

</section>