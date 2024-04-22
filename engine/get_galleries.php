<?php

//===============================================================
function displayMainSlider() {
	global $db, $dir_prefix;

	$sliders = $db->getData("select * from Images where Category = '785' order by CustomOrder");

	
	foreach ($sliders as $slide) {
		unset($s);
		$s['name'] = $slide['Name'];
		list($s['desc'], $s['pagin']) = array_map('trim', explode("|", $slide['Description']));
		if (!$s['pagin']) $s['pagin'] = $s['desc'];
		$s['small_img'] = $dir_prefix . $slide['smallURL'];
		$s['big_img'] = $dir_prefix . $slide['bigURL'];
		if ($slide['Link'] != "")
			$s['href'] = getRealLinkURL($slide['Link']);
		$ss[] = $s;
	}
	
	$res['firstSlideBig'] = $dir_prefix . $sliders[0]['bigURL'];
	$res['firstSlideSmall'] = $dir_prefix . $sliders[0]['smallURL'];
	$res['slides'] = $ss;

	return parseTemplateFromFile(dir_prefix."engine/templates/mainslider.htm", $res);
}

//===============================================================
function processPicCategory($what){
  global $p, $dir_prefix;

  $catID = str_replace("#", "", $what[0]);
  $pageLimit = str_replace("#", "", $what[1]);
  $rowLimit = str_replace("#", "", $what[2]);
  $showLabels = str_replace("#", "", $what[3]); 

  if($showLabels == "" || $showLabels == "0") $showLabels=false;
  else $showLabels=true;
  if($rowLimit < 1) $rowLimit = 3;

	list($images, $totalImages) = getPictures($catID, $pageLimit);

  if(count($images) <1) return "";

	$res['pagination'] = generatePagination($totalImages, $p, $pageLimit);

	foreach ($images as $image) {
		unset($img);
		$img['Name'] = $image['Name'];
		$img['smallURL'] = $dir_prefix . $image['smallURL'];
		$img['bigURL'] = $dir_prefix . $image['bigURL'];
		if ($showLabels) $img['isLabel'] = true;
		$imgs[] = $img;
	}
  $res['photos'] = $imgs;

	$res['columns'] = $rowLimit;

  return parseTemplate(tplFromFile(dir_prefix."engine/templates/photos.htm"), $res);
}

//===============================================================
function getPictures($catID, $pageLimit) {
	global $db, $p;

	if ($p < 1) $p = 1;
	$from = ($p - 1) * $pageLimit;
	if ($from < 0) $from = 0;
	$limit = "LIMIT ".$from.", ".$pageLimit;

	$images = $db->getData("select * from Images where Category='" . intval($catID) . "' order by CustomOrder, Name " . $limit);

	$totaImages = $db->getData("select count(__id) as cnt from Images where Category='" . intval($catID) . "'")[0]['cnt'];

	$res[] = $images;
	$res[] = $totaImages;

	return $res;
}