<?php

unset($attached_images);

function getImageRaw($small, $big, $Link, $desc, $descText, $align, $alt, $href, $realBigURL, $bigExt = "", $is_admin = false, $alt_display = 1, $alt_text = "", $frame = 1, $force_text_link = false) {

	if ($alt_text == "")
		$alt_text = $alt;

	global $dir_prefix, $is_subscribe, $attached_images, $admin_baseURL;
	global $level, $webis_href;

	$alt = htmlspecialchars($alt);

	$url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

	if (strstr($url, "home.php")) {
		$url = substr($url, 0, strlen($url) - 14);
	} else {
		for ($i = 0; $i <= $level; $i++)
			$url = substr($url, 0, strrpos($url, "/"));
	}
	$url = "http://" . str_replace("//", "/", $url . "/");

	$fileinfo = utf8_pathinfo($small);
	$imgType  = 0;

	$altText  = " title=\"" . processQuotes($alt) . "\"";

	if (!$is_admin || $is_subscribe)
		$webis_pic = "";
	else
		$webis_pic = " data-webis_pic=\"" . $webis_href . "\"";



	switch (strtolower($fileinfo['extension'])) {
		case "swf":
			list($width, $height, $type, $attr) = getimagesize($small);
			if ($is_subscribe)
				$small = $admin_baseURL . $small;
			$imgC = "<object " . $webis_pic . " height=\"" . $height . "\" width=\"" . $width . "\" classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\"><param name=\"wmode\" value=\"opaque\"><param name=\"scale\" value=\"exactfit\" /><param name=\"movie\" value=\"" . $small . "\" /><param name=\"quality\" value=\"high\" /><embed src=\"" . $small . "\" quality=\"high\" scale=\"exactfit\" height=\"" . $height . "\" width=\"" . $width . "\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" wmode=\"opaque\" /></embed></object>";
			break;

		case "gif":
		case "jpg":
		case "jpeg":
		case "png":

			if ($is_subscribe) {
				$attached_images[] = $small;
				$small  = "cid:" . substr($small, strrpos($small, "/") + 1, strlen($small));
			}
			$imgC     = "<img " . $webis_pic . " src=\"" . $small . "\"" . $altText . " " . $style . ">";
			$imgType  = 1;
			break;

		default:
			$imgC             = $alt_text;
			$force_text_link  = true;
			break;
	}

	if ($realBigURL == "")
		$realBigURL = $big;

	$oa = $align;
	$od = $desc;

	if ($Link != "" && !$is_admin && !$force_text_link) {
		if (strpos($Link, 'EMAIL') !== false)
			$Link = encode_everything("mailto:" . str_replace("EMAIL:", "", $Link));
		else {
			if (strpos($Link, 'URL') !== false)
				$target_blank = ' target="_blank" ';
			else
				$target_blank = '';

			$Link = getRealLinkURL($Link);
		}

		$imgC   = '<a href="' . $Link . '"' . $target_blank . '>' . $imgC . '</a>';
	} else if (trim($realBigURL) != $dir_prefix && trim($realBigURL) != $url && !$is_subscribe) {
		if ($bigExt == "") {
			$fi     = utf8_pathinfo($realBigURL);
			$bigExt = $fi['extension'];
		}
		$oImgC    = $imgC;
		switch (strtolower($bigExt)) {
			case "swf":
				list($width, $height, $type, $attr) = getimagesize($realBigURL);
				if (!$is_admin) {
					$imgC = "<a " . $webis_pic . "  onClick=\"window.open('" . $big . "', '', 'width=" . $width . " height=" . $height . " resizable=yes');\">" . $imgC . "</a>";
				}
				break;

			case "gif":
			case "png":
			case "jpg":
			case "jpeg":
				if (!$is_admin) {
					if (!$force_text_link)
						$imgC = "<figure data-src=\"" . $realBigURL . "\" lg-background-color=\"white\">" . $imgC . "</figure>";
					else {
						if ($alt_display == 0)
							$imgC = "<a " . $altText . " href=\"" . $realBigURL . "\" " . $webis_pic . " >" . $imgC . "</a>";
						else
							$imgC = "<a " . $altText . " href=\"" . $realBigURL . "\"  " . $webis_pic . " >" . $imgC . "</a>";
					}
					$force_text_link = false;
				} else if ($force_text_link)
					$imgC = "<a " . $webis_pic . " data-role='pic' href=\"" . $big . "\">" . $imgC . "</a>";
				break;

			default:
				if (!$is_admin)
					$imgC = "<a " . $altText . " " . $webis_pic . " target=\"_blank\" href=\"" . $big . "\">" . $imgC . "</a>";
				break;
		}
	}


	if ($force_text_link) {
		if ($big == "" && $small != "")
			$big = $small;
		$imgC = "<a " . $webis_pic . " href=\"" . $big . "\">" . $alt_text . "</a>";
	}


	if ($imgType != 0 && !$is_admin) { // place image into wrapper with description
		if ($align != "0") {
			if ($align == "l")
				$align = " left";
			else if ($align == "r")
				$align = " right";
			else if ($align == "c")
				$align = " center";
		} else
			$align = "";

		$imageCell = "<div class=\"inline-image\" style=\"float:".$align."\">" . $imgC;

		if ($desc != "0" && $desc != "") {
			$hd = " hasdesc";
			$imageCell .= "<div class=\"image-description ";
			if ($desc == "t")
				$imageCell .= "top";
			if ($desc == "b")
				$imageCell .= "bottom";
			if ($desc == "l")
				$imageCell .= "left";
			if ($desc == "r")
				$imageCell .= "right";
			$imageCell .= "\">" . $descText . "</div>";
		} else
			$hd = "";

		$imageCell = str_replace("#hasdesc#", $hd, $imageCell);
		$imageCell .= "</div>";

		if ($oa != "0" || $od != "0") {
			$str = $imageCell;
		} else
			$str = $imgC;

	} else if ($is_admin) {
		if ($align != "0") {
			if ($align == "l")
				$align = " align=\"left\"";
			else if ($align == "r")
				$align = " align=\"right\"";
			else if ($align == "c")
				$align = " style=\"display: block; margin: 0 auto\"";
		} else
			$align = "";

		$align .= " data-role='pic' ";

		$str = preg_replace("/<img/iu", "<img " . $align, $imgC);
	} else
		$str = $imgC;

	return $str;
}


