<?php

//===============================================================
function encode_everything($string) {
	$encoded = "";
	for ($n = 0; $n < strlen($string); $n++) {
		$check = htmlentities($string[$n], ENT_QUOTES);
		$string[$n] == $check ? $encoded .= "&#" . ord($string[$n]) . ";" : $encoded .= $check;
	}
	return $encoded;
}

//===============================================================
/**
 * @param $element
 * @param $type
 * @return mixed|string|void
 */
function processElement($element, $type) {
	global $specialStyles;

	switch ($type) {

		case "news":
			// $specialStyles = false;
			$res = processNews($element);
			break;

		case "employee":
			$res = processEmployee($element);
			break;

		case "form":
			$res = displayForm($element);
			break;

		case "link":
			$res = processLink($element);
			break;

		case "sublinks":
			$res = processSubLinks($element);
			break;

		case "pagesGrid":
			$res = displayPagesGrid($element[0]);
			break;

		case "appointmentActions":
			$res = processAppointmentActions($element);
			break;

		case "pic":
			$res = processPic($element);
			break;

		case "video":
			$res = processVideo($element);
			break;

		case "pcat":
			$res = processPicCategory($element);
			break;
		
		case "doctors":			
			$res = processCatalogue($element);
			break;	

	}
	return $res;
}

//===============================================================
function replaceSpecial($what, $type) {
	do {
		$found = false;
		$pos = strpos($what, "<#" . $type);
		if ($pos > 0 || $pos === 0) {
			$found    = true;
			$str1     = substr($what, $pos);
			$str1     = substr($str1, 0, strpos($str1, "#>") + 2);
			$str2     = str_replace("<#" . $type . "#", "", $str1);
			$str2     = str_replace("#>", "", $str2);
			$command  = explode("#", $str2);

			$res = processElement($command, $type);

			$what = str_replace($str1, $res, $what);
		}
	} while ($found);
	return $what;
}

//===============================================================
function processText($what) {
	global $separator, $debug;
	$n = "\n";
	$r = "\r";

	$pattern = '/<iframe[^>]*>*<\/iframe>/iu';

	$count_matches_of_video = preg_match_all($pattern, $what, $matches, PREG_SET_ORDER);

	if ($count_matches_of_video > 0) {
		for ($i = 0; $i < $count_matches_of_video; $i++) {
			$m = $matches[$i][0];
			$what = str_replace($m, '<div class="video-container">' . $m . '</div>', $what);
		}
	}

	$what = str_replace($r, "", $what);
	$what = str_replace("<#sep#>", $separator, $what);

	$what = replaceSpecial($what, 'pic');
	$what = replaceSpecial($what, 'video');
	$what = replaceSpecial($what, 'cat');
	$what = replaceSpecial($what, 'pcat');
	$what = replaceSpecial($what, 'doctors');
	$what = replaceSpecial($what, 'link');
	$what = replaceSpecial($what, 'news');
	$what = replaceSpecial($what, 'pagesGrid');
	$what = replaceSpecial($what, 'employee');
	$what = replaceSpecial($what, 'sublinks');
	$what = replaceSpecial($what, 'appointmentActions');
	$what = replaceSpecial($what, 'shops');
	$what = replaceSpecial($what, 'benefits');
	$what = replaceSpecial($what, 'form');

	$what = preg_replace("/<p[^>]*><\/p>/iu", "", $what);
	$what = preg_replace("/<p[^>]*> <\/p>/iu", "", $what);

	$what = str_replace("class=\"\"", "", $what);
	$what = str_replace("class=\"mceVisualAid\"", "", $what);
	$what = str_replace("class=mceVisualAid", "", $what);
	$what = str_replace(" mceVisualAid", "", $what);
	$what = str_replace("mceVisualAid", "", $what);
	$what = preg_replace("/<table([^>]*) border=1([^>]*)>/iu", "<table\\1 class=\"tbl\"\\2>", $what);
	$what = preg_replace("/<table([^>]*) border=\"1\"([^>]*)>/iu", "<table\\1 class=\"tbl\"\\2>", $what);
	$what = str_replace("<table", "<div class=\"table-wrapper\"><table", str_replace("</table>", "</table></div>", $what));

	return $what;
}