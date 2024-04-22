<?php

//===========================================================================================
function getIDFromURL($tableName, $directory) {
	global $db;
	$res = $db->getData("select __id from `" . mysql_real_escape_string($tableName) . "` where DirectoryName='" . mysql_real_escape_string($directory) . "'");
	if (count($res) < 1) {
		return false;
	} else return $res[0]['__id'];
}

//===========================================================================================
function getVirtualURL($tableName, $id, $prefix = "") {
	global $db;
	$res = $db->getData("select DirectoryName from `" . mysql_real_escape_string($tableName) . "` where __id='" . intval($id) . "'");
	if ($res[0]['DirectoryName'] == "") return $prefix . $id;
	else return $res[0]['DirectoryName'];
}

//===============================================================
function getBrandVirtualURL($id) {
	global $db;

	$res = $db->getData("SELECT DirectoryName FROM `Brands` WHERE __id = '" . intval($id) . "'");

	if (!$res[0]['DirectoryName']) {
		return "brand-" . $id;
	} else {
		return "brand-" . $res[0]['DirectoryName'];
	}
}

function getBrandIDfromVirtualURL($url) {
	global $db;

	$res = $db->getData("SELECT __id FROM `Brands` WHERE DirectoryName = '" . mysql_real_escape_string($url) . "'");

	if ($res) {
		return $res[0]['__id'];
	}
}

//===========================================================================================
function processLink($what) {
	global $special_locations;
	$text = $what[0];
	$location = urldecode($what[1]);
	$target = "";
	$rel = "";
	$noindex1 = "";
	$noindex2 = "";

	$location = str_replace("EMAIL:mailto:", "EMAIL:", $location);

	if (strstr($location, "URL:")) { // get URL
		$loc = trim(str_replace("URL:", "", str_replace("&grid;", "#", $location)));
		if (!strstr($loc, "javascript:")) {

			if (!strstr($loc, "tel:") && !strstr($loc, "skype:") && !strstr($loc, "tel:") && !strstr($loc, "://"))
				$loc = "http://" . $loc;

			if (!strstr($loc, $_SERVER['HTTP_HOST']) && !strstr($loc, "tel:") && !strstr($loc, "skype:") && !strstr($loc, "tel:")) {
				$noindex1 = "<noindex>";
				$noindex2 = "</noindex>";
				$target = ' target="_blank" ';
				$rel = " rel=\"nofollow\" ";
			}
		}
		$loc = str_replace("&amp;grid;", "#", $loc);
		$loc = str_replace("&grid;", "#", $loc);
	} else if (strstr(" " . $location, "EMAIL:")) { // get email

		$email = str_replace("EMAIL:", "", $location);
		if (strstr($text, "@")) $text = encode_everything(trim($text));
		$loc = encode_everything("mailto:" . $email);
	} else // get site page
		$loc = getRealLinkURL($location);

	$res = $noindex1 . "<a" . $target . $rel . " href=\"" . $loc . "\">" . $text . "</a>" . $noindex2;
	return $res;
}

//===========================================================================================
function getRealLinkURL($id) {
	global $dir_prefix, $is_subscribe, $admin_baseURL;
	$site_root = "";

	$location = explode(":", $id);
	if (count($location) > 2) {
		$location[0] = $location[1];
		$location[1] = $location[2];
	}

	if (count($location) < 2) {
		$res = dir_prefix . $site_root;
	} else {
		$module = $location[0];
		$id = $location[1];

		switch ($module) {
			case "news":
				$res = $dir_prefix . site_root . "news/" . getVirtualURL("News", $id);
				break;

			case "cid":
			case "cat":
				$res = $dir_prefix . site_root . "o-nas/doctors/" . getVirtualURL("CatCategories", $id, "cat");
				break;

			case "catitems":
			case "pid":
				global $db;
				$item_parent = $db->getData("select Category from CatItems where __id = '".intval($id)."'");
				if(getTopCatParent($item_parent[0]['Category'])=="300")
					$res = $dir_prefix . "" . site_root . "o-nas/management/" . getVirtualURL("CatItems", $id, "doctor").'/';
				else
					$res = $dir_prefix . "" . site_root . "o-nas/doctors/" . getVirtualURL("CatItems", $id, "doctor").'/'; 
				break;

			case "brand":
				$res = $dir_prefix . site_root . "cat/" . getBrandVirtualURL($id);
				break;

			default:
				$url = getNearestPageHref($id);

				if ($id == page_index) $url = "";

				if ($is_subscribe) {
					$res = $admin_baseURL . "/" . $url;
				} else if (!strstr(" " . strtolower($url), "http://") && !strstr(" " . strtolower($url), "javascript:")) {
					$res = $dir_prefix . $url;
				} else {
					$res = $url;
				}
		}
	}

	$res = str_replace("http:||", "http://", str_replace("//", "/", str_replace("http://", "http:||", $res)));

	return $res;
}

//===============================================================
function getNearestPageHref($id, $additional = "", $pid = -1, $level = 0) {
	global $db;

	if (!is_numeric($id)) return false;

	$opid = $pid;

	if ($pid == 0) {
		$res = "";
		return $res;
	}

	if ($pid < 0) $pid = $id;

	$pg = $db->getData("select __id, Parent, DirectoryName, PageURL from Pages where __id='" . intval($pid) . "'", true);

	if ($pg[0]['PageURL'] != "") $res = $pg[0]['PageURL'];
	else {
		if ($pg[0]['DirectoryName'] != "") {
			$res = getNearestPageHref($id, $additional, $pg[0]['Parent'], $level + 1) . $pg[0]['DirectoryName'] . "/";
			if ($opid == -1) {
				if ($additional != "") $res .= $additional;
				else $res .= "";
			}
		} else {
			if ($opid == -1)
				$res = getNearestPageHref($id, $additional, $pg[0]['Parent'], $level + 1) . "?id=" . $id;
			else
				$res = getNearestPageHref($id, $additional, $pg[0]['Parent'], $level + 1);
		}
	}

	return $res;
}
