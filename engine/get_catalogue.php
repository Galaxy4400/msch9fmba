<?php

$displaySubCatItems = true;
$catItemsLimit = 20; //лимит элементов на странице
$typeItemsLimit = 8; //лимит элементов каждого типа на главной странице

//===============================================================
function processCatalogue($what = []) {
	global $cid, $pid, $dir, $bdir, $bid, $special_styles;
	
	$special_styles = false;

	$cid = $what[0];
	$except = $what[1];

	if ($dir) {
		$c = getIDFromURL("CatCategories", $dir);
		if (!$c) {
			$c = getIDFromURL("CatItems", $dir);
			if (!$c)
				headerNotFound();
			else
				$pid = $c;
		} else {
			$cid = $c;
		}
	}

	if ($bdir)
		$bid = getBrandIDfromVirtualURL($bdir);

	if ($bid)
		return displayBrandCatalogue($bid, $cid);

	if ($pid > 0)
		return getCatItem($pid, $except);
	else
		return getCatCategory($cid, $except); 
}

//======================================================================================
function getCatCategory($id, $except = ""){
	global $db, $p, $catItemsLimit, $dir_prefix, $hasAside;
	global $MetaTitle, $MetaKeywords, $MetaDescription, $altHeader;

	$id = intval($id);
	$catItemsLimit = intval($catItemsLimit);
	list($catItems, $totalItems) = getCatItems($id, $except);
	
	//---------------------------------------------------------------
	if ($id > 0) {
		$category = $db->getData("select * from CatCategories where __id = '" . intval($id) . "' AND Active = 1")[0];

		displayCatBreadcrumbs();
		$MetaTitle = $category['MetaTitle'] ? $category['MetaTitle'] : $category['Name'];
		$MetaKeywords = $category['MetaKeywords'] ? $category['MetaKeywords'] : $category['Name'];
		$MetaDescription = $category['MetaDescription'] ? $category['MetaDescription'] : $category['Name'];
		$altHeader = $category['altHeader'] ? $category['altHeader'] : $category['Name'];
	}

	// -------------------- cat categories --------------------------
	$categories = $db->getData("select * from CatCategories where Parent='".intval($id)."' and Active = 1 order by CustomOrder");

	$cats = processCatCategories($categories);
	if($cats) $res['cats'] = $cats;

	//----------------------- cat items ----------------------------
	$items = processCatItems($catItems);
	if ($items) $res['items'] = $items;

	//--------------------- cat pagination -------------------------
	$suffix = getCatFilterSuffix();
	$pagination = generatePagination($totalItems, $p, $catItemsLimit, $dir_prefix."cat/cat".$id, $suffix);
	if ($pagination) {
		$res['pagination'] = $pagination;
	}

	//---------------------------------------------------------------
	$res['filterForm'] = displayFilterForm($id);

	//---------------------------------------------------------------
	$res['teamInfo'] = $mailFromName = getSystemVariable($db, "team_info");

	if ($except !== "") {
		$categories = $db->getData(
			"SELECT DISTINCT Name 
			FROM CatCategories 
			WHERE __id <> '".$except."' 
			ORDER BY CatCategories.Name ASC");
	} else {
		$categories = $db->getData(
			"SELECT DISTINCT Name 
			FROM CatCategories 
			WHERE __id = '".$cid."' 
			ORDER BY CatCategories.Name ASC");
	}

	for ($i = 0; $i < count($categories); $i++) {
		$categories[$i]['index'] = $i + 2;
	}

	$res['categories'] = $categories;
	//---------------------------------------------------------------

	if($id==0) {
		$hasAside = false;
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cat_root_doctors.htm"), $res);
	} else {
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cat_root_management.htm"), $res);
	}
}

//===============================================================
function getCatItems($id, $except = "") {
	global $db, $p, $catItemsLimit, $catSort, $catTypes;

	foreach ($_GET['type'] as $type) {
		$types[] = $catTypes[$type]['where'];
	}
	// $sort = $_GET['sort'] ? $catSort[$_GET['sort']] : $catSort[0];
	$sort['order'] = "CatItems.CustomOrder ASC";
	$catItemsLimit = $_GET['show'] ? $_GET['show'] : $catItemsLimit;
	
	//---------------------------------------------------------------
	if ($p < 1) $p = 1;
	$from = ($p - 1) * $catItemsLimit;
	if ($from < 0) $from = 0;
	$limit = "LIMIT ".$from.", ".$catItemsLimit;
	
	//---------------------------------------------------------------
	if ($types) 
		$where[] = "(" . implode(' or ', $types) . ")";
	$where[] = getFilterItemsWhere($id);
	$where[] = "CatItems.Active = '1'";
	$where[] = "CatItems.Created = '1'";
	if ($except !== "") {
		$where[] = "CatItems.Category <> '" . $except . "'";
	} else {
		$where[] = "CatItems.Category = '" . $cid . "'";
	}
	$where = "where ".implode(' and ', $where);

	//---------------------------------------------------------------
	$items = $db->getData("
		select 
			CatItems.*,
			CatCategories.Name as categoryName,
			Images.smallURL as smallImage,
			Images.bigURL as bigImage
		from 
			CatItems 
		left join 
			( select __id, smallURL, bigURL, Category, CustomOrder from Images ) Images
		on 
			Images.Category = CatItems.Gallery and Images.Category>0 and Images.CustomOrder=(select min(CustomOrder) from Images where Images.Category=CatItems.Gallery)
		left join 
			CatCategories
		on
			CatItems.Category = CatCategories.__id
		".$where."
		group by 
			CatItems.__id
		ORDER BY 
			".$sort['order']."
	");
	
	//---------------------------------------------------------------
	$totalItems = $db->getData("select count(__id) as cnt from CatItems ".$where)[0]['cnt'];

	$res[] = $items;
	$res[] = $totalItems;

	return $res;
}

//===============================================================
function processCatCategories($categories) {
	global $dir_prefix;

	foreach ($categories as $category) {
		unset($cat);
		$cat['name'] = $category['Name'];
		$cat['href'] = getRealLinkURL("cat:".$category['__id']);
		$cat['img'] = $category['Image'] ? $dir_prefix . $category['Image'] : $dir_prefix."images/img/plug.jpg";
		$cats[] = $cat;
	}
	return $cats;
}

//===============================================================
function processCatItems($catItems) {
	global $dir_prefix, $noPriceLable, $db;

	foreach ($catItems as $catItem) {
		unset($item);
		$item['id'] = $catItem['__id'];
		$item['category'] = $db->getData("
			SELECT DISTINCT CatCategories.Name FROM CatCategories 
			INNER JOIN CatItems
			ON CatItems.Category = CatCategories.__id
			WHERE CatCategories.__id = '".$catItem['Category']."'
		")[0][Name];
		$item['name'] = $catItem['Name'];
		$item['surname'] = mb_strtolower(strtok($item['name'], " "));
		$item['specialty'] = nl2br($catItem['Specialty']);
		$item['regalia'] = nl2br($catItem['Regalia']);
		$item['smallDesc'] = nl2br($catItem['SmallDesc']);
		$item['description'] = nl2br($catItem['Description']);
		$item['experience'] = $catItem['Experience'];
		$item['gallery'] = $catItem['Gallery'];
		$item['img'] = $catItem['smallImage'] ? $dir_prefix.$catItem['smallImage'] : $dir_prefix."images/img/plug-doctor.png";
		$item['href'] = getRealLinkURL('pid:'.$catItem['__id']);

		$items[] = $item;
	}

	return $items;
}

//===============================================================
function getCatItem($pid, $except = ""){
	global $db, $dir_prefix, $MetaKeywords, $MetaDescription, $altHeader, $altTitle, $noPriceLable, $rUser, $doctorID, $id, $altBreadcrumbs;

	$item = $db->getData("
		select 
			CatItems.*,
			CatCategories.Name as categoryName,
			Images.smallURL as smallImage,
			Images.bigURL as bigImage
		from 
			CatItems 
		left join 
			( select __id, smallURL, bigURL, Category, CustomOrder from Images ) Images
		on 
			Images.Category = CatItems.Gallery and Images.Category > 0 and Images.CustomOrder=(select min(CustomOrder) from Images where Images.Category=CatItems.Gallery)
		left join 
			CatCategories
		on
			CatItems.Category = CatCategories.__id
		where
			CatItems.__id='" . intval($pid) . "' AND CatItems.Active = 1 and CatItems.Created='1'
	")[0];

	// $item['Body'] = processText($item['Body']);
	$item['smallImage'] = $dir_prefix . $item['smallImage'];
	$item['bigImage'] = $item['bigImage'] ? $dir_prefix . $item['bigImage'] : $dir_prefix."images/img/plug-doctor.png";
	$item['href'] = getRealLinkURL('pid:'.$catItem['__id']);

	$current_items[0]['name'] = $item['Name'];
	$current_items[0]['href'] = getRealLinkURL("pid:".$item['__id']);

	$current_items[1]['name'] = getPageName($id);
	$current_items[1]['href'] = getRealLinkURL("page:".$id);

	$altBreadcrumbs = getBreadcrumbs($id, $current_items);

	if (!checkItemRatingVote($id))
		$item['rating'] = true;


	//---------------------------------------------------------------
	$gallery = $db->getData("select bigURL as bigImg, smallURL as smallImg from Images where Category = ".$item['Gallery'] . " order by CustomOrder");
	if ($gallery) {
		foreach ($gallery as &$image) {
			$image['smallImg'] = $dir_prefix . $image['smallImg'];
			$image['bigImg'] = $dir_prefix . $image['bigImg'];
		}
		$item['gallery'] = $gallery;
	} 
	
	//---------------------------------------------------------------
	$altTitle        		= ($item['MetaTitle'] !="") ? $item['MetaTitle'] : $item['Name'];
	$MetaKeywords     	= ($item['MetaKeywords']!="") ? $item['MetaKeywords'] : $item['Name'];
	$MetaDescription  	= ($item['MetaDescription']!="") ? $item['MetaDescription'] : $item['Name'];
	// $altHeader        	= ($item['altHeader']!="") ? $item['altHeader'] : $item['Name'];
	$altHeader        	= " ";
	$doctorID = $item['__id'];

	//---------------------------------------------------------------
	$item['Description'] = nl2br($item['Description']);
	$item['SmallDesc'] = nl2br($item['SmallDesc']);
	$item['Regalia'] = nl2br($item['Regalia']);
	$item['Specialty'] = nl2br($item['Specialty']);

	//---------------------------------------------------------------
	displayCatBreadcrumbs();
	
	//---------------------------------------------------------------
	$item['feedback'] = $db->getData(
		"SELECT * FROM Ratings 
		WHERE DoctorID = " . intval($pid) . " AND Feedback <> '' AND Visible = 1
		ORDER BY Ratings.Date DESC");

	if ($except) {
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cat_details_doctors.htm"), $item);
	} else {
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cat_details_management.htm"), $item);
	}

}
//======================================================================================
function getSeeAlso($itemID, $catID, $grp){
	global $db;

	if(!$grp) {
		$suffix = " AND CatItems.Category = '" . $catID . "' ";
	} else {
		$suffix = " AND CatItems.Grp='" . intval($grp) . "'";
	}

	$items = $db->getData("
		select 
			CatItems.*, 
			Images.smallURL as smallImage
		from 
			CatItems 
		left join 
			( select __id, smallURL, Category, CustomOrder from Images ) Images
		on 
			Images.Category = CatItems.Gallery and Images.Category>0 and Images.CustomOrder=(select min(CustomOrder) from Images where Images.Category=CatItems.Gallery)
		where 
			CatItems.Active = '1' AND 
			CatItems.Created='1' AND
			CatItems.__id <> '" . $itemID . "' 
			".$suffix."
		group by 
      		CatItems.__id
		ORDER BY 
			CatItems.__id DESC
	");

	if (!$items) return;

	$res['items'] = processCatItems($items);

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/seealso.htm"), $res);
}

//======================================================================================
function getParamsTable($parameters, $specific = null, $suffix = ""){
	$n = "\n";
	$parameters = explode($n, $parameters);
	unset($ps);

	for ($i = 0; $i < count($parameters); $i++) {
		unset($pp);
		list($param, $value) = explode(":", trim($parameters[$i]));
		$pp['param'] = $param;
		if ($value == "")
			$value = "-";
		if ($param != "") {
			$pp['value'] = $value;
			if (count($specific) < 1 || in_array($param, $specific))
				$ps[] = $pp;
		}
	}
	$ent['params'] = $ps;

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/params_table" . $suffix . ".htm"), $ent);
}

//======================================================================================
function displayCatMenu($parent = 0, $level = 0, $template = "cat_menu.htm"){
	global $db, $cid, $pid, $dir_prefix;

	if ($pid) {
		$cid = $db->getData("select Category from CatItems where __id = ".intval($pid))[0]['Category'];
	}
	
	$parent = intval($parent);
	$res = $db->getData("SELECT * FROM CatCategories WHERE Parent='".$parent."' AND Active = 1 ORDER BY CustomOrder");
	
	unset($menu);
	foreach ($res as $elem) {
		unset($c);
		$c['name'] = $elem['Name'];
		$c['emphasise'] = $elem['isEmphasise'] ? "cat-menu__sub-link_emphasise" : "";
		$c['href'] = getRealLinkURL("cid:" . $elem['__id']);
		$c['icon'] = $dir_prefix . $elem['Icon'];
		$c['submenu'] = displayCatMenu($elem['__id'], $level + 1, "cat_submenu.htm");
		$c['itemsCount'] = getCountCatItems($elem['__id']);
		if($cid == $elem['__id'])
			$c['active'] = '_active';
			
		$menu[] 		= $c;
	}
	
	if (!$menu) return;

	$res['menu'] = $menu;

	if ($level > 1)
		$res['level'] = true;
	
	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/".$template), $res);
}

//===============================================================
function displayItemGallery($id){
	global $ent, $db, $dir_prefix;
	if ($id == "") 
		return "";

	$res = $db->getData("
		SELECT 
			Images.* 
		FROM 
			CatItems
		LEFT JOIN 
			Images 
		ON 
			CatItems.Gallery = Images.Category
		where 
			CatItems.__id='" . intval($id) . "' AND
			CatItems.Gallery>0
		ORDER BY 
			Images.CustomOrder
	");

	unset($slides);
	$index = 0;
	for ($i = 0; $i < count($res); $i++) {
		unset($sl);
		$sl['bigURL'] 		= $dir_prefix.$res[$i]['bigURL'];
		$sl['smallURL']  	= $dir_prefix.$res[$i]['smallURL'];
		$sl['name'] 		= $res[$i]['Name'];
		$sl['index'] 		= $index;
		$slides[] = $sl;
		$index ++;
	}

	$ent['slides'] 		= $slides;

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/slides.htm"), $ent);
}

//===============================================================
function displayMainPageCatalog() {
	global $dir_prefix;

	$res['dir_prefix']	= $dir_prefix;
	$res['hit']	= getCatItemsByType('isHit');
	$res['new']	= getCatItemsByType('isNew');
	$res['sale'] = getCatItemsByType('isSale');

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cat_index.htm"), $res);
}

//===============================================================
function getCatItemsByType($type) {
	global $db, $typeItemsLimit;

	if($typeItemsLimit > 0) $limit_suffix = " limit 0, ".intval($typeItemsLimit);

	$res = $db->getData("
		select 
			CatItems.*,
			Images.CustomOrder,
			Images.smallURL as smallImage
		from 
			CatItems 
		left join 
			( select __id, smallURL, Category, CustomOrder from Images ) Images
		on 
			Images.Category = CatItems.Gallery and Images.Category>0 and Images.CustomOrder=(select min(CustomOrder) from Images where Images.Category=CatItems.Gallery)
		where 
			CatItems.".$type."='1' and 
			CatItems.Active='1' and 
			CatItems.Created='1' and
			Images.smallURL <> '' 
		order by 
			CatItems.CustomOrder " . $limit_suffix
	);



	$items = processCatItems($res);
	return $items;
}

//===============================================================
function getCountCatItems($catID) {
	global $db;

	$subCats = $db->getData("select __id as id from CatCategories where Parent = " . intval($catID) . " and Active = 1");
	$catItems = $db->getData("select count(*) as cnt from CatItems where Category = " . intval($catID) . " and Active = 1");

	$subCats = array_column($subCats, 'id');
	$count = $catItems[0]['cnt'];

	foreach ($subCats as $cat) {
		$count += getCountCatItems($cat);
	}

	return $count;
}

//===============================================================
function getChildCategories($parent = 0){
	global $db;
	unset($r);
	$res = $db->getData("select __id from CatCategories where Parent='" . intval($parent) . "'", true);
	for ($i = 0; $i < count($res); $i++) {
		if ($res[$i]['__id'] > 0) {
			$r[] = $res[$i]['__id'];
			$r = my_array_merge($r, getChildCategories($res[$i]['__id']));
		}
	}
	return $r;
}

//======================================================================================
function isCatParent($what, $id){
  global $db;
  if(!is_numeric($id)||!is_numeric($what)) return false;
  if(!isset($id)||!isset($what)) return false;

	$pg = $db->getData("select __id, Parent from CatCategories where __id='".intval($id)."'", true);
	$parent = $pg[0]['Parent'];

	if($parent == $what) {
		$res 	= true;
	} else if ($parent != page_index) {
		$res = isCatParent($what, $parent);
	} else {
		$res = false;
	}
	return $res;
}

//===============================================================
function getTopCatParent($id){
	global $db;
	$pg 	= $db->getData("select __id, Parent from CatCategories where __id='" . intval($id) . "'");
	$parent = $pg[0]['Parent'];
	if ($parent != "" && $parent != 0)
		$parent = getTopCatParent($parent);
	else
		$parent = $id;
	return $parent;
}

//======================================================================================
function getCatParent($id){
	global $db;
	return $db->getData("select * from CatCategories where __id = '" . intval($id) . "'")[0];
}

//===============================================================
function getCatFilterSuffix() {
	$url = explode('&', $_SERVER['QUERY_STRING']);
	foreach($url as $k => &$u) {
		if(stristr($u, 'p=') || stristr($u, 'cid=')) {
			unset($url[$k]);
		}
	}
	$url = implode('&', $url);

	return $url ? '&'.$url : "";
}

//===============================================================
function displayCatBreadcrumbs(){
	global $db, $page, $needBreadcrumbs, $needBreadcrumbsCurrent, $altBreadcrumbs, $pid;

	$product = $db->getData("select * from CatItems where __id = '" . intval($pid) . "'")[0];
	$res['current'] = $product['Name'];

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
	$b['href'] = $dir_prefix;
	$b['name'] = getHomePageName();
	$breadcrumbs[] = $b;

	$res['breadcrumbs'] = array_reverse($breadcrumbs);

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/breadcrumbs.htm"), $res);
}


//===============================================================
function doItemRating($itemID, $value) {
	global $db, $rUser;

	$itemID = intval($itemID);
	$value = intval($value);

	if (!$value) 
		return getItemRating($itemID);

	if($rUser && checkItemRatingVote($itemID))
		return getItemRating($itemID);

	if(!$rUser){
		$rUser = generatePass(15);
		my_setcookie("rUser", $rUser, time() + 10 * 365 * 24 * 60 * 60); 
	}

	$data['ItemID'] = $itemID;
	$data['Value'] = $value;
	$data['User'] = $rUser;
	$data['IP'] = getVisitorRealIP();

	$db->my_insert('CatItemsRating', $data);

	return getItemRating($itemID);
}

function checkItemRatingVote($itemID) {
	global $db, $rUser;
	$check = $db->getData("select * from CatItemsRating where ItemID = '".intval($itemID)."' and User='".mysql_real_escape_string($rUser)."'");
	return $check ? true : false;
}

function getItemRating($itemID) {
	global $db;
	$res = $db->getData("SELECT ROUND(SUM(Value) / COUNT(*), 1) AS rating FROM CatItemsRating WHERE ItemID = $itemID")[0]['rating'];
	return $res ? $res : "0.0";
}

function addFeedback($doctor_id, $scope = 0, $feedback = "", $FullName = "", $Email = "") {
	global $db;

	if ($scope == 0 && $feedback == "") {
		$ent['title'] = "Данные не отправлены!";
		$ent['desc'] = "Пожалуйста, поставьте оценку и/или напишите отзыв!";
		return parseTemplateFromFile(dir_prefix."engine/templates/error_feedback.htm", $ent);
	} else {
		$db->query(
			"INSERT INTO `Ratings`(`DoctorID`, `Scope`, `Feedback`, `FullName`, `Email`) 
			 VALUES (
				'".intval($doctor_id)."', 
				'".intval($scope)."', 
				'".mysql_real_escape_string($feedback)."',
				'".mysql_real_escape_string($FullName)."',
				'".mysql_real_escape_string($Email)."'
				)
			");
		$ent['message'] = "Данные отправлены!";
		$ent['messageCaption'] = "Спасибо за проявленный интерес!";
		$ent['class'] = " success ";
		return parseTemplateFromFile(dir_prefix."engine/templates/message_feedback.htm", $ent);
	}
}