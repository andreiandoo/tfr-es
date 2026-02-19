<?php
/** Helpers & calcule pentru SEL.
 *  Depinde de: $wpdb, $student, $rowsRaw, $tbl_students, $tbl_results, $tbl_generations
 *  Necesită: raport-elev-helpers-common.php deja încărcat.
 */

/* ---------- Config SEL ---------- */
$SEL_CHAPTERS = ['Conștientizare de sine','Autoreglare','Conștientizare socială','Relaționare','Luarea deciziilor'];
function sel_stage_from_modul($m){ $m=strtolower(trim($m??'')); if(strpos($m,'-t0')!==false)return't0'; if(strpos($m,'-ti')!==false)return'ti'; if(strpos($m,'-t1')!==false)return't1'; return null; }
function parse_sel_score_map_any($raw){ if(is_array($raw))return$raw; if(is_serialized($raw)){ $a=@unserialize($raw); if(is_array($a))return$a; } return []; }

/* ---------- Split doar SEL ---------- */
$rowsSEL = array_values(array_filter($rowsRaw ?? [], fn($r)=> strtolower(trim($r->modul_type??''))==='sel' || str_starts_with(strtolower(trim($r->modul??'')),'sel-')));

/* ---------- Latest pe etape (student) ---------- */
$latestSEL=['t0'=>null,'ti'=>null,'t1'=>null]; $latestKey=['t0'=>null,'ti'=>null,'t1'=>null];
foreach($rowsSEL as $r){ $st=sel_stage_from_modul($r->modul); if(!$st)continue; $k=row_order_key($r); if($latestKey[$st]===null||$k>$latestKey[$st]){ $latestKey[$st]=$k; $latestSEL[$st]=$r; } }

$map_t0=$map_ti=$map_t1=array_fill_keys($SEL_CHAPTERS,null);
if($latestSEL['t0']){ $raw=parse_sel_score_map_any(maybe_unserialize($latestSEL['t0']->score)); foreach($SEL_CHAPTERS as $c) $map_t0[$c]=(isset($raw[$c])&&is_numeric($raw[$c]))?floatval($raw[$c]):null; }
if($latestSEL['ti']){ $raw=parse_sel_score_map_any(maybe_unserialize($latestSEL['ti']->score)); foreach($SEL_CHAPTERS as $c) $map_ti[$c]=(isset($raw[$c])&&is_numeric($raw[$c]))?floatval($raw[$c]):null; }
if($latestSEL['t1']){ $raw=parse_sel_score_map_any(maybe_unserialize($latestSEL['t1']->score)); foreach($SEL_CHAPTERS as $c) $map_t1[$c]=(isset($raw[$c])&&is_numeric($raw[$c]))?floatval($raw[$c]):null; }

$completion = [
  't0' => $latestSEL['t0']?intval($latestSEL['t0']->completion):null,
  'ti' => $latestSEL['ti']?intval($latestSEL['ti']->completion):null,
  't1' => $latestSEL['t1']?intval($latestSEL['t1']->completion):null,
];
$status = [
  't0' => $latestSEL['t0']->status ?? null,
  'ti' => $latestSEL['ti']->status ?? null,
  't1' => $latestSEL['t1']->status ?? null,
];

/* ---------- Totaluri student ---------- */
$sel_total_t0 = avg_non_null($map_t0);
$sel_total_ti = avg_non_null($map_ti);
$sel_total_t1 = avg_non_null($map_t1);
$sel_total_overall = avg_non_null(array_filter([$sel_total_t0,$sel_total_ti,$sel_total_t1], fn($v)=>$v!==null));

$delta_total_ti_t0 = ($sel_total_ti!==null && $sel_total_t0!==null) ? $sel_total_ti-$sel_total_t0 : null;
$delta_total_t1_ti = ($sel_total_t1!==null && $sel_total_ti!==null) ? $sel_total_t1-$sel_total_ti : null;
$delta_total_t1_t0 = ($sel_total_t1!==null && $sel_total_t0!==null) ? $sel_total_t1-$sel_total_t0 : null;

/* ---------- Δ-uri pe capitole ---------- */
$delta_ti_t0=$delta_t1_ti=$delta_t1_t0=[];
foreach($SEL_CHAPTERS as $cap){ $t0=$map_t0[$cap]; $ti=$map_ti[$cap]; $t1=$map_t1[$cap];
  $delta_ti_t0[$cap]=($ti!==null&&$t0!==null)?$ti-$t0:null;
  $delta_t1_ti[$cap]=($t1!==null&&$ti!==null)?$t1-$ti:null;
  $delta_t1_t0[$cap]=($t1!==null&&$t0!==null)?$t1-$t0:null;
}

