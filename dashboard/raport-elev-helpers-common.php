<?php
/** Helpers comune pentru Raport Elev (SEL + LIT)
 *  Se recomandă require_once în raport-elev.php, înaintea celorlalte helper-e.
 */

/* ---------- Compat ---------- */
if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle){ return $needle===''?true:strpos($haystack,$needle)===0; }
}
if (!function_exists('is_serialized')) {
  function is_serialized($d){ if(!is_string($d))return false; $d=trim($d); if($d==='N;')return true; if(!preg_match('/^[adObis]:/',$d))return false; return @unserialize($d)!==false||$d==='b:0;'; }
}

/* ---------- Agregare / formatting ---------- */
if (!function_exists('avg_non_null')) {
  function avg_non_null($arr){ $s=0;$n=0; foreach($arr as $v){ if($v!==null && $v!==''){ $s+=floatval($v); $n++; } } return $n?($s/$n):null; }
}
if (!function_exists('row_order_key')) {
  function row_order_key($r){ if(!empty($r->updated_at))return 't:'.strtotime($r->updated_at); if(!empty($r->created_at))return 'c:'.strtotime($r->created_at); return 'i:'.intval($r->id??0); }
}
if (!function_exists('status_chip')) {
  function status_chip($s){ $s=strtolower(trim((string)$s)); if(!$s)return '<span class="inline-flex items-center rounded-full bg-gray-400 text-white px-2.5 py-0.5 text-xs font-medium">—</span>';
    $cls = $s==='final' ? 'bg-emerald-600 text-white' : ($s==='draft' ? 'bg-amber-500 text-white' : 'bg-gray-400 text-white');
    return '<span class="inline-flex items-center rounded-full '.$cls.' px-2.5 py-0.5 text-xs font-medium">'.strtoupper($s).'</span>';
  }
}
if (!function_exists('badge_score')) {
  function badge_score($v,$size='sm'){ if($v===null||$v==='')return '<span class="inline-flex items-center text-gray-700 bg-gray-200 rounded '.($size==='xl'?'px-5 py-1.5 text-2xl font-extrabold leading-8':($size==='md'?'px-2.5 py-1 text-base':'px-2 py-0.5 text-sm')).'">—</span>'; $v=floatval($v);
    $cls='bg-emerald-600 text-white'; if($v<3)$cls='bg-lime-600 text-white'; if($v<2.75)$cls='bg-lime-500 text-gray-900'; if($v<2.5)$cls='bg-yellow-300 text-gray-900'; if($v<2)$cls='bg-orange-400 text-white'; if($v<1.5)$cls='bg-red-600 text-white';
    $pad = $size==='xl' ? 'px-5 py-1.5 text-2xl font-extrabold leading-8' : ($size==='md'?'px-2.5 py-1 text-base':'px-2 py-0.5 text-sm');
    return '<span class="inline-flex items-center rounded '.$cls.' '.$pad.'">'.number_format($v,2).'</span>';
  }
}
if (!function_exists('delta_chip')) {
  function delta_chip($d,$size='xs'){ if($d===null)return '—'; $cls='bg-gray-100 text-gray-700'; if($d>0)$cls='bg-emerald-100 text-emerald-700'; if($d<0)$cls='bg-red-100 text-red-700';
    $pad = $size==='sm' ? 'px-2 py-0.5 text-sm' : 'px-1.5 py-0.5 text-xs';
    return '<span class="inline-flex items-center rounded '.$cls.' '.$pad.'">Δ '.(($d>0?'+':'').number_format($d,2)).'</span>';
  }
}
if (!function_exists('pct_badge')) {
  function pct_badge($p,$size='sm'){
    if($p===null) return '<span class="inline-flex items-center px-2 py-0.5 text-sm rounded bg-gray-200 text-gray-700">—</span>';
    $p = max(0,min(100, floatval($p)));
    $cls = 'bg-emerald-600 text-white';
    if($p<75) $cls='bg-lime-600 text-white';
    if($p<50) $cls='bg-yellow-300 text-slate-900';
    if($p<25) $cls='bg-orange-400 text-white';
    if($p<10) $cls='bg-red-600 text-white';
    $pad = $size==='xl' ? 'px-5 py-1.5 text-2xl font-extrabold leading-8' : ($size==='md'?'px-2.5 py-1 text-base':'px-2 py-0.5 text-sm');
    return '<span class="inline-flex items-center rounded '.$cls.' '.$pad.'">'.number_format($p,0).'%</span>';
  }
}

/* ---------- Normalizări “clasă” & niveluri LIT ---------- */
if (!function_exists('edus_grade_number_from_classlabel')) {
  /** Returnează: -1 preșcolar, 0 pregătitoare, 1..8 clasele. Fallback 0. */
  function edus_grade_number_from_classlabel($name){
    $s=mb_strtolower(trim($name??'')); $s=str_replace(['â','ă','î','ș','ţ','ț'],['a','a','i','s','t','t'],$s);
    if(strpos($s,'prescolar')!==false || strpos($s,'preșcolar')!==false || strpos($s,'gradinita')!==false) return -1;
    if(strpos($s,'pregatitoare')!==false || strpos($s,'preparator')!==false) return 0;
    if(preg_match('/\b([0-9])\b/',$s,$m)){ $n=intval($m[1]); if($n>=1 && $n<=8) return $n; }
    return 0;
  }
}
if (!function_exists('edus_level_string_to_num')) {
  /** Mapare PP -> -1, P -> 0, “1”..“4” -> 1..4, altfel null */
  function edus_level_string_to_num($v){
    $v=trim((string)$v);
    if($v==='') return null;
    $u=strtoupper($v);
    if($u==='PP') return -1;
    if($u==='P')  return 0;
    if(is_numeric($v)) return intval($v);
    return null;
  }
}
