<?php

$catSort = [
	0 => [
		'id' => 0, 
		'name' => 'по новизне', 
		'order' => ' CatItems.DateCreate asc', 
		'checked' => ($_GET['sort'] < 1) ? 'checked' : ''
	],

	1 => [
		'id' => 1,
		'name' => 'по цене',
		'order' => ' CatItems.Price asc',
		'checked' => ($_GET['sort'] == 1) ? 'checked' : ''
	],

	2 => [
		'id' => 2,
		'name' => 'по алфавиту',
		'order' => ' CatItems.Name asc',
		'checked' => ($_GET['sort'] == 2) ? 'checked' : ''
	]
];

//---------------------------------------------------------------

$catTypes = [
	0 => [
		'id' => 0,
		'name' => 'Хиты продаж',
		'where' => 'CatItems.isHit = 1',
		'checked' => in_array(0, $_GET['type']) ? "checked" : ""
	],

	1 => [
		'id' => 1,
		'name' => 'Новинки каталога',
		'where' => 'CatItems.isNew = 1',
		'checked' => in_array(1, $_GET['type']) ? "checked" : ""
	],
	
	2 => [
		'id' => 2,
		'name' => 'Распродажа',
		'where' => 'CatItems.isSale = 1',
		'checked' => in_array(2, $_GET['type']) ? "checked" : ""
	],
];

//---------------------------------------------------------------

$catShow = [
	0 => [
		'value' => 20,
		'selected' => $_GET['show'] == 20 ? "selected" : ""
	],

	1 => [
		'value' => 50,
		'selected' => $_GET['show'] == 50 ? "selected" : ""
	],
	
	2 => [
		'value' => 100,
		'selected' => $_GET['show'] == 100 ? "selected" : ""
	],
];



//===============================================================
//===============================================================
//===============================================================
function displayFilterForm($catID) {
	global $p, $catSort, $catTypes, $catShow;

	$res['filters'] = getCatalogueFilters($catID);
	$res['cid'] = $catID;
	$res['p'] = $p;
	$res['types'] = $catTypes;
	$res['sorts'] = $catSort;
	$res['show'] = $catShow;

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/filter_form.htm"), $res);
}

//===============================================================
function getCatalogueFilters($catID) {
	$numeric = [];
	$nonNumeric = [];
	
	$parameters = getCatCategoryParameters($catID);

	foreach ($parameters as $parameter) {
		if ($parameter['isNumeric']) {
			$entered_min_var_name = "p" . $parameter['__id'] . "_min";
			$entered_max_var_name = "p" . $parameter['__id'] . "_max";

			$numeric[] = [
				'__id' => $parameter['__id'],
				'pname' => $parameter['PropertyName'],
				'default_min' => floor($parameter['PropertyMin']),
				'default_max' => ceil($parameter['PropertyMax']),
				'entered_min' => $_GET[$entered_min_var_name],
				'entered_max' => $_GET[$entered_max_var_name],
			];
			
		} else {
			$paramVariants = $parameter['variants'];

			foreach ($paramVariants as &$variant) {
				$variant['pid'] = $parameter['__id'];
				$pvArrName = "p" . $parameter['__id'];
				if (in_array($variant['PropertyValue'], $_GET[$pvArrName])) $variant['checked'] = " checked";
			}

			$nonNumeric[] = array(
				'__id' => $parameter['__id'],
				'pname' => $parameter['PropertyName'],
				'values' => $paramVariants
			);
		}
	}

	$res['numeric'] = $numeric;
	$res['nonNumeric'] = $nonNumeric;
	$res['catID'] = $catID;

	return parseTemplateFromFile(dir_prefix . "engine/templates/filters.htm", $res);
}

//===============================================================
function getFilterItemsWhere($catID) {
	global $db;

	$catItems = array_column($db->getData("select __id from CatItems where " . getFilterCatsWhere($catID)), "__id");

	$parameters = getCatCategoryParameters($catID);

	foreach ($parameters as $parameter) {
		$p_where = [];
		
		if ($parameter['isNumeric']) {
			$entered_min_var_name = "p" . $parameter['__id'] . "_min";
			$entered_max_var_name = "p" . $parameter['__id'] . "_max";

			if ($_GET[$entered_min_var_name]) 
				$p_where[] = "(cv.PropertyID='" . $parameter['__id'] . "' AND cv.PropertyValueNumeric >= '" . floatval($_GET[$entered_min_var_name]) . "')";
			if ($_GET[$entered_max_var_name]) 
				$p_where[] = "(cv.PropertyID='" . $parameter['__id'] . "' AND cv.PropertyValueNumeric <= '" . floatval($_GET[$entered_max_var_name]) . "')";

		} else {
			$pvArrName = "p" . $parameter['__id'];
			$p_val_where = [];

			foreach ($_GET[$pvArrName] as $value)
				if ($value) $p_val_where[] = "cv.PropertyValue='" . mysql_real_escape_string($value) . "'";

			if ($p_val_where)
				$p_where[] = "(cv.PropertyID='" . $parameter['__id'] . "' AND (" . join(" OR ", $p_val_where) . "))";
		}

		if ($p_where) {
			$query = "select ItemID from CatItemPropertyValues cv where " . join(" AND ", $p_where) . " AND cv.ItemID in (" . join(", ", $catItems) . ")";
			$pres = $db->getData($query);
			if ($pres) $catItems = array_column($pres, "ItemID");
		}
	}

	if ($catItems) {
		$result = "CatItems.__id in (" . join(", ", $catItems) . ")";
	} else {
		$result = "CatItems.__id = -1";
	}

	return $result;
}

//===============================================================
function getCatCategoryParameters($catID) {
	global $db;

	$query = "
		select 
			distinct cp.*, 
			min(cv.PropertyValueNumeric) as PropertyMin, 
			max(cv.PropertyValueNumeric) as PropertyMax
		from CatItemProperties cp 
		inner join CatItemPropertyValues cv on cv.PropertyID=cp.__id
		inner join CatItems on CatItems.__id = cv.ItemID
		where " . getFilterCatsWhere($catID) . "
		group by cp.__id
		order by cp.isNumeric desc, cp.PropertyName
	";

	$parameters = $db->getData($query);

	foreach ($parameters as &$parameter) {
		$variants = $db->getData("
			select distinct cv.PropertyValue
			from CatItemPropertyValues cv
			inner join CatItems on CatItems.__id = cv.ItemID
			inner join CatItemProperties cp on cp.__id = cv.PropertyID and cp.isNumeric = '0'
			where cv.PropertyID = '" . $parameter['__id'] . "' AND " . getFilterCatsWhere($catID) . "
			group by cv.PropertyValue
			order by cv.PropertyValue
		");

		$parameter['variants'] = $variants;
	}

	return $parameters;
}

//===============================================================
function getFilterCatsWhere($catID) {
	global $displaySubCatItems;

	if ($displaySubCatItems) {
		$cats = getChildCategories($catID);
		$cats[] = $catID;
		$where = "CatItems.Category in ('" . implode("','", $cats) . "')";
	} else {
		$where = "CatItems.Category = " . intval($catID);
	}

	return $where;
}