/* ---------- Medii generație ---------- */
$gen_t0_sum = array_fill_keys($SEL_CHAPTERS, 0.0); $gen_t0_cnt = array_fill_keys($SEL_CHAPTERS, 0);
$gen_ti_sum = array_fill_keys($SEL_CHAPTERS, 0.0); $gen_ti_cnt = array_fill_keys($SEL_CHAPTERS, 0);
$gen_t1_sum = array_fill_keys($SEL_CHAPTERS, 0.0); $gen_t1_cnt = array_fill_keys($SEL_CHAPTERS, 0);
$gen_all_sum= array_fill_keys($SEL_CHAPTERS, 0.0); $gen_all_cnt= array_fill_keys($SEL_CHAPTERS, 0);

if(!empty($student->generation_id)){
  $peer_ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$tbl_students} WHERE generation_id=%d", $student->generation_id));
  if($peer_ids){
    $peer_ids = array_map('intval',$peer_ids);
    $in = implode(',', array_fill(0, count($peer_ids), '%d'));
    $rowsGen = $wpdb->get_results($wpdb->prepare("SELECT modul, score FROM {$tbl_results} WHERE (LOWER(modul_type)='sel' OR LOWER(modul) LIKE 'sel-%') AND student_id IN ($in)", ...$peer_ids));
    foreach($rowsGen as $gr){
      $stage = sel_stage_from_modul($gr->modul);
      $map = parse_sel_score_map_any(maybe_unserialize($gr->score));
      foreach($SEL_CHAPTERS as $c){
        if(isset($map[$c]) && is_numeric($map[$c])){
          $v=floatval($map[$c]);
          $gen_all_sum[$c]+=$v; $gen_all_cnt[$c]+=1;
          if($stage==='t0'){ $gen_t0_sum[$c]+=$v; $gen_t0_cnt[$c]+=1; }
          elseif($stage==='ti'){ $gen_ti_sum[$c]+=$v; $gen_ti_cnt[$c]+=1; }
          elseif($stage==='t1'){ $gen_t1_sum[$c]+=$v; $gen_t1_cnt[$c]+=1; }
        }
      }
    }
  }
}
$gen_t0_avg=$gen_ti_avg=$gen_t1_avg=$gen_all_avg=array_fill_keys($SEL_CHAPTERS,null);
foreach($SEL_CHAPTERS as $c){
  if($gen_t0_cnt[$c]>0)$gen_t0_avg[$c]=$gen_t0_sum[$c]/$gen_t0_cnt[$c];
  if($gen_ti_cnt[$c]>0)$gen_ti_avg[$c]=$gen_ti_sum[$c]/$gen_ti_cnt[$c];
  if($gen_t1_cnt[$c]>0)$gen_t1_avg[$c]=$gen_t1_sum[$c]/$gen_t1_cnt[$c];
  if($gen_all_cnt[$c]>0)$gen_all_avg[$c]=$gen_all_sum[$c]/$gen_all_cnt[$c];
}

/* ---------- Per capitol + footere ---------- */
$stud_cap_avg = [];
foreach($SEL_CHAPTERS as $c){ $stud_cap_avg[$c] = avg_non_null([$map_t0[$c],$map_ti[$c],$map_t1[$c]]); }

$footer_stud_t0 = $sel_total_t0;  $footer_gen_t0  = avg_non_null($gen_t0_avg);
$footer_stud_ti = $sel_total_ti;  $footer_gen_ti  = avg_non_null($gen_ti_avg);
$footer_stud_t1 = $sel_total_t1;  $footer_gen_t1  = avg_non_null($gen_t1_avg);
$footer_delta_t0 = ($footer_stud_t0!==null && $footer_gen_t0!==null) ? $footer_stud_t0-$footer_gen_t0 : null;
$footer_delta_ti = ($footer_stud_ti!==null && $footer_gen_ti!==null) ? $footer_stud_ti-$footer_gen_ti : null;
$footer_delta_t1 = ($footer_stud_t1!==null && $footer_gen_t1!==null) ? $footer_stud_t1-$footer_gen_t1 : null;

$footer_stud_avg_cap = avg_non_null($stud_cap_avg);
$footer_gen_avg_cap  = avg_non_null($gen_all_avg);

/* ===== Variabile consumate în raport-elev-tab-sel.php ===== */
