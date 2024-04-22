<?php

//=======================================================================
function headerNotFound() {
	header("HTTP/1.1 404 Not Found");
}

//=======================================================================
function format_price($what, $currency = ""){
  $price = number_format($what, 2, '.', ' ');
  $price = str_replace(".00", "", $price);
	if ($currency != "")
		$price .= " " . $currency;
  return $price;
}

//=======================================================================
function my_setcookie($name, $value, $time="", $path = "/", $domain = "") {
	global $HTTP_HOST;

	if($domain=="") $domain = $HTTP_HOST;
	if($time=="") $time = time()+30*24*3600;
	$name = trim($name);
	setcookie($name, $value, $time, $path, str_replace("www.", "", strtolower($domain)));
	setcookie($name, $value, $time, $path, "www.".str_replace("www.", "", strtolower($domain)));
}

//=======================================================================
function strip_slashes_all(){
	foreach($_POST as $key=>$value){
		global $$key;
		$$key = stripslashes($value);
	}
	foreach($_COOKIE as $key=>$value){
		global $$key;
		$$key = stripslashes($value);
	}
}

//=======================================================================
function hashPswd($pswd = '', $salt = '') { 
	return crypt($pswd, '$2y$11$' . sha1(md5("IdER93j") . sha1($salt)) . '$');
}

//=======================================================================
function generatePass($length = 15, $nosymbols = false){
	if(!$nosymbols)
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789)(*&^%$#@!;";
	else
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		
	$string = "";
	
	for ($i=0; $i<$length; $i++) {
		$pos = rand(0, strlen($chars)-1);
		$string.=substr($chars, $pos, 1);
	}	
	return $string;
}

//=======================================================================
function my_nl2br($what){
	$n = "\n";
	$r = "\r";
	$what = str_replace($r, "", $what);
	$what = str_replace($n.$n, $n, trim($what));
	$what = nl2br($what);
	return $what;
}

//=======================================================================
function processQuotes($name){
	return str_replace("\"", "&quot;", $name);
}

//=======================================================================
function processDate($what, $template = 0){
	global $monthArr, $monthArr_short, $monthArr;

	$dt = getdate(MySQLtimestamp2unix($what));

	if($template==0) {
		$res['dd'] = $dt['mday'];
		$res['mm'] = $dt['mon'];
		$res['yy'] = $dt['year'];

		$res['hh'] = $dt['hours'];
		$res['min'] = $dt['minutes'];
		$res['ss'] = $dt['seconds'];

		$res['mmChar'] = $monthArr[$dt['mon']-1];
		$res['mmCharShort'] = $monthArr_short[$dt['mon']-1];

		// $res = date("d.m.Y", MySQLtimestamp2unix($what));
		
	} else {
		$res = date("d ", MySQLtimestamp2unix($what))."<span>".$monthArr[$dt['mon']-1]."</span> ".$dt['year'];
	}
	return $res;
}

//===============================================================
// $titles = array('яблоко', 'яблока', 'яблок')
function sufixFromNumber($n, $titles) {
  $cases = array(2, 0, 1, 1, 1, 2);
  return $titles[($n % 100 > 4 && $n % 100 < 20) ? 2 : $cases[min($n % 10, 5)]];
}

//===============================================================
function phoneToLink($tel) {
	$tel = preg_replace('/[^0-9+]/', '', $tel);
	return $tel;
}

//===============================================================
function twoDigits($num) {
	if ($num < 10) {
		return ('0' . $num);
	} else {
		return $num;
	}
}

//===============================================================
function selectorListFromArray($selectsArray, $active = "") {

	foreach ($selectsArray as $i => $select) {
		unset($s);
		$s['no'] = $i;
		$s['name'] = trim($select);
		if ($active !== "") {
			if (trim($select) === trim($active)) {
				$s['selected'] = 'selected';
			}
			if (is_numeric($active) && $i == $active) {
				$s['selected'] = 'selected';
			}
		}
		$ss[] = $s;
	}

	return $ss;
}

//===============================================================
function radioListFromArray($selectsArray, $active = "") {
	foreach ($selectsArray as $i => $select) {
		unset($s);
		$s['no'] = $i;
		$s['name'] = $select;
		if ($active == "") {
			if ($i == 0) {
				$s['checked'] = 'checked';
			}
		} else {
			if ($i == $active) {
				$s['checked'] = 'checked';
			}
		}
		$ss[] = $s;
	}
	return $ss;
}