function getImage($pic, $desc, $align, $alt, $href, $is_admin, $alt_display, $alt_text, $frame = "1", $force_txt_link) {
	global $db, $dir_prefix;

	if ($dir_prefix == "")
		$dp = dir_prefix;
	else
		$dp  = $dir_prefix;
	$img = $db->getData("select __id, Name, Description, Link, smallURL, bigURL from Images where __id='" . intval($pic) . "'");
	$img    = $img[0];
	$img['Description'] = nl2br($img['Description']);
	$small  = $dp . $img['smallURL'];
	$bu     = $dp . $img['bigURL'];

	// die("ext($pic)=");

	if ($bu != $dp) {
		$fileinfo = utf8_pathinfo($dp . $img['bigURL']);
		$ext = strtolower($fileinfo['extension']);
		switch ($ext) {
			case "jpg":
			case "gif":
			case "jpeg":
			case "png":
				$big = $dp . $img['bigURL'];
				break;

			case "swf":
				$big = $dp . "flash/?id=" . $pic;
				break;

			default:
				$big = $dp . $img['bigURL'];
				break;
		}
	} else $ext = "";

	if ($alt == "") $alt = $img['Name'];

	$str = getImageRaw($small, $big, $img['Link'], $desc, $img['Description'], $align, $alt, $href, $bu, $ext, $is_admin, $alt_display, $alt_text, $frame, $force_txt_link);

	return $str;
}

//===============================================================

function processPic($what, $is_admin = false) {
	global $webis_href;
	global $imd;

	$pic          = str_replace("#", "", $what[0]);
	$desc         = str_replace("#", "", $what[1]);
	$align        = str_replace("#", "", $what[2]);
	$alt_display  = str_replace("#", "", $what[3]);
	$alt_text     = str_replace("#", "", $what[4]);
	$frame        = str_replace("#", "", $what[5]);
	if ($frame == "") $frame = "1";

	//<#pic#\\1#\\2#\\3#\\4#\\5#>

	$webis_href   = join("#", $what);
	$res          = getImage($pic, $desc, $align, $image_alt, $image_href, $is_admin, $alt_display, $alt_text, $frame);
	return $res;
}

//===============================================================
function processVideo($element) {
	global $db;
	$res['frame'] = getSystemVariable($db, $element[0]);
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/video.htm"), $res);
}