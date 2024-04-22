<?php

$needBreadcrumbs = true;
$needBreadcrumbsCurrent = true;
$altBreadcrumbs = "";

//===============================================================
function displayLogo() {
	global $db, $dir_prefix, $is_index;

	$logo = $db->getData("select * from Images where __id = 1909")[0];

	if (!$logo) return;

	$res['img'] = $dir_prefix . $logo['bigURL'];
	$res['imgBlind'] = $dir_prefix . $logo['smallURL'];
	$res['name'] = $logo['Name'];
	$res['desc'] = $logo['Description'];
	if (!$is_index) {
		$res['href'] = 'href="'.$dir_prefix.'"';
	}

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/logo.htm"), $res);
}

//===============================================================
function getPage($id, $what = "") {
	global $db, $dir_prefix, $MetaTitle, $MetaKeywords, $MetaDescription, $altHeader, $specialStyles, $hasAside, $doctorID;

	$noPageAside = [26, 29, 159, 160];
	
	$res = $db->getData("select * from Pages where __id='" . intval($id) . "'", true);

	unset($page);
	$url = trim($res[0]['PageURL']);
	if ($url != "") {
		header("Location: " . $url);
		die();
	}

	//---------------------------------------------------------------
	$page["id"] = $res[0]['__id'];
	$page["Header"] = processText($res[0]['Header']);
	$page["Abstract"] = processText(nl2br($res[0]['Abstract']));
	$page['altHeader'] = $res[0]['altHeader'];
	$page['MetaTitle'] = $res[0]['MetaTitle'];
	$page['MetaKeywords'] = $res[0]['MetaKeywords'];
	$page['MetaDescription'] = $res[0]['MetaDescription'];
	$page["Body"] = processText($res[0]['Body']);
	$page["Body2"] = processText($res[0]['Body2']);
	$page["PictureURL"] = $dir_prefix . $res[0]['Picture'];
	$page["Parent"] = $res[0]['Parent'];
	$page['ParentLink'] = $dir_prefix . getRealLinkURL("pages:" . $res[0]['Parent']);
	$page['breadcrumbs'] = getBreadcrumbs($res[0]['__id']);

	if (!$doctorID) {
		$hasAside = false;
	}
	if(!in_array($res[0]['__id'], $noPageAside) || $hasAside) {
		if ($doctorID) {
			$page['submenu'] = processSubLinks([getPageSubmenuId($res[0]['__id'], $doctorID), "aside"], true);
		} else {
			$page['submenu'] = processSubLinks([getPageSubmenuId($res[0]['__id'], $doctorID), "aside"], false);
		}
	}
	
	if ($specialStyles){
		$page['specialStyles'] = "_special-styles";
	}
	
	if ($page['submenu']) {
		$page['leftAside'] = "page-inner__body_has-aside";
	}
	if ($page['Body2']) {
		$page['rightAside'] = "inner-content__body_has-aside";
	}

	//---------------------------------------------------------------
	if ($altHeader != "") $page['altHeader'] = $altHeader;
	if ($MetaTitle != "") $page['MetaTitle'] = $MetaTitle;
	if ($MetaKeywords != "") $page['MetaKeywords'] = $MetaKeywords;
	if ($MetaDescription != "") $page['MetaDescription'] = $MetaDescription;

	//---------------------------------------------------------------

	return $what ? $page[$what] : $page;
}

//===============================================================
function getBreadcrumbs($pageID, $current_items) {
	global $db, $page, $needBreadcrumbs, $needBreadcrumbsCurrent, $altBreadcrumbs, $doctorID, $dir_prefix;
	
	if ($pageID == page_index || !$needBreadcrumbs) return;
	
	if ($altBreadcrumbs) return $altBreadcrumbs;

	$page = $db->getData("select * from Pages where __id = '".intval($pageID)."'")[0];
	$parentID = $page['Parent'];

	if ($needBreadcrumbsCurrent) $res['current'] = $page['Header'];

	while ($page = $db->getData("select * from Pages where __id = '" . intval($parentID) . "'")[0]) {
		unset($b);
		$b['href'] = getRealLinkURL("pages:" . $page['__id']);
		$b['name'] = $page['Header'];
		$breadcrumbs[] = $b;
		$parentID = $page['Parent'];
	}

	if($current_items){
		$res['current'] = $current_items[0]['name'];
		array_unshift($breadcrumbs, $current_items[1]);
	}

	$res['breadcrumbs'] = array_reverse($breadcrumbs);

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/breadcrumbs.htm"), $res);
}

//===============================================================
function displayStaticBreadcrumbs($current){
	global $dir_prefix, $altBreadcrumbs;

	$b['href'] 	= $dir_prefix;
	$b['name'] = getHomePageName();
	$breadcrumbs[] = $b;

	$res['breadcrumbs'] = $breadcrumbs;
	$res['current'] = $current; 
	
	$altBreadcrumbs = parseTemplate(tplFromFile(dir_prefix."engine/templates/breadcrumbs.htm"), $res);
}

//===============================================================
function display404() {
	global $ent, $dir_prefix;
	$page['Header'] = "404";

	if ($dir_prefix == './') $dir_prefix = "/";

	$page['Body']   = parseTemplate(tplFromFile(dir_prefix . "engine/templates/404.htm"), $ent);
	headerNotFound();

	return $page;
}

//===============================================================
function getPageName($id) {
	global $db;
	$res = $db->getData("select Header from Pages where __id='" . intval($id) . "'", true);
	return $res[0]['Header'];
}

//===============================================================
function getHomePageName() {
	global $db;
	$res = $db->getData("select Header from Pages order by __id limit 1", true);
	return $res[0]['Header'];
}

//===============================================================
function displayBody($id) {
	global $db;
	$res = $db->getData("select Body from Pages where __id = '" . intval($id) . "'", true);
	$body = processText($res[0]['Body']);
	return $body;
}

//===============================================================

function getChildPages($id) {
	global $db;
	$res = $db->getData("select __id, Parent, Header, CustomOrder, Active from Pages where Parent='" . intval($id) . "' AND Active=1 order by CustomOrder", true);
	unset($pages);
	for ($i = 0; $i < count($res); $i++) {
		unset($page);
		$page['id'] = $res[$i]['__id'];
		$page['Header'] = $res[$i]['Header'];
		$pages[] = $page;
	}
	return $pages;
}

//===============================================================

function isParent($what = 0, $id = 0) {
	global $db;
	if ($what == 0 && $id == 0) return;
	$res = $db->getData("select Parent from Pages where __id='" . $id . "'", true);
	if ($res[0]['Parent'] == page_index || $res[0]['Parent'] == 0) return false;
	return ($res[0]['Parent'] == $what) ? true : isParent($what, $res[0]['Parent']);
}

//===============================================================

function getParentAtLevel($level, $id) {
	global $db;
	$res = $db->getData("select __id, Parent from Pages where __id='" . intval($id) . "'", true);
	$parent = $res[0]['Parent'];
	if (getPageLevel($id) == $level) return $id;
	else if ($parent != page_index) return getParentAtLevel($level, $parent);
	else return false;
}

//===============================================================
function getTopParent($id) {
	global $db;
	$pg = $db->getData("select __id, Parent from Pages where __id='" . intval($id) . "'", true);
	$parent = $pg[0]['Parent'];
	if ($parent != "" && $parent != 0 && $parent != page_index)
		$parent = getTopParent($parent);
	else
		$parent = $id;
	return $parent;
}

//===============================================================
function getPageLevel($id, $level = 0) {
	global $db;
	if ($id == page_index) return $level;
	$parent = $db->getData("select __id, Parent from Pages where __id='" . intval($id) . "'", true)[0]['Parent'];
	return getPageLevel($parent, $level + 1);
}

//===============================================================
function getPageSubmenuId($pageID, $doctorID = "") {
	global $db;

	if (($pageID == 159 || $pageID == 160) && $doctorID != "") {
		return getSubmenuForDoctors($doctorID);
	}

	$subPages = $db->getData("select __id from Pages where Parent = '".intval($pageID)."' limit 1");

	if(!$subPages && getPageLevel($pageID) == 1) return;

	if ($subPages) {
		return $pageID;
	} else {
		return $db->getData("select Parent from Pages where __id = '" .intval($pageID). "'")[0]['Parent'];
	}
}

//===============================================================
function getSubmenuForDoctors($doctorID) {
	global $db;

	$doctorCategory = $db->getData("SELECT CatItems.Category FROM CatItems WHERE CatItems.__id='".$doctorID."'")[0]['Category'];

	$subPages = $db->getData(
		"SELECT CatItems.Name, CatItems.DirectoryName, CatItems.__id
		FROM CatCategories
		INNER JOIN CatItems ON CatCategories.__id = CatItems.Category
		WHERE CatCategories.__id = '".$doctorCategory."'
		ORDER BY CatItems.CustomOrder ASC");

	foreach ($subPages as $item) {
		unset($it);
		$it['class'] = ($item['__id'] == $doctorID) ? "_active" : "";
		$it['href'] = '../'.$item['DirectoryName'].'/';
		$it['title'] = $item['Name'];
		$its[] = $it;
	}
	$ent['items'] = $its;
	$ent['categoryName'] = $db->getData(
		"SELECT CatCategories.Name FROM `CatCategories` 
		WHERE CatCategories.__id = '".$doctorCategory."'");

	return $ent;
}

//===============================================================
function getOpenGraph() {
	global $pageTitle, $site_logo;
	$current_url  = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$metas[]      = '<meta property="og:title" content="' . htmlspecialchars($pageTitle) . '" />';
	$metas[]      = '<meta property="og:type" content="website" />';
	$metas[]      = '<meta property="og:url" content="' . $current_url . '" />';
	$metas[]      = '<meta property="og:image" content="http://' . $_SERVER['HTTP_HOST'] . '/' . $site_logo . '" />';
	return join("\r\n", $metas);
}