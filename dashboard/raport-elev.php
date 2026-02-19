<?php
/* Template Name: Raport Elev */
if (!is_user_logged_in()) { wp_redirect(wp_login_url()); exit; }
global $wpdb;

/* flags + compat (păstrează ale tale) */
$DEBUG = !empty($_GET['debug']) && $_GET['debug'] == '1';
if (!function_exists('str_starts_with')) {
  function str_starts_with($h,$n){ return $n===''?true:strpos($h,$n)===0; }
}
if (!function_exists('is_serialized')) {
  function is_serialized($d){ if(!is_string($d))return false; $d=trim($d); if($d==='N;')return true; if(!preg_match('/^[adObis]:/',$d))return false; return @unserialize($d)!==false||$d==='b:0;'; }
}
function edus_parse_ids($raw){
  if (empty($raw)) return [];
  if (is_array($raw)) return array_values(array_filter(array_map('intval', $raw)));

  if (is_string($raw)) {
    $s = trim($raw);

    // JSON: "[2,5]" sau "2"
    if ((str_starts_with($s, '[') && substr($s,-1)===']') || ($s !== '' && ctype_digit($s))) {
      $json = json_decode($s, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        return edus_parse_ids($json);
      }
    }

    // Serialized: 'a:1:{i:0;i:2;}', etc.
    if (preg_match('/^[adObis]:/', $s)) {
      $val = @unserialize($s);
      if ($val !== false || $s === 'b:0;') {
        return edus_parse_ids($val);
      }
    }

    // CSV: "2,5,9"
    $parts = preg_split('/\s*,\s*/', $s);
    return array_values(array_filter(array_map('intval', $parts)));
  }
  return [];
}

/* URL / IDs */
$student_id = intval(get_query_var('student_id'));
if(!$student_id && !empty($_SERVER['REQUEST_URI']) && preg_match('#/panou/raport/elev/(\d+)/?#',$_SERVER['REQUEST_URI'],$m)) $student_id=intval($m[1]);
if(!$student_id){ echo '<div class="max-w-6xl p-6 mx-auto"><p class="text-red-600">Elev invalid.</p></div>'; get_footer(); return; }

/* Tabele */
$tbl_students    = $wpdb->prefix.'edu_students';
$tbl_results     = $wpdb->prefix.'edu_results';
$tbl_generations = $wpdb->prefix.'edu_generations';
$tbl_schools     = $wpdb->prefix.'edu_schools';


/* Fetch elev */
$student = $wpdb->get_row($wpdb->prepare("SELECT s.* FROM {$tbl_students} s WHERE s.id=%d",$student_id));
if(!$student){ echo '<div class="max-w-6xl p-6 mx-auto"><p class="text-red-600">Elevul nu a fost găsit.</p></div>'; get_footer(); return; }

/* Profesor & generatie (păstrează-ți codul existent pentru header) */
$teacherName = '';
if (!empty($student->professor_id)) { $u = get_userdata(intval($student->professor_id)); if($u) $teacherName = $u->display_name ?: ''; }
if(!$teacherName) $teacherName='Profesor';

$generationLabel = '';
if (!empty($student->generation_id)) {
  $has_gen_table = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl_generations)) === $tbl_generations;
  if ($has_gen_table) {
    $gen = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_generations} WHERE id=%d", $student->generation_id));
    if ($gen) {
      $generationLabel = $gen->name ?? $gen->label ?? '';
      if(!$generationLabel) $generationLabel = 'Generația '.$gen->id;
    }
  }
  if(!$generationLabel) $generationLabel = 'Generația #'.intval($student->generation_id);
} else { $generationLabel = '—'; }

$safeStudent = trim(($student->first_name ?? '').' '.($student->last_name ?? 'Elev'));

$tbl_schools = $wpdb->prefix.'edu_schools';
$schoolDisplay = '—';
$profId = !empty($student->professor_id) ? (int)$student->professor_id : 0;

if ($profId) {
  // 1) ia meta
  $assigned = get_user_meta($profId, 'assigned_school_ids', true);
  $schoolIds = edus_parse_ids($assigned);

  if ($DEBUG) {
    echo '<!-- EDU-DEBUG school_ids raw='.esc_html(var_export($assigned,true)).' parsed='.esc_html(json_encode($schoolIds)).' -->';
  }

  if (!empty($schoolIds)) {
    // Preferăm simplu: ia toate școlile în ordinea ID-urilor (suficient)
    $placeholders = implode(',', array_fill(0, count($schoolIds), '%d'));
    $schools = $wpdb->get_results($wpdb->prepare("
      SELECT id, short_name, name, location, superior_location, county
      FROM {$tbl_schools}
      WHERE id IN ($placeholders)
    ", $schoolIds));

    if ($DEBUG) {
      echo '<!-- EDU-DEBUG schools found='.esc_html(count($schools ?? [])).' -->';
    }

    if ($schools) {
      $parts = [];
      foreach ($schools as $sc) {
        $short = trim((string)($sc->short_name ?: $sc->name));
        $loc   = trim((string)$sc->location);            // sat/comună
        $sup   = trim((string)$sc->superior_location);   // oraș
        $cnty  = trim((string)$sc->county);              // județ
        $where = array_filter([$loc ?: null, $sup ?: null, $cnty ?: null]);
        $parts[] = $short . ( $where ? (' — ' . implode(', ', $where)) : '' );
      }
      if (!empty($parts)) {
        $schoolDisplay = implode('; ', $parts);
      }
    }
  }
}

/* Fetch toate rezultatele elevului (vor fi împărțite în helper-e) */
$rowsRaw = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_results} WHERE student_id=%d",$student_id));

/* ============================ CALCULE ============================ */
require_once __DIR__ . '/raport-elev-helpers-common.php';
/* SEL */
require_once __DIR__ . '/raport-elev-helper-sel.php';
/* LIT */
require_once __DIR__ . '/raport-elev-helper-lit.php';


$urlSelCsv  = $urlSelCsv  ?? '';
$fileSelCsv = $fileSelCsv ?? '';
$urlSelPdf  = $urlSelPdf  ?? '';
$fileSelPdf = $fileSelPdf ?? '';

/* ============================ UI ============================ */
/* Toolbar + chips meta + tab nav — păstrează-ți exact markup-ul tău existent.
   Important e containerul cu x-data="{tab:'sel'}" și cele două zone x-show.
*/
?>
<section class="sticky top-0 z-10 border-b inner-submenu bg-slate-800 border-slate-200">
  <div class="relative z-10 flex items-center justify-between px-2 py-2 gap-x-2">
    <div class="flex items-center justify-start gap-x-4">
      <?php if($viewer_mode === 'TUTOR') : ?>
        <a href="<?= esc_url(home_url('/panou/generatia/') . $student->generation_id) ?>"
            class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-white rounded-md hover:bg-slate-700">
          <span class="transition-transform duration-150 ease-in-out group-hover:-translate-x-1">←</span> Înapoi
        </a>
      <?php elseif($viewer_mode === 'PROF') : ?>
        <a href="<?= esc_url(home_url('/panou/lista')) ?>"
            class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-white rounded-md hover:bg-slate-700">
          <span class="transition-transform duration-150 ease-in-out group-hover:-translate-x-1">←</span> Înapoi
        </a>
      <?php else : ?>
        <a href="<?= esc_url(home_url('/panou/generatia/') . $student->generation_id) ?>"
            class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-white rounded-md hover:bg-slate-700">
          <span class="transition-transform duration-150 ease-in-out group-hover:-translate-x-1">←</span> Înapoi
        </a>
      <?php endif;?>

      <div class="flex items-center gap-2 text-xl font-semibold text-slate-800 lg:text-2xl">
        <span class="inline-flex items-center py-1 text-xs uppercase text-slate-400">
          Raport elev
        </span>
        <span class="text-sm text-white"><?= esc_html($safeStudent) ?></span>
      </div>

      <div class="flex items-center gap-2 text-xl font-semibold text-slate-800 lg:text-2xl">
        <span class="inline-flex items-center py-1 text-xs uppercase text-slate-400">
          Profesor
        </span>
        <span class="text-sm text-white"><?= esc_html($teacherName) ?></span>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <!-- Export CSV -->
      <?php if (!empty($urlSelCsv)) { ?>
        <button type="button"
                onclick="exportAndNotify('<?= esc_url($urlSelCsv) ?>','<?= esc_attr($fileSelCsv) ?>')"
                class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-white rounded-md hover:bg-slate-700">
          <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="size-4">
            <path d="M3 4h18v4H3z"></path><path d="M3 10h18v10H3z"></path><path d="M7 14h2"></path><path d="M11 14h2"></path><path d="M15 14h2"></path>
          </svg>
          Export CSV
        </button>
      <?php } ?>
      <!-- Export PDF -->
      <?php if (!empty($urlSelPdf)) { ?>
        <button type="button"
                onclick="exportAndNotify('<?= esc_url($urlSelPdf) ?>','<?= esc_attr($fileSelPdf) ?>')"
                class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-white rounded-md hover:bg-slate-700">
          <svg viewBox="0 0 24 24" class="size-4" fill="currentColor">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Zm0 0v6h6"></path>
            <path d="M8 13h2.5a1.5 1.5 0 0 1 0 3H8zM13 13h1a2 2 0 1 1 0 4h-1zM8 17h1.5"></path>
          </svg>
          Export PDF
        </button>
      <?php } ?>

      <a href="../documentatie/#profesori" target="_blank" rel="noopener noreferrer"
      class="inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-semibold text-white rounded-md hover:bg-slate-700">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
          <path d="M12 .75a8.25 8.25 0 0 0-4.135 15.39c.686.398 1.115 1.008 1.134 1.623a.75.75 0 0 0 .577.706c.352.083.71.148 1.074.195.323.041.6-.218.6-.544v-4.661a6.714 6.714 0 0 1-.937-.171.75.75 0 1 1 .374-1.453 5.261 5.261 0 0 0 2.626 0 .75.75 0 1 1 .374 1.452 6.712 6.712 0 0 1-.937.172v4.66c0 .327.277.586.6.545.364-.047.722-.112 1.074-.195a.75.75 0 0 0 .577-.706c.02-.615.448-1.225 1.134-1.623A8.25 8.25 0 0 0 12 .75Z" />
          <path fill-rule="evenodd" d="M9.013 19.9a.75.75 0 0 1 .877-.597 11.319 11.319 0 0 0 4.22 0 .75.75 0 1 1 .28 1.473 12.819 12.819 0 0 1-4.78 0 .75.75 0 0 1-.597-.876ZM9.754 22.344a.75.75 0 0 1 .824-.668 13.682 13.682 0 0 0 2.844 0 .75.75 0 1 1 .156 1.492 15.156 15.156 0 0 1-3.156 0 .75.75 0 0 1-.668-.824Z" clip-rule="evenodd" />
        </svg>
        Documentatie
      </a>
    </div>
  </div>
</section>


<div class="w-full px-6 pb-8 transition-content" x-data="{tab:'sel'}">
  <!-- Chips meta elev (culori diferite) -->
  <div class="flex-wrap items-center hidden gap-2 mt-4 mb-6 text-sm sm:flex">
    <!-- Clasă -->
    <span class="inline-flex items-center gap-2 px-3 py-1 text-emerald-800 rounded-xl bg-emerald-50 ring-1 ring-inset ring-emerald-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-emerald-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
      </svg>
      <span class="font-semibold"><?= esc_html($student->class_label ?: '—') ?></span>
    </span>

    <!-- Vârstă -->
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-amber-50 text-amber-900 ring-1 ring-inset ring-amber-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-amber-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Zm-3 0a.375.375 0 1 1-.53 0L9 2.845l.265.265Zm6 0a.375.375 0 1 1-.53 0L15 2.845l.265.265Z" />
      </svg>
      <span class="font-medium">Vârstă:</span>
      <span class="font-semibold"><?= esc_html($student->age ?: '—') ?> ani</span>
    </span>

    <!-- Sex -->
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-fuchsia-50 text-fuchsia-800 ring-1 ring-inset ring-fuchsia-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-fuchsia-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
      </svg>
      <span class="font-medium">Sex:</span>
      <span class="font-semibold"><?= esc_html($student->gender ?: '—') ?></span>
    </span>

    <!-- Școală -->
    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-sky-50 text-sky-800 ring-1 ring-inset ring-sky-200">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-sky-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
      </svg>
      <span class="font-medium">Școală:</span>
      <span class="font-semibold"><?= $schoolDisplay ? esc_html($schoolDisplay) : '—' ?></span>
    </span>
  </div>

  <!-- Tabs -->
  <div class="inline-flex p-1 mb-4 bg-white border rounded-xl">
    <button @click="tab='sel'" :class="tab==='sel' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
            class="px-4 py-2 text-xs font-semibold tracking-wide uppercase rounded-lg">SEL</button>
    <button @click="tab='lit'" :class="tab==='lit' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
            class="px-4 py-2 text-xs font-semibold tracking-wide uppercase rounded-lg">Literație</button>
    <button @click="tab='num'" :class="tab==='num' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
            class="px-4 py-2 text-xs font-semibold tracking-wide uppercase rounded-lg">Numerație</button>
  </div>

  <!-- TAB: SEL -->
  <div x-show="tab==='sel'" x-cloak class="space-y-6">
    <?php require __DIR__ . '/raport-elev-tab-sel.php'; ?>
  </div>

  <!-- TAB: LIT -->
  <div x-show="tab==='lit'" x-cloak class="space-y-6">
    <?php require __DIR__ . '/raport-elev-tab-lit.php'; ?>
  </div>

  <!-- TAB: NUM -->
  <div x-show="tab==='num'" x-cloak class="space-y-6">
    <?php //require __DIR__ . '/raport-elev-tab-num.php'; ?>
  </div>
</div>

<!-- Toast (Tailwind only) -->
<div id="toast" class="fixed right-4 bottom-4 z-[9999] bg-gray-900 text-white px-3.5 py-2.5 rounded-xl shadow-xl text-sm opacity-0 translate-y-2 transition"></div>

<script>
  function showToast(msg,type='success',timeout=3000){
    const t=document.getElementById('toast');
    t.textContent=msg;
    t.className='fixed right-4 bottom-4 z-[9999] px-3.5 py-2.5 rounded-xl shadow-xl text-sm transition '+
      (type==='error'?'bg-red-800 text-white':'bg-emerald-700 text-white')+' opacity-100 translate-y-0';
    setTimeout(()=>{t.classList.add('opacity-0','translate-y-2')},timeout);
  }
  async function exportAndNotify(url,filename){
    try{
      const resp=await fetch(url,{credentials:'same-origin'});
      if(!resp.ok) throw new Error('Eroare server ('+resp.status+')');
      const blob=await resp.blob(); const obj=URL.createObjectURL(blob); const a=document.createElement('a');
      a.href=obj; a.download=filename||'export'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(obj);
      showToast('Fișierul a fost descărcat.','success');
    }catch(e){ showToast('Exportul a eșuat: '+(e.message||e),'error',5000); }
  }
</script>



