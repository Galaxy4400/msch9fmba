<?php

function getKeywords($where){
  $where = strip_tags(str_replace(">", "> ", $where));
  $where = eregi_replace("&[^ ;]*;", "", $where);
  unset($words);
  $word = "";
  for ($i=0; $i<strlen($where); $i++) {
    $c = substr($where, $i, 1);
    if((ord($c)>=ord('a')&&ord($c)<=ord('z'))||(ord($c)>=ord('A')&&ord($c)<=ord('Z'))||(ord($c)>=ord('а')&&ord($c)<=ord('я'))||(ord($c)>=ord('А')&&ord($c)<=ord('Я'))) $word.=$c;
    else {
      if(strlen($word)>=3) {
      	$no = $words[$word];
      	if($no=="") {
      	  $no = 0;
      	  $words[$word] = "0";
      	}
        $words[$word] = $no+1;
      }
      $word = "";
    }
  }
  arsort($words);
  global $kd;
  if($kd==1) print_r($words);
  unset($ws);
  $cnt = 0;
  foreach($words as $key=>$value) {
    if($value>1) $ws[] = $key;
    $cnt++;
    if($cnt>100) break;
  }
  return join(" ", $ws);
}