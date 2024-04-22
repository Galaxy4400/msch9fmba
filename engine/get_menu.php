<?php

//===============================================================
function displayMainMenu($root = page_index, $curId = 0, $level = 0, $template = "mainmenu", $templatesub = "submenu", $suffix = "", $subsufix = "", $showSubmenu = true) {
	global $db, $id;
	
	if ($curId == 0) $curId = $id;

	$root = intval($root);

	$query = "
    select 
      __id,
      Header,
      DirectoryName,
      CustomOrder
    from 
      Pages 
    where 
      Parent = '" . $root . "' AND 
      Enabled = 1 AND 
      Active > '0'
      " . $suffix . " 
    order by
    	CustomOrder
  ";

	$res = $db->getData($query);

	unset($menu);
	if (!count($res)) return "";

	$submenuActive = false;

	for ($i = 0; $i < count($res); $i++) {
		unset($m);

		$m['name'] = $res[$i]['Header'];
		$m['href'] = getRealLinkURL("pages:" . $res[$i]['__id']);

		if ($showSubmenu) {
			$m['submenu'] = displayMainMenu($res[$i]['__id'], $curId, $level + 1, $templatesub, $templatesub, $subsufix, $subsufix);
		}

		if ($res[$i]['__id'] == $curId || isParent(intval($res[$i]['__id']), intval($curId))) {
			$m['active'] = "_active";
			$submenuActive = true;
		} else {
			$m['active'] = "";
		}

		$m['target']  = "";

		$menu[] = $m;
	}

	$ent['menu'] = $menu;

	$ent['arrow_class'] = ($level > 1) ? "menu__sub-arrow" : "menu__arrow";
	$ent['level'] = "_$level";
	if ($submenuActive) {
		$ent['active'] = "_active";
	}

	$res = parseTemplateFromFile(dir_prefix . "engine/templates/" . $template . ".htm", $ent);

	return $res;
}

//===============================================================
function getCatMenu($Parent = 0, $level = 0, $template = "sitemap.htm") {
	global $db, $ent, $cid;

	$Parent = intval($Parent);

	$res 	= $db->getData("select __id, Name, Image from CatCategories where Parent='" . $Parent . "' and Active='1' order by CustomOrder");

	if (count($res) == "")
		return "";

	foreach ($res as $cat) {
		unset($p);
		if ($level < 1) $p['nextlevel']  = getCatMenu($cat['__id'], $level + 1);
		$p['class']      = ($cid == $cat['__id']) ? "_active" : "";
		$p['name']       = $cat['Name'];
		$p['title']      = htmlspecialchars($cat['Name']);
		$p['href'] 		   = getRealLinkURL("cid:" . $cat['__id']);
		$p['photo_src']  = dir_prefix . $cat['Image'];
		$cats[] 		     = $p;
	}
	$ent['menu'] 		= $cats;

	$ent['level'] = $level;

	return parseTemplateFromFile(dir_prefix . "engine/templates/" . $template, $ent);
}

//===============================================================
function processSubLinks($element, $forDoctors = false) {
	global $db, $id, $doctorID;

	if($forDoctors) {
		$res['items'] = $element[0]['items'];
		$res['category'] = $element[0]['categoryName'][0]['Name'];
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/sublinks_aside.htm"), $res);
	}

	if ($element[0] == 1) $pageCategory = 'Меню';
	else $pageCategory = $db->getData("SELECT Header FROM Pages WHERE __id = '".$element[0]."'")[0]['Header'];
	$ent['category'] = $pageCategory;

	if ($element[0] > 0) {
		$pageID = $element[0];
	} else {
		$pageID = $id;
	}
		
	$submenuTemplate = $element[1];
	if ($submenuTemplate) {
		$submenuTemplate = "_".$submenuTemplate;
	}

	$items = $db->getData("select * from Pages where Parent='" . intval($pageID) . "' AND Enabled='1' AND Active =1 order by CustomOrder");

	if (!$items) return false;

	foreach ($items as $item) {
		unset($it);
		$it['class'] = ($item['__id'] == $id) ? "_active" : "";
		$it['href'] = getRealLinkURL("pages:" . $item['__id']);
		$it['title'] = $item['Header'];
		$its[] = $it;
	}
	$ent['items'] = $its;

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/sublinks" . $submenuTemplate . ".htm"), $ent);
}

//===============================================================
function getSitemap($Parent = 0, $id, $level = 0) {
	global $db, $ent;
	$Parent = intval($Parent);
	$res 	= $db->getData("select __id, Header, DirectoryName from Pages where Parent='" . $Parent . "' and Enabled='1' order by CustomOrder");
	if (count($res) == "")
		return "";

	foreach ($res as $page) {
		unset($p);
		if ($page['DirectoryName'] == 'cat') {
			$p['nextlevel'] = getCatMenu(0, 0);
		} else {
			$p['nextlevel'] = getSitemap($page['__id'], $id, $level + 1);
		}
		$p['name'] 		= $page['Header'];
		$p['title'] 	= htmlspecialchars($page['Header']);
		$p['href'] 		= getRealLinkURL("pages:" . $page['__id']);
		$pages[] 		  = $p;
	}
	$ent['menu'] 		= $pages;
	return parseTemplateFromFile(dir_prefix . "engine/templates/sitemap.htm", $ent);
}

//===============================================================
function displaySiteMap($array_only = false) {
	global $ent, $altTitle, $altHeader, $altBreadcrumbs, $is_sitemap;

	$is_sitemap = true;

	$filename = dir_prefix . "tmp/sm" . language;

	if (!$array_only) include($filename . ".php");

	if ($last_modified >= last_update) {
		$p['Body'] = join("", file($filename . ".htm"));
	} else {
		set_time_limit(120);
		$p['Body'] = getSitemap(page_index, 0, 0);

		$fl = fopen($filename . ".htm", "w");
		fwrite($fl, $p['Body']);
		fclose($fl);

		$phpStr = "
		<?
			$" . "last_modified = " . time() . ";
		?>";

		$fl = fopen($filename . ".php", "w");
		fwrite($fl, $phpStr);
		fclose($fl);
		chmod($filename . ".htm", 0666);
		chmod($filename . ".php", 0666);
	}

	$altTitle = "";
	$altHeader = "";

	if ($array_only)
		return $ent['menu'];
	else
		return $p;
}