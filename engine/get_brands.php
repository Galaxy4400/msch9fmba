<?php

//===============================================================
function displayBrandsForMain() {
	global $db;

	$brands = $db->getData("select * from Brands order by CustomOrder");

	foreach ($brands as &$brand) {
		$brand = prepareBrand($brand);
	}

	$res['brands'] = $brands;

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/main_brands.htm"), $res);
}


//===============================================================
function displayBrandCatalogue($brandID, $catID = 0) {
	global $db;

	$categories = $db->getData("select * from CatCategories where Parent='0' and Active = 1 order by CustomOrder");

	$cats = processCatCategories($categories);

	$brand_param_name = getBrandParamName();

	$brandFilter[$brand_param_name][] = $db->getData("select Name from Brands where __id = " . intval($brandID))[0]['Name'];

	$suffix = http_build_query($brandFilter);

	foreach ($cats as &$cat) {
		$cat['href'] = $cat['href'] . "?" . $suffix;
	}

	$res['cats'] = $cats;

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cat_brand.htm"), $res);
}

//===============================================================
function prepareBrand($brand, $catID = 0){
	global $dir_prefix, $s;
	if ($catID || !empty($s)) {
		$brand['href'] = $dir_prefix . 'cat/?brand_id='.$brand['__id'].'&'.catItemsQueryString(['brand_id', 'params', 'type', 'p']);
	} else {
		$brand['href'] = $dir_prefix . getRealLinkURL("brand:{$brand['__id']}");
	}
	$brand['img'] = $dir_prefix . ($brand['Image'] ? : "images/img/plug.jpg");

	return $brand;
}

//===============================================================
function getBrandParamName() {
	global $db; 

	$paramID = $db->getData("select * from CatItemProperties where PropertyName = 'Бренд'")[0]['__id'];

	return "p{$paramID}";
}