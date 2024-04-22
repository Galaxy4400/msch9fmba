<?php

//===============================================================
function displayPageHead() {
	global $is_index;

	$res['menu'] = displayMainMenu();
	$res['search'] = displaySearchForm();
	if ($is_index) {
		$res['slider'] = displayMainSlider();
	}

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/page_head.htm"), $res);
}

//===============================================================
function displayInnerHead() {
	global $page;
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/inner_head.htm"), $page);
}

//===============================================================
function displayAside() {
	global $is_index, $page;

	$res['is_index'] = $is_index;
	
	if ($is_index) {
		$res['banners'] = displayBanners();
	}
	$res['menu'] = displayCatMenu();
	
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/aside.htm"), $res);
}

//===============================================================
function displayLinksList() {
	global $db;
	$res = $db->getData("select __id, Header from Pages where __id in(145,146,147)");
	foreach ($res as &$item) {
		$item['link'] = getRealLinkURL('pages:'.$item['__id']);
	}
	$ent['links'] = $res;
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/links.htm"), $ent);
}

//===============================================================
function displayToday() {
	$res['today'] = processDate(timeStr(), 1);
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/today.htm"), $res);
}

//===============================================================
function displaySocial() {
	global $db, $dir_prefix;
	$res = $db->getData("select *from Images where Category = 787");
	foreach ($res as $elem) {
		unset($s);
		$s['img'] = $dir_prefix.$elem['bigURL'];
		$s['href'] = $elem['Link'];
		$socials[] = $s;
	}
	$res['socials'] = $socials;
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/social.htm"), $res);
}

//===============================================================
function displayMainAbout() {
	global $db, $dir_prefix;

	$images = $db->getData("select * from Images where Category = 800");
	
	foreach ($images as $img) {
		unset($i); 
		$i['name'] = $img['Name'];
		$i['img'] = $dir_prefix . $img['bigURL'];
		$ii[] = $i;
	}

	$res['dir_prefix'] = $$dir_prefix;
	$res['images'] = $ii;
	
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/main_about.htm"), $res);
}

//===============================================================
function displayPageBanners() {
	global $db, $dir_prefix;

	$bigBanners = $db->getData("select * from Images where Category = 803");
	$smalllBanners = $db->getData("select * from Images where Category = 804");

	unset($bb);
	foreach ($bigBanners as $bigBanner) {
		unset($b);
		$b['title'] = $bigBanner['Name'];
		$b['desc'] = $bigBanner['Description'];
		$b['img'] = $dir_prefix . $bigBanner['bigURL'];
		if ($bigBanner['Link']) {
			$b['href'] = getRealLinkURL($bigBanner['Link']);
		}
		$bb[] = $b;
	}
	$res['bigBanners'] = $bb;
	
	unset($sb);
	foreach ($smalllBanners as $smallBanner) {
		unset($b);
		$b['title'] = $smallBanner['Name'];
		$b['desc'] = $smallBanner['Description'];
		$b['img'] = $dir_prefix . $smallBanner['bigURL'];
		if ($smallBanner['Link']) {
			$b['href'] = getRealLinkURL($smallBanner['Link']);
		}
		$sb[] = $b;
	}
	$res['smallBanners'] = $sb;

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/page_banners.htm"), $res);
}

//===============================================================
function displayRubricator($pageID) {
	$res['title'] = getPageName($pageID);
	$res['grid']	= displayPagesGrid($pageID);
	
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/rubricator.htm"), $res);
}

//===============================================================
function displayPagesGrid($pageID) {
	global $db, $dir_prefix, $is_index;
	
	if(!$is_index) $and = "";
	else $and = "and __id<>29";
	
	$pages = $db->getData("select * from Pages where Parent = " . intval($pageID) . " ".$and." and Active = 1 order by CustomOrder");

	foreach ($pages as $page) {
		$res['pages'][] = [
			'name' => $page['Header'],
			'img' => $dir_prefix . $page['Picture'],
			'link' => getRealLinkURL('pages:'.$page['__id'])
		];
	}

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/pages_grid.htm"), $res);
}

//===============================================================
function displayMap() {
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/map.htm"), []);
}

//===============================================================
function processEmployee($element) {
	global $db, $dir_prefix;

	$id = intval($element[0]);

	$res = $db->getData("select * from Images where __id = '$id'")[0];

	if (!$res) {
		$res['message'] = "Такого сотрудника не существует";
	}

	list($res['description'], $res['phone']) = array_map('trim', explode('|', $res['Description']));
	
	$res['phone'] = $res['phone'] ? $res['phone'] : getSystemVariable($db, 'phone');
	$res['phoneFormat'] = phoneToLink($res['phone']);
	$res['dir_prefix'] = $dir_prefix;

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/employee.htm"), $res);
}

//===============================================================
function processAppointmentActions() {
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/appointment_actions.htm"), []);
}

//===============================================================
function displayWebis() {
	global $dir_prefix;
	$res = '
		<a id="webisgroup" href="https://webisgroup.ru/" target="_blank">
			<img src="'.$dir_prefix.'images/img/webis.svg" alt="webisgroup">
			<span>создание сайта</span>
		</a>
	';
	
	return $res;
}


//===============================================================

function processTest() {
	return parseTemplate(tplFromFile(dir_prefix."engine/templates/test.htm"), []);
}