<?php
/* Template Name: View Class */
if (!defined('ABSPATH')) exit;

wp_enqueue_script('panou-clasa', get_template_directory_uri() . '/js/panou-clasa.js', ['jquery'], filemtime(get_template_directory() . '/js/panou-clasa.js'), true);
wp_add_inline_script(
  'panou-clasa',
  'window.ajaxurl = "'.esc_url( admin_url('admin-ajax.php') ).'";',
  'before'
);

$current_user_id = get_current_user_id();
$current_user    = wp_get_current_user();
$class_id = get_query_var('class_id');
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

if (!$class_id || !is_numeric($class_id)) {
  echo '<div class="p-6 text-red-800 bg-red-100">Clasa inexistentă.</div>';
  get_footer('blank'); exit;
}

global $wpdb;
$classes_table  = $wpdb->prefix . 'edu_classes';
$students_table = $wpdb->prefix . 'edu_students';
$schools_table  = $wpdb->prefix . 'edu_schools';

$own_class = $wpdb->get_var($wpdb->prepare(
  "SELECT COUNT(*) FROM $classes_table WHERE id = %d AND teacher_id = %d",
  $class_id, $current_user_id
));
if (!$own_class) {
  echo '<div class="p-6 text-red-800 bg-red-100">Acces interzis sau clasa nu este a ta.</div>';
  get_footer('blank'); exit;
}

$class  = $wpdb->get_row($wpdb->prepare("SELECT * FROM $classes_table WHERE id = %d", $class_id));
$school = $wpdb->get_row($wpdb->prepare("SELECT * FROM $schools_table WHERE id = %d", $class->school_id));
$student_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $students_table WHERE class_id = %d", $class_id));

/** Nivel -> slug modul literație */
function edus_lit_modul_from_level($level){
  $lvl = mb_strtolower(trim($level ?? ''));
  $lvl = str_replace(['ș','ă','ț','â','î'], ['s','a','t','a','i'], $lvl);
  if ($lvl === 'prescolar' || $lvl === 'preșcolar') return 'literatie-prescolar';
  if (in_array($lvl, ['clasa pregatitoare','pregatitoare','pregătitoare'])) return 'literatie-pregatitoare';
  if (in_array($lvl, ['primar mic','primar mare','primar'])) return 'literatie-primar';
  if (strpos($lvl, 'gimnaz') !== false) return 'literatie-primar';
  return 'literatie-primar';
}
$lit_modul_slug = edus_lit_modul_from_level($class->level);

/** ========= Citește schema LIT din CPT `modul` via ACF ========= */
$lit_schema = ['slug'=>$lit_modul_slug,'source'=>'none','questions'=>[],'__debug'=>[]];
$modul_post = get_page_by_path($lit_modul_slug, OBJECT, 'modul');

function edus_acf_field_to_q($fo){
  if (!$fo || !is_array($fo)) return null;
  $label   = isset($fo['label']) ? $fo['label'] : strtoupper($fo['name']);
  $name    = isset($fo['name']) ? $fo['name'] : '';
  $type    = isset($fo['type']) ? $fo['type'] : 'text';
  $req     = !empty($fo['required']);
  $default = isset($fo['default_value']) ? $fo['default_value'] : null;

  $uiType = 'range';
  if (in_array($type, ['select','radio','button_group'], true)) $uiType = 'select';
  if (in_array($type, ['range','number'], true)) $uiType = 'range';

  $q = ['name'=>$name,'label'=>$label,'type'=>$uiType,'required'=>$req];

  if ($uiType === 'select') {
    $choices = [];
    if (!empty($fo['choices']) && is_array($fo['choices'])) {
      foreach ($fo['choices'] as $val=>$lab) {
        $choices[] = ['value'=>(string)$val, 'label'=>(string)($lab===''?$val:$lab)];
      }
    }
    $q['choices'] = $choices;
    if ($default !== null && $default !== '') $q['default'] = (string)$default;
  } else {
    $min  = isset($fo['min'])  ? (int)$fo['min']  : 0;
    $max  = isset($fo['max'])  ? (int)$fo['max']  : (isset($fo['range']['max']) ? (int)$fo['range']['max'] : 10);
    $step = isset($fo['step']) ? (string)$fo['step'] : '1';
    $init = ($default !== null && $default !== '') ? $default : $min;
    $q += ['min'=>$min,'max'=>$max,'step'=>$step?:'1','initial'=>(string)$init];
  }
  return $q;
}

if ($modul_post && function_exists('get_field_object')) {
  // 1) încercare directă (lit_q1..lit_q12)
  for ($i=1;$i<=12;$i++){
    $name='lit_q'.$i;
    $fo = get_field_object($name, $modul_post->ID, false);
    if ($fo) {
      $q = edus_acf_field_to_q($fo);
      if ($q) {
        if ($lit_modul_slug==='literatie-primar' && $i>=7 && $i<=12) $q['cond']=['field'=>'lit_q2','values'=>['P','PP','p','pp']];
        $lit_schema['questions'][] = $q;
      }
    }
  }
  $lit_schema['__debug']['direct_found'] = count($lit_schema['questions']);

  // 2) din grupurile ACF atașate postării
  if (count($lit_schema['questions']) < 12 && function_exists('acf_get_field_groups') && function_exists('acf_get_fields')) {
    $byName = []; $final = [];
    foreach (acf_get_field_groups(['post_id'=>$modul_post->ID]) as $grp) {
      foreach ((array)acf_get_fields($grp['key']) as $fo) {
        if (empty($fo['name'])) continue;
        if (!preg_match('/^lit_q(\d{1,2})$/', $fo['name'], $m)) continue;
        $idx = intval($m[1]);
        $q = edus_acf_field_to_q($fo);
        if ($q) {
          if ($lit_modul_slug==='literatie-primar' && $idx>=7 && $idx<=12) $q['cond']=['field'=>'lit_q2','values'=>['P','PP','p','pp']];
          $byName[$fo['name']] = $q;
        }
      }
    }
    for ($i=1;$i<=12;$i++){ $n='lit_q'.$i; if (isset($byName[$n])) $final[]=$byName[$n]; }
    if (!empty($final)) $lit_schema['questions'] = $final;
    $lit_schema['__debug']['groups_found'] = count($final);
  }

  if (!empty($lit_schema['questions'])) $lit_schema['source']='acf';
}

/** Fallback, dacă nu găsim nimic în ACF */
if (empty($lit_schema['questions'])) {
  $lit_schema['source']='fallback';
  if ($lit_modul_slug === 'literatie-prescolar') {
    $lit_schema['questions'] = [
      ['name'=>'lit_q1','label'=>'Interes pentru literație','type'=>'range','min'=>0,'max'=>22,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q2','label'=>'Vocabular expresiv','type'=>'range','min'=>0,'max'=>20,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q3','label'=>'Distingerea sunetului inițial','type'=>'range','min'=>0,'max'=>3,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q4','label'=>'Înțelegerea poveștii ascultate','type'=>'range','min'=>0,'max'=>5,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q5','label'=>'Scrierea prenumelui','type'=>'range','min'=>0,'max'=>1,'step'=>'1','initial'=>'0','required'=>true],
    ];
  } elseif ($lit_modul_slug === 'literatie-pregatitoare') {
    $lit_schema['questions'] = [
      ['name'=>'lit_q1','label'=>'Noțiunea despre textul tipărit','type'=>'range','min'=>0,'max'=>6,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q2','label'=>'Recunoașterea și reproducerea alfabetului','type'=>'range','min'=>0,'max'=>93,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q3','label'=>'Noțiunea de cuvânt','type'=>'range','min'=>0,'max'=>12,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q4','label'=>'Segmentarea fonemică','type'=>'range','min'=>0,'max'=>22,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q5','label'=>'Recunoașterea cuvintelor','type'=>'range','min'=>0,'max'=>12,'step'=>'1','initial'=>'0','required'=>true],
      ['name'=>'lit_q6','label'=>'Scrierea cuvintelor','type'=>'range','min'=>0,'max'=>5,'step'=>'1','initial'=>'0','required'=>true],
    ];
  } else { // literatie-primar
    $choices_pp = array_map(fn($v)=>['value'=>(string)$v,'label'=>(string)$v], ['P','PP','1','2','3','4']);
    $choices_p  = array_map(fn($v)=>['value'=>(string)$v,'label'=>(string)$v], ['P','1','2','3','4']);
    $condPP     = ['field'=>'lit_q2','values'=>['P','PP']];
    $lit_schema['questions'] = [
      ['name'=>'lit_q1','label'=>'Lista de cuvinte','type'=>'select','choices'=>$choices_pp,'required'=>true],
      ['name'=>'lit_q2','label'=>'Acuratețe citire','type'=>'select','choices'=>$choices_pp,'required'=>true],
      ['name'=>'lit_q3','label'=>'CCPM','type'=>'range','min'=>0,'max'=>100,'step'=>1,'initial'=>'0','required'=>true],
      ['name'=>'lit_q4','label'=>'Comprehensiune citire','type'=>'select','choices'=>$choices_p,'required'=>true],
      ['name'=>'lit_q5','label'=>'Comprehensiune audiere','type'=>'select','choices'=>$choices_p,'required'=>true],
      ['name'=>'lit_q6','label'=>'Scris (număr cuvinte pe care le scriu corect)','type'=>'range','min'=>0,'max'=>5,'step'=>1,'initial'=>'0','required'=>true],
      ['name'=>'lit_q7','label'=>'Noțiunea despre textul tipărit (0–6)','type'=>'range','min'=>0,'max'=>6,'step'=>1,'initial'=>'0','required'=>true,'cond'=>$condPP],
      ['name'=>'lit_q8','label'=>'Recunoașterea și reproducerea alfabetului (0–93)','type'=>'range','min'=>0,'max'=>93,'step'=>1,'initial'=>'0','required'=>true,'cond'=>$condPP],
      ['name'=>'lit_q9','label'=>'Noțiunea de cuvânt (0–12)','type'=>'range','min'=>0,'max'=>12,'step'=>1,'initial'=>'0','required'=>true,'cond'=>$condPP],
      ['name'=>'lit_q10','label'=>'Segmentarea fonemică (0–22)','type'=>'range','min'=>0,'max'=>22,'step'=>1,'initial'=>'0','required'=>true,'cond'=>$condPP],
      ['name'=>'lit_q11','label'=>'Recunoașterea cuvintelor (0–12)','type'=>'range','min'=>0,'max'=>12,'step'=>1,'initial'=>'0','required'=>true,'cond'=>$condPP],
      ['name'=>'lit_q12','label'=>'Scrierea cuvintelor (0–5)','type'=>'range','min'=>0,'max'=>5,'step'=>1,'initial'=>'0','required'=>true,'cond'=>$condPP],
    ];
  }
}
?>
<div class="w-full px-6 pb-8 transition-content" data-class-id="<?= esc_attr($class_id) ?>">
  <div class="transition-content flex items-center justify-between gap-4 px-(--margin-x) pt-4 mb-6">
    <div class="flex items-center min-w-0 gap-4">
      <h2 class="text-xl font-medium tracking-wide text-gray-800 truncate dark:text-dark-50 lg:text-2xl">
        Clasa <?= esc_html($class->name) ?> <small>(<?= esc_html($student_count) ?> elevi)</small>
      </h2>
      <div class="self-stretch hidden py-1 sm:flex"><div class="w-px h-full bg-gray-300 dark:bg-dark-600"></div></div>
      <ul class="flex flex-wrap items-center gap-1.5 max-sm:hidden">
        <li class="flex items-center gap-1.5">
          <a href="<?= esc_url(home_url('/panou/clase')) ?>" class="flex items-center text-sm tracking-wide transition-colors gap-x-2 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-500 active">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            Înapoi la lista de clase
          </a>
        </li>
      </ul>
    </div>
    <div class="flex space-x-2 ">
      <a href="<?= esc_url(home_url('/panou/raport/clasa/' . $class_id)) ?>" class="flex items-center h-8 px-3 space-x-2 text-xs text-gray-900 border border-gray-300 rounded-md gap-x-2 btn-base btn hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4"><path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z"/><path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.284a.75.75 0 0 1-.46.711 47.87 47.87 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.87 47.87 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286.921.304 1.83.634 2.726.99v1.27a1.5 1.5 0  0 0-.14 2.508c-.09.38-.222.753-.397 1.11.452.213.901.434 1.346.66a6.727 6.727 0  0 0 .551-1.607 1.5 1.5 0  0 0 .14-2.67v-.645a48.549 48.549 0  0 1 3.44 1.667 2.25 2.25 0  0 0 2.12 0Z"/><path d="M4.462 19.462c.42-.419.753-.89 1-1.395.453.214.902.435 1.347.662a6.742 6.742 0  0 1-1.286 1.794.75.75 0  0 1-1.06-1.06Z"/></svg>
        Raport clasa
      </a>
      <button id="toggleAddForm" class="flex items-center h-8 px-3 space-x-2 text-xs text-gray-900 border border-gray-300 rounded-md gap-x-2 btn-base btn hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4"><path d="M5.25 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM2.25 19.125a7.125 7.125 0  1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM18.75 7.5a.75.75 0 0 0-1.5 0v2.25H15a.75.75 0 0 0 0 1.5h2.25v2.25a.75.75 0 0 0 1.5 0v-2.25H21a.75.75 0  0 0 0-1.5h-2.25V7.5Z"/></svg>
        Adaugă elevi
      </button>
      <a href="<?= esc_url(home_url('/panou/clase/clasa-' . $class_id . '/edit')) ?>" class="flex items-center h-8 px-3 space-x-2 text-xs text-gray-900 border border-gray-300 rounded-md gap-x-2 btn-base btn hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4"><path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0  0 0-1.32 2.214l-.8 2.685a.75.75 0  0 0 .933.933l2.685-.8a5.25 5.25 0  0 0 2.214-1.32L19.513 8.2Z"/></svg>
        Editează clasa
      </a>
      <a href="<?= esc_url(home_url('/panou/clase/clasa-' . $class_id . '/delete')) ?>" class="flex items-center h-8 px-3 space-x-2 text-xs text-gray-900 border border-gray-300 rounded-md gap-x-2 btn-base btn hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25" onclick="return confirm('Sigur vrei să ștergi această clasă?');">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0  0 1 3.878.512.75.75 0  1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0  0 1-2.991 2.77H8.084a3 3 0  0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0  0 1-.256-1.478A48.567 48.567 0  0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0  0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-.355 5.945a.75.75 0  1 0-1.5.058l.347 9a.75.75 0  1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0  1 0-1.498-.058l-.347 9a.75.75 0  0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
        Șterge clasa
      </a>
      <button class="flex items-center h-8 px-3 space-x-2 text-xs text-gray-900 border border-gray-300 rounded-md btn-base btn hover:bg-gray-300/20 focus:bg-gray-300/20 active:bg-gray-300/25" type="button">
        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0  0 24 24" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M4 17v2a2 2 0  0 0 2 2h12a2 2 0  0 0 2 -2v-2"></path><path d="M7 9l5 -5l5 5"></path><path d="M12 4l0 12"></path></svg>
        <span>Export CSV</span>
      </button>
    </div>
  </div>

  <div class="flex items-center mb-6 gap-x-6">
    <p class="text-sm font-semibold"><strong class="px-2 py-1 mr-2 text-xs font-semibold tracking-wide text-white uppercase rounded bg-slate-800">Profesor</strong> <?= esc_html($current_user->display_name) ?></p>
    <p class="text-sm font-semibold"><strong class="px-2 py-1 mr-2 text-xs font-semibold tracking-wide text-white uppercase rounded bg-slate-800">Nivel:</strong> <?= esc_html($class->level) ?></p>
    <?php if ($school): ?>
      <p class="text-sm font-semibold"><strong class="px-2 py-1 mr-2 text-xs font-semibold tracking-wide text-white uppercase rounded bg-slate-800">Școală:</strong> <?= esc_html($school->name) ?> — <?= esc_html($school->city) ?>, <?= esc_html($school->county) ?></p>
    <?php endif; ?>
    <p class="text-sm font-semibold"><strong class="px-2 py-1 mr-2 text-xs font-semibold tracking-wide text-white uppercase rounded bg-slate-800">ID Clasă:</strong> <?= esc_html($class_id) ?></p>
  </div>

  <?php if ($debug): ?>
    <details open class="p-4 mb-6 text-sm border rounded bg-yellow-50">
      <summary class="font-semibold cursor-pointer">DEBUG (LIT)</summary>
      <div class="grid gap-2 mt-3">
        <div>Modul slug: <code><?= esc_html($lit_modul_slug) ?></code></div>
        <div>Modul post: <code><?= $modul_post ? 'FOUND #'.$modul_post->ID : 'MISSING' ?></code></div>
        <div>Schemă sursă: <code><?= esc_html($lit_schema['source']) ?></code></div>
        <div>Întrebări: <code><?= count($lit_schema['questions']) ?></code></div>
        <div>ACF direct găsite: <code><?= (int)($lit_schema['__debug']['direct_found'] ?? 0) ?></code> / din grupuri: <code><?= (int)($lit_schema['__debug']['groups_found'] ?? 0) ?></code></div>
        <pre class="p-2 overflow-auto bg-white border rounded max-h-64"><?php echo esc_html(json_encode($lit_schema, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
      </div>
    </details>
  <?php endif; ?>

  <div id="addStudentsWrapper" class="hidden p-4 mb-10 bg-green-100">
    <h2 class="mb-4 text-xl font-semibold">Adăugă elevii tăi</h2>
    <div class="flex items-center mb-4 gap-x-4">
      <label for="studentCount" class="block mb-2">Câți elevi vrei să adaugi?</label>
      <input type="number" id="studentCount" value="1" min="1" class="w-16 p-1 text-sm text-white rounded border-es-green bg-es-green">
    </div>

    <form id="addStudentsForm" class="space-y-4">
      <input type="hidden" name="action" value="add_students">
      <input type="hidden" name="class_id" value="<?= esc_attr($class_id) ?>">
      <input type="hidden" id="class_level" value="<?= esc_attr($class->level) ?>">

      <table id="studentsTable" class="w-full mt-4 text-sm border border-separate table-auto">
        <thead>
          <tr class="text-white bg-green-600">
            <th class="p-2 border">Prenume</th>
            <th class="p-2 border">Nume</th>
            <th class="p-2 border">Vârstă</th>
            <th class="p-2 border">Gen</th>
            <th class="p-2 border">Observație</th>
            <th class="p-2 border">Absenteism</th>
            <th class="p-2 border frecventa_th">Frecvență grădiniță</th>
            <th class="p-2 border bursa_th">Bursă socială</th>
            <th class="p-2 border">Limba diferită</th>
            <th class="p-2 border">Mențiuni</th>
            <th class="p-2 border">Ștergere</th>
          </tr>
        </thead>
        <tbody id="studentsContainer" class="border border-separate border-gray-200 border-spacing-0"></tbody>
      </table>

      <button type="submit" class="px-4 py-2 mt-4 text-white rounded bg-es-green hover:bg-es-green-dark">Salvează elevii</button>
    </form>
  </div>

  <h2 class="mb-4 text-xl font-semibold">Elevi existenți</h2>
  <div id="studentList"
       class="space-y-4"
       data-class-id="<?= esc_attr($class_id) ?>"
       data-lit-modul="<?= esc_attr($lit_modul_slug) ?>"
       data-class-level="<?= esc_attr($class->level) ?>">
    <div class="text-gray-500">Se încarcă elevii...</div>
  </div>
</div>

<script>
/* ====== Injectăm schema LIT (ACF/fallback) + DEBUG FLAG ====== */
window.__LIT_SCHEMA = <?php echo wp_json_encode($lit_schema, JSON_UNESCAPED_UNICODE); ?>;
window.__LIT_DEBUG  = <?php echo $debug ? 'true' : 'false'; ?>;
</script>

<script>
/* ========= LIT: coloană, headere, formular dinamic, progres, scor, salvare, prefill ========= */
(function(){
  const D=(...a)=>{ if(window.__LIT_DEBUG) console.log('[LIT]',...a); };
  const getEl=(id)=>document.getElementById(id);
  let __PATCHING_LIT = false;

  document.addEventListener('DOMContentLoaded', function(){
    const list=document.getElementById('studentList'); if(!list) return;
    const classId=list.dataset.classId||'';
    const litModul=list.dataset.litModul||(window.__LIT_SCHEMA?.slug||'literatie-primar');
    const classLvl=list.dataset.classLevel||'';
    const norm=s=>(s||'').toLowerCase().replace(/\s+/g,' ').trim();

    function headerIndices(thead){
      const row=thead?.rows?.[0]; if(!row) return {};
      let idx={selGroupStart:-1, selGroupSpan:1, raport:-1, actiuni:-1, lit:-1};
      for(let i=0,c=0;i<row.cells.length;i++){
        const th=row.cells[i], span=parseInt(th.getAttribute('colspan')||'1',10);
        const t=norm(th.textContent);
        if(idx.selGroupStart<0 && (t.includes('evaluări sel')||t.includes('evaluari sel'))){ idx.selGroupStart=c; idx.selGroupSpan=span; }
        if(t==='raport') idx.raport=c;
        if(t.includes('acțiuni')||t.includes('actiuni')) idx.actiuni=c;
        if(th.classList.contains('th-lit')||t==='evaluare literație'||t==='evaluare literatie') idx.lit=c;
        c+=span;
      }
      return idx;
    }

    function ensureHeaderOrder(table){
      const thead = table.tHead || table.querySelector('thead'); if(!thead) return;
      const row   = thead.rows[0]; if(!row) return;

      const norm = s => (s || '').toLowerCase().replace(/\s+/g,' ').trim();

      // calculăm poziția dorită a coloanei LIT
      const idx = headerIndices(thead);
      const desiredLitPos = (idx.selGroupStart >= 0)
        ? (idx.selGroupStart + idx.selGroupSpan)
        : (idx.raport >= 0 ? idx.raport : row.cells.length);

      // asigurăm <th class="th-lit">
      let thLit = thead.querySelector('.th-lit');
      if (!thLit) {
        thLit = document.createElement('th');
        thLit.className = 'p-2 border th-lit';
        thLit.textContent = 'Evaluare Literație';
        row.insertBefore(thLit, row.children[desiredLitPos] || null);
      } else {
        const cur = Array.prototype.indexOf.call(row.children, thLit);
        if (cur !== desiredLitPos) {
          row.insertBefore(thLit, row.children[desiredLitPos] || null);
        }
      }

      // repoziționează "Raport" numai dacă e cazul
      const thRap = [...row.children].find(th => norm(th.textContent) === 'raport');
      if (thRap) {
        const litIdx = Array.prototype.indexOf.call(row.children, thLit);
        const rapDesired = litIdx + 1;
        const rapCur = Array.prototype.indexOf.call(row.children, thRap);
        if (rapCur !== rapDesired) {
          row.insertBefore(thRap, row.children[rapDesired] || null);
        }
      }

      // repoziționează "Acțiuni" numai dacă e cazul (după Raport, altfel după Lit)
      const thAct = [...row.children].find(th => {
        const t = norm(th.textContent);
        return t.includes('acțiuni') || t.includes('actiuni');
      });
      if (thAct) {
        const afterNode = thRap || thLit;
        const afterIdx  = Array.prototype.indexOf.call(row.children, afterNode);
        const actDesired = afterIdx + 1;
        const actCur     = Array.prototype.indexOf.call(row.children, thAct);
        if (actCur !== actDesired) {
          row.insertBefore(thAct, row.children[actDesired] || null);
        }
      }
    }


    function computeLitInsertPos(thead){
      const idx=headerIndices(thead);
      return (idx.selGroupStart>=0)?(idx.selGroupStart+idx.selGroupSpan):(idx.raport>=0?idx.raport:thead.rows[0].cells.length);
    }

    function renderLitCell(studentId, studentFullName){
      return `
        <button type="button"
                class="btn-lit inline-flex w-full items-center justify-between px-3 py-1.5 rounded border hover:bg-gray-50 text-xs"
                data-id="${String(studentId)}" data-type="lit" data-name="${esc(studentFullName || '')}">
          <span class="inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 mr-1.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h7.879a1.5 1.5 0 0 1 1.06.44l3.621 3.621a1.5 1.5 0 0 1 .44 1.06V19.5A1.5 1.5 0 0 1 16.5 21h-12A1.5 1.5 0 0 1 3 19.5v-15Z"/></svg>
            Literație
          </span>
          <span class="lit-pill inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium rounded-full bg-gray-200">0%</span>
        </button>`;
    }

    function guessStudentName(tr){
      if (!tr) return '';
      // 1) pe <tr> dacă există
      const byData = tr.dataset?.studentName || tr.getAttribute('data-student-name');
      if (byData) return byData.trim();

      // 2) din posibila coloană "Nume elev"
      const a = tr.querySelector('.student-name, .student_full_name, a.toggle-details');
      if (a && a.textContent) return a.textContent.trim();

      // 3) din coloane separate "Prenume" + "Nume" (câteva variante comune)
      const firstEl = tr.querySelector('[data-first-name], .first-name, .prenume');
      const lastEl  = tr.querySelector('[data-last-name],  .last-name,  .nume');
      const first = (firstEl?.textContent || firstEl?.value || firstEl?.getAttribute?.('data-first-name') || '').trim();
      const last  = (lastEl?.textContent  || lastEl?.value  || lastEl?.getAttribute?.('data-last-name')  || '').trim();
      if (first || last) return `${first} ${last}`.trim();

      return '';
    }

    function ensureLitColumn(table){
      if (__PATCHING_LIT) return;            // nu intra dacă deja patch-uim
      __PATCHING_LIT = true;
      try {
        const thead = table.tHead || table.querySelector('thead');
        const tbody = table.tBodies[0] || table.querySelector('tbody');
        if (!thead || !tbody) return;

        ensureHeaderOrder(table);
        const insertPos = computeLitInsertPos(thead);

        [...tbody.rows].forEach(tr => {
          // curățăm eventuale butoane LIT rătăcite în grupul SEL
          for (let j = Math.max(0, insertPos - 3); j < insertPos; j++) {
            const c = tr.children[j]; if (!c) continue;
            c.querySelectorAll('button,a').forEach(el => {
              if ((el.dataset && el.dataset.type === 'lit') || /literat/i.test(el.textContent || '')) el.remove();
            });
          }

          let tdLit = tr.querySelector('.td-lit');
          if (!tdLit) {
            tdLit = document.createElement('td');
            tdLit.className = 'p-2 border td-lit';

            // ghicim student_id
            let sid = null;
            let sname = '';

            // încearcă să citești din butonul SEL existent de pe rând
            for (let j = Math.max(0, insertPos - 3); j < insertPos; j++) {
              const c = tr.children[j]; if (!c) continue;
              const b = c.querySelector('.start-questionnaire[data-type="sel"]');
              if (b?.dataset?.id) {
                sid   = b.dataset.id;
                sname = b.dataset.name || b.dataset.studentName || '';
                break;
              }
            }
            // fallback la data-* pe <tr> sau la input hidden
            if (!sid && tr.dataset?.id) sid = tr.dataset.id;
            const hid = tr.querySelector('input[name="student_id"]');
            if (!sid && hid?.value) sid = hid.value;

            // fallback: ghicește din rând dacă nu avem nume
            if (!sname) sname = guessStudentName(tr);
            if (sname) try { tr.setAttribute('data-student-name', sname); } catch(e) {}

            // randează celula LIT cu numele în data-name
            tdLit.innerHTML = sid
              ? renderLitCell(sid, sname)
              : '<span class="text-sm text-gray-400">—</span>';

            (tr.children[insertPos]) ? tr.insertBefore(tdLit, tr.children[insertPos]) : tr.appendChild(tdLit);

            // click → pasează numele mai departe către openLitForm
            const btn  = tdLit.querySelector('.btn-lit');
            const pill = tdLit.querySelector('.lit-pill');
            if (btn && sid) {
              btn.addEventListener('click', e => {
                e.preventDefault();
                const nm = btn.dataset.name || btn.getAttribute('data-student-name') || sname || '';
                openLitForm(sid, nm);
              });
            }
            if (sid) fetchLitProgress(pill, sid);
          } else {
            const cur = Array.prototype.indexOf.call(tr.children, tdLit);
            if (cur !== insertPos) {
              (tr.children[insertPos]) ? tr.insertBefore(tdLit, tr.children[insertPos]) : tr.appendChild(tdLit);
            }
          }
        });
        D('patched head + column (stable)');
      } finally {
        __PATCHING_LIT = false;
      }
    }


    // --------- Helpers progres ---------
    function setPill(el, pct){
      if(!el) return;
      pct = Math.max(0, Math.min(100, Math.round(Number(pct)||0)));
      el.textContent = pct + '%';
      el.classList.remove('bg-gray-200','bg-red-600','bg-yellow-400','bg-orange-500','bg-green-600','text-white');
      if (pct >= 75) { el.classList.add('bg-green-600','text-white'); }
      else if (pct >= 50) { el.classList.add('bg-orange-500','text-white'); }
      else if (pct >= 25) { el.classList.add('bg-yellow-400'); }
      else if (pct > 0)   { el.classList.add('bg-red-600','text-white'); }
      else { el.classList.add('bg-gray-200'); }
    }

    function computePercentFromResults(results, schema){
      const qs = Array.isArray(schema?.questions) ? schema.questions : [];
      if (!qs.length) return 0;

      const getVal = (name)=> (results && Object.prototype.hasOwnProperty.call(results, name)) ? results[name] : '';
      const visible = (q)=>{
        if (!q.cond || !q.cond.field || !Array.isArray(q.cond.values)) return true;
        const val = String(getVal(q.cond.field) ?? '').toUpperCase();
        const allowed = q.cond.values.map(v=>String(v).toUpperCase());
        return allowed.includes(val);
      };

      let total=0, answered=0;
      qs.forEach(q=>{
        if (!visible(q)) return;
        total++;
        const v = getVal(q.name);
        if (q.type === 'select') {
          if (String(v) !== '') answered++;
        } else {
          if (v !== '' && v !== null && v !== undefined) answered++; // '0' e valid
        }
      });
      return total ? Math.round(100*answered/total) : 0;
    }

    function fetchLitProgress(pillEl, studentId){
      if(!pillEl) return;
      const modul = (window.__LIT_SCHEMA?.slug) || 'literatie-primar';

      // 1) încearcă procentul din DB
      fetch(ajaxurl, {
        method:'POST',
        credentials:'same-origin',
        headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body:new URLSearchParams({action:'get_lit_progress', student_id:studentId, modul})
      })
      .then(r=>r.json())
      .then(data=>{
        const pct = (data && data.success) ? Number(data.data?.percent || 0) : 0;
        if (pct > 0) { setPill(pillEl, pct); return; }

        // 2) fallback: calculează din results
        return fetch(ajaxurl, {
          method:'POST',
          credentials:'same-origin',
          headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
          body:new URLSearchParams({action:'get_questionnaire', modul_type:'lit', modul, student_id:studentId})
        })
        .then(r=>r.json())
        .then(d=>{
          const results = d?.data?.results || {};
          const pct2 = computePercentFromResults(results, window.__LIT_SCHEMA);
          setPill(pillEl, pct2);
        });
      })
      .catch(()=>{ setPill(pillEl, 0); });
    }

    // --------- Overlay & Panel (on-demand) ---------
    function closePanel(){
      const overlay=getEl('overlay');
      const panel=getEl('questionnairePanel');
      const content=getEl('questionnaireContent');
      if(overlay) overlay.classList.add('hidden');
      if(panel) panel.classList.add('translate-x-full', 'right-0');
      if(content) content.innerHTML='';
    }

    const esc=s=>String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    function qToHtml(q, withHr){
      const req  = q.required ? 'data-required="1"' : '';
      const cond = (q.cond && q.cond.field && q.cond.values?.length)
        ? `data-cond-field="${esc(q.cond.field)}" data-cond-values="${esc(q.cond.values.join('|'))}" style="display:none;"`
        : '';
      if(q.type==='select'){
        const opts=(q.choices||[]).map(c=>{
          const v = ('value' in c) ? c.value : c,
                l = ('label' in c) ? c.label : c;
          const sel=(q.default!=null && String(q.default)===String(v))?' selected':'';
          return `<option value="${esc(v)}"${sel}>${esc(l)}</option>`;
        }).join('');
        return `<div class="py-3 question-wrapper" ${req} ${cond}>
                  <label class="block mb-2 text-sm font-medium question-label">${esc(q.label)}</label>
                  <select name="${esc(q.name)}" class="px-2 py-1 border rounded">
                    <option value="">—</option>${opts}
                  </select>
                  ${withHr?'<hr class="mt-3 border-gray-200">':''}
                </div>`;
      }
      const min=q.min??0, max=q.max??10, step=q.step??'1', init=(q.initial??min);
      return `<div class="py-3 question-wrapper" ${req} ${cond}>
                <label class="block mb-2 text-sm font-medium question-label">${esc(q.label)} <span class="ml-2 text-xs text-gray-500">(${min}–${max})</span></label>
                <div class="flex items-center gap-3">
                  <input type="range" name="${esc(q.name)}" min="${min}" max="${max}" step="${esc(step)}" value="${esc(init)}" class="w-64 lit-range">
                  <output class="px-2 py-0.5 text-xs border rounded">${esc(init)}</output>
                </div>
                ${withHr?'<hr class="mt-3 border-gray-200">':''}
              </div>`;
    }

    function renderLitForm(schema, studentId, studentName){
      const modul=schema?.slug||litModul;
      const qs=Array.isArray(schema?.questions)?schema.questions:[];
      const body=qs.map((q,i)=>qToHtml(q,i<qs.length-1)).join('');
      const displayName = (studentName && String(studentName).trim())
        ? esc(studentName)
        : `Elev #${esc(studentId)}`;

      console.log({studentId, studentName, displayName});
      return `
        <div class="flex items-center justify-between p-4 border-b">
          <h3 id="questionnaireTitle" class="text-lg font-semibold">Evaluare Literație — ${esc(classLvl||'Nivel necunoscut')} · ${displayName}</h3>
          <button id="closeQuestionnaire" class="px-3 py-1 border rounded">Închide</button>
        </div>
        <div class="p-4">
          <div class="flex items-center gap-2 mb-3 text-sm text-gray-600">
            <strong>Modul:</strong> <span class="px-2 py-0.5 rounded bg-slate-100">LIT — ${esc(modul)}</span>
            <span class="text-gray-400">•</span>
            <span>Schemă: ${esc(window.__LIT_SCHEMA?.source||'n/a')}</span>
          </div>
          <form id="questionnaireForm" class="space-y-2" data-type="lit" data-student="${esc(studentId)}" data-modul="${esc(modul)}">
            <div class="mb-2">
              <div id="questionnaire-progress" class="mb-1 text-sm text-gray-700">Completat: 0</div>
              <div class="h-1 bg-gray-100 rounded"><div id="progressBar" class="h-1 rounded bg-emerald-500" style="width:0%"></div></div>
            </div>
            ${body}
            <div class="flex items-center gap-3 pt-2">
              <button type="button" class="save-lit px-3 py-1.5 rounded border" data-status="draft">Salvare temporară</button>
              <button type="button" class="save-lit px-3 py-1.5 rounded text-white bg-emerald-600" data-status="final">Salvare permanentă</button>
            </div>
          </form>
        </div>`;
    }

    function visibleByCond(form, wrap){
      const field=wrap.dataset.condField; if(!field) return true;
      const allowed=(wrap.dataset.condValues||'').split('|').map(v=>v.trim().toUpperCase()).filter(Boolean);
      const ctrl=form.querySelector(`[name="${field}"]`);
      const val =(ctrl?.value||'').toUpperCase();
      const txt =(ctrl?.tagName==='SELECT')?(ctrl.selectedOptions[0]?.text||'').toUpperCase():'';
      const show=allowed.includes(val)||(txt&&allowed.includes(txt));
      wrap.style.display=show?'':'none';
      if(!show){ const inp=wrap.querySelector('input,select'); if(inp){ inp.value=(inp.tagName==='SELECT'?'':''); delete inp.dataset.touched; } }
      return show;
    }

    function updateRangesLive(form){
      form.querySelectorAll('.lit-range').forEach(r=>{
        const out=r.parentElement.querySelector('output'), upd=()=>{ out.value=r.value; };
        r.addEventListener('input', ()=>{ r.dataset.touched='1'; upd(); });
        r.addEventListener('change',()=>{ r.dataset.touched='1'; upd(); });
        upd();
      });
      form.querySelectorAll('select[name^="lit_q"]').forEach(s=>{
        s.addEventListener('change', ()=>{ if((s.value||'')!=='') s.dataset.touched='1'; else delete s.dataset.touched; });
      });
    }

    function isAnswered(w){
      if(w.style.display==='none') return false;
      const el=w.querySelector('input,select'); if(!el) return false;
      if(el.type==='range'||el.type==='number') return el.dataset.touched==='1';
      return ((el.value||'')!=='') && el.dataset.touched==='1';
    }

    function isRequiredNow(w, form){
      if(!w.dataset.required) return false;
      const field=w.dataset.condField; if(!field) return true;
      return visibleByCond(form,w);
    }

    function applyCondsAndProgress(form){
      const wraps=[...form.querySelectorAll('.question-wrapper')];
      wraps.forEach(w=>{ visibleByCond(form,w); });
      let total=0, ans=0;
      wraps.forEach(w=>{
        if(w.style.display==='none') return;
        const reqNow=w.dataset.required ? (visibleByCond(form,w)) : true;
        if(reqNow){ total++; if(isAnswered(w)) ans++; }
      });
      if(total===0){ total=wraps.filter(w=>w.style.display!=='none').length; ans=wraps.filter(isAnswered).length; }
      const pct=total?Math.round(100*ans/total):0;
      form.querySelector('#questionnaire-progress').textContent=`Completat: ${ans} din ${total} (${pct}%)`;
      form.querySelector('#progressBar').style.width=pct+'%';
    }

    function computeLitScore(schema, form){
      const qs=Array.isArray(schema?.questions)?schema.questions:[];
      const breakdown=[]; let sumRange=0, answered=0;
      qs.forEach((q)=>{
        const el=form.querySelector(`[name="${q.name}"]`), wrap=el?el.closest('.question-wrapper'):null;
        if(!wrap||!el||wrap.style.display==='none') return;
        const touched=el.dataset.touched==='1';
        if(q.type==='select'){
          const val=el.value;
          if(touched&&val!==''){ answered++; breakdown.push({name:q.name,label:q.label,value:val}); }
        }else{
          const v=(el.value===''?null:Number(el.value)), max=(typeof q.max==='number')?q.max:Number(el.getAttribute('max')||0);
          if(touched&&v!==null){ answered++; sumRange+=(isFinite(v)?v:0); breakdown.push({name:q.name,label:q.label,value:v,max:max}); }
        }
      });
      return { module:schema?.slug||'', source:schema?.source||'', breakdown, totals:{range_sum:sumRange, answered, total_questions:qs.length} };
    }

    function prefillFromSaved(schema, form, studentId){
      const modul=form.dataset.modul||litModul;
      return fetch(ajaxurl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:new URLSearchParams({action:'get_questionnaire', modul_type:'lit', modul, student_id:studentId})})
        .then(r=>r.json())
        .then(data=>{
          if(!data?.success || !data?.data?.results) return;
          const res=data.data.results;
          Object.keys(res).forEach(k=>{
            const el=form.querySelector(`[name="${k}"]`); if(!el) return;
            el.value=res[k]; el.dataset.touched='1';
            if(el.classList.contains('lit-range')){ const out=el.parentElement.querySelector('output'); if(out) out.value=el.value; }
          });
          applyCondsAndProgress(form);
        })
        .catch(()=>{});
    }

    function openLitForm(studentId, studentName){
      const overlay=getEl('overlay');
      const panel=getEl('questionnairePanel');
      const content=getEl('questionnaireContent');
      if(!overlay||!panel||!content){ alert('Panoul nu este disponibil încă. Reîncarcă lista de elevi.'); return; }

      overlay.classList.remove('hidden'); panel.classList.remove('translate-x-full', 'right-0'); panel.classList.add('right-4');
      content.innerHTML=renderLitForm(window.__LIT_SCHEMA, studentId, studentName);
      content.querySelector('#closeQuestionnaire')?.addEventListener('click', closePanel);

      const form=content.querySelector('#questionnaireForm'); if(!form) return;
      updateRangesLive(form);
      applyCondsAndProgress(form);
      prefillFromSaved(window.__LIT_SCHEMA, form, studentId); // PRE-POPULARE

      form.addEventListener('change', ()=>applyCondsAndProgress(form));
      form.addEventListener('input',  ()=>applyCondsAndProgress(form));

      content.querySelectorAll('.save-lit').forEach(btn=>{
        btn.addEventListener('click', function(){
          const status=this.dataset.status||'draft';
          if(status==='final'){
            let ok=true;
            form.querySelectorAll('.question-wrapper').forEach(w=>{
              if(w.style.display==='none') return;
              if(!isRequiredNow(w,form)) return;
              if(!isAnswered(w)){
                ok=false; w.classList.add('border','border-red-500');
                if(!w.querySelector('.error-message')){ const p=document.createElement('p'); p.className='mt-1 text-sm text-red-500 error-message'; p.textContent='⚠️ Răspuns necesar'; w.appendChild(p); }
              }else{ w.classList.remove('border','border-red-500'); w.querySelector('.error-message')?.remove(); }
            });
            if(!ok){ alert('Completeză toate câmpurile obligatorii.'); return; }
            if(!confirm('După salvare, datele nu mai pot fi modificate. Continui?')) return;
          }

          const fd=new FormData();
          fd.append('action','save_questionnaire_lit');
          fd.append('student_id', form.dataset.student||'');
          fd.append('class_id',  classId||'');
          fd.append('modul_type','lit');
          fd.append('modul',     form.dataset.modul||litModul);
          fd.append('status',    status);

          (window.__LIT_SCHEMA?.questions||[]).forEach(q=>{
            const el=form.querySelector(`[name="${q.name}"]`); if(el) fd.append(q.name, el.value);
          });

          const score=computeLitScore(window.__LIT_SCHEMA, form);
          fd.append('score_total', String(score.totals.range_sum));
          fd.append('score_breakdown', JSON.stringify(score.breakdown));
          fd.append('score_meta', JSON.stringify({ module:score.module, source:score.source, answered:score.totals.answered, total_questions:score.totals.total_questions }));
          fd.append('score', String(score.totals.range_sum));

          fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'})
            .then(r=>r.json())
            .then(data=>{
              if(!data?.success) throw new Error(data?.data||'Eroare la salvare.');
              alert('✔️ Chestionar LIT salvat ('+status+').');
              closePanel();

              // reîncarcă vizual pastilele de progres
              const table=list.querySelector('table');
              if(table){
                ensureLitColumn(table);
                [...table.querySelectorAll('.td-lit')].forEach(td=>{
                  const btn=td.querySelector('.btn-lit');
                  const pill=td.querySelector('.lit-pill');
                  const sid=btn?.dataset?.id || td.closest('tr')?.querySelector('input[name="student_id"]')?.value || null;
                  if(sid) fetchLitProgress(pill, sid);
                });
              }
            })
            .catch(err=>{ console.error('[LIT][save]',err); alert('Eroare la salvare LIT.'); });
        });
      });
    }
    window.__openLitForm=openLitForm;

    // Pornește după ce tabela elevilor există (fără MutationObserver)
    (function boot(){
      function waitForTable(){
        const table = list.querySelector('table');
        if (!table) { setTimeout(waitForTable, 150); return; }

        // patch inițial
        ensureLitColumn(table);

        // cât timp se mai adaugă rânduri, repornim patch-ul rar și apoi ne oprim
        const tbody = table.tBodies[0] || table.querySelector('tbody');
        if (!tbody) return;

        let lastCount = tbody.rows.length;
        let stableTicks = 0;
        const iv = setInterval(()=>{
          const now = tbody.rows.length;
          if (now !== lastCount) {
            lastCount = now;
            stableTicks = 0;
            ensureLitColumn(table);
          } else {
            stableTicks += 1;
            // după 3 „cicluri” stabile (~750ms), ne oprim
            if (stableTicks >= 3) clearInterval(iv);
          }
        }, 250);
      }
      waitForTable();
    })();


    // Toggle "Adaugă elevi" (nemodificat)
    (function(){
      const level=<?= json_encode($class->level) ?>;
      if(!["Prescolar","Primar Mic","Primar Mare"].includes(level)){ document.querySelectorAll("th.frecventa_th").forEach(el=>el.style.display="none"); }
      if(!["Primar Mic","Primar Mare","Gimnazial"].includes(level)){ document.querySelectorAll("th.bursa_th").forEach(el=>el.style.display="none"); }
      document.getElementById("toggleAddForm")?.addEventListener("click",function(){
        const form=document.getElementById("addStudentsWrapper");
        form.classList.toggle("hidden");
        if(!form.classList.contains("hidden")){
          document.getElementById("studentCount")?.dispatchEvent(new Event("change"));
          window.scrollTo({ top: form.offsetTop - 100, behavior: 'smooth' });
        }
      });
    })();
  });
})();
</script>
