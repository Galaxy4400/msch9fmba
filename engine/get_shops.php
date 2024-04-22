<?php

$city_selected_default = "Москва";

//===============================================================
function processShops() {
  global $db, $ent, $c, $dir, $special_styles;
	$special_styles = false;

  if($dir!="") {
   $c = getIDFromURL("Shops", $dir);
   if(!$c) die404();
  }

  return displayShops();
}

//===============================================================
function displayShops() { //create sshop_list.htm and shops_markers.htm
  global $db, $action, $city_selected_default;

  $where = "City = '".mysql_real_escape_string($city_selected_default)."'";

  $res = $db->getData("SELECT * FROM Shops WHERE Active = '1' AND ".$where." ORDER BY Name");

  for($i = 0; $i<count($res); $i++){
    unset($c);
    $c['num'] = $i;
    $c['name'] = $res[$i]['Name'];

    if ($res[$i]['__id']==130){
			$c['attr'] = 'href="'.$res[$i]['Site'].'" target="_blank"';
			$c['site'] = "<br><a target=\"_blank\" rel=\"nofollow\" href=\"".$res[$i]['Site']."\">перейти на сайт</a>";
    }else if($res[$i]['Address'] != '' && $res[$i]['Site'] != ""){
			$c['attr'] = 'href="javascript:void(0)" data-marker-id="'.$c['num'].'"';
			$c['site'] = "<br><a target=\"_blank\" rel=\"nofollow\" href=\"".$res[$i]['Site']."\">перейти на сайт</a>";
    }else if ($res[$i]['Address'] != '' && $res[$i]['Site'] == "") {
			$c['attr'] = 'href="javascript:void(0)" data-marker-id="'.$c['num'].'"';
    }else if ($res[$i]['Address'] == '' && $res[$i]['Site'] != "") {
			$c['attr'] = 'href="'.$res[$i]['Site'].'" target="_blank"';
    }else{
			$c['attr'] = '';
    }

    $c['address']   = ($res[$i]['Address']!='') ? str_replace(array("\r","\n"), "", nl2br($res[$i]['Address'])) : '';
    $c['lat']       = $res[$i]['lat'];
    $c['lon']       = $res[$i]['lon'];
    $c['markerId']  = $c['num'];

    if($c['address']!="" && $c['lat']!="" && $c['lon']!="")
      $cats[] = $c;
  }

  $ent1 = array();
  $ent1['shops']        = $cats;
  $ent['shops_list']    = parseTemplate(tplFromFile(dir_prefix . "engine/templates/shops_list.htm"), $ent1);
  $ent['shops_markers'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/shops_markers.htm"), $ent1);

  $ent['countries'] = getShopCities();
  $ent['filter_search'] = $filter_search;

  return parseTemplate(tplFromFile(dir_prefix . "engine/templates/shops.htm"), $ent);
}

//===============================================================
function getShopsList(){
  global $db;

  $where = getShopsWhere();

  $res = $db->getData("SELECT * FROM Shops WHERE Active = 1 ".$where." ORDER BY Name");

  if(count($res)<1)
    return "<div class='text-center'>Результатов нет</div>";

  for($i = 0; $i<count($res); $i++){
    unset($c);
    $c['num'] = $i;
    $c['markerId']  = $c['num'];
    $c['name'] = $res[$i]['Name'];
    $c['address'] = str_replace(array("\r","\n"), "", nl2br($res[$i]['Address']));
    $c['lat'] = $res[$i]['lat'];
    $c['lon'] = $res[$i]['lon'];
    $c['attr'] =  'href="javascript:void(0)" data-marker-id="'.$c['num'].'"';

    if($res[$i]['Site']!=""){
      $c['site'] = "<br><a target=\"_blank\" rel=\"nofollow\" href=\"".$res[$i]['Site']."\">перейти на сайт</a>";
    }

    $cats[] = $c;
  }

  $ent['shops'] = $cats;

  return parseTemplate(tplFromFile(dir_prefix . "engine/templates/shops_list.htm"), $ent);
} 

//===============================================================
function getShopsMarkers(){
  global $db;

  $where = getShopsWhere();

  $res = $db->getData("SELECT * FROM Shops WHERE Active = 1 ".$where." ORDER BY Name");

  for($i = 0; $i<count($res); $i++){
    unset($c);
    $c['lat'] = $res[$i]['lat'];
    $c['lon'] = $res[$i]['lon'];
    $c['html'] = $res[$i]['Name']."<br>".str_replace(array("\r","\n"), "", nl2br($res[$i]['Address']))."<br>".str_replace(array("\r","\n"), "", nl2br($res[$i]['Phone']));
    $c['markerId']  = $i;
    $markers[] = $c;
  }

  return json_encode($markers);
}

//===============================================================
function getShopsWhere(){
  global $filter_search, $filter_city, $filter_type;

  $where = "";

  $filter_search = mysql_real_escape_string(trim($filter_search));
  $filter_city = mysql_real_escape_string(trim($filter_city));
  $filter_type = mysql_real_escape_string(trim($filter_type));

  if($filter_search != ""){
    $where.= " AND (Name LIKE '%".$filter_search."%' OR Address LIKE '%".$filter_search."%')";
  }

  if($filter_city != ""){
    $where.= " AND City = '".$filter_city."'";
  }

  return $where;
}

//===============================================================
function getShopCities(){
  global $db, $city_selected_default;

  $res = $db->getData("SELECT DISTINCT(Country) FROM Shops ORDER BY Country");

  for ($i=0; $i < count($res); $i++) { 
    unset($c);
    $c['name'] = $res[$i]['Country'];
    $cities = $db->getData("SELECT DISTINCT(City) FROM Shops WHERE Country='".$res[$i]['Country']."' ORDER BY City");

    unset($rows);
    for ($j=0; $j < count($cities); $j++) {
      unset($k);
      $k['name'] = $cities[$j]['City'];
      if($k['name'] == $city_selected_default){
        $k['selected'] = "selected";
      }
      $rows[] = $k;
    }
    $c['cities'] = $rows;
    $cats[] = $c;
  }

  return $cats;
}

//===============================================================
function getShopTypes(){
  global $shopTypes, $type_selected_default;

  foreach ($shopTypes as $key => $val) {
    unset($c);
    $c['name'] = $val;
    $c['id'] = $key;
    if($key == $type_selected_default){
      $c['active'] = "active";
    }
    $cats[] = $c;
  }
  return $cats;
}