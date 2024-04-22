<?php

//===============================================================
function displayBanners($template = "banners", $limit = 999){
  global $db, $ent, $location_code, $dir_prefix;

	$pages = array_map('mysql_real_escape_string', $location_code);

	$pgStr = join("|%' OR Location like '%|", $pages);
	$query = "select * from Banners where Active=1 AND (Location like '%|".$pgStr."|%' or Location like '%||%' or Location is NULL)";

	$banners = $db->getData($query);

	unset($show);
	foreach ($banners as $banner) {
		if($banner['MaxExposures'] == 0 || $banner['Exposures'] < $banner['MaxExposures']) {
      unset($b);
      $b['id'] = $banner['__id'];
      $b['title'] = nl2br($banner['Title']);
      $b['content'] = $banner['Content'];
      $b['exposures'] = $banner['Exposures'];
      $b['img'] = $dir_prefix . $banner['Image'];
      $b['CustomOrder'] = $banner['CustomOrder'];
			if (strpos($banner['Link'], 'http://') !== false || strpos($banner['Link'], 'https://') !== false) {
				$b['target_blank'] = "target='_blank'";
				$b['link'] = $banner['Link'];
			} else {
				$b['link'] = getRealLinkURL($banner['Link']);
			}
      $show[] = $b;
    }
	}

	shuffle($show);

	$res['banners'] = array_slice($show, 0, $limit);

	$db->query("update Banners set Exposures=Exposures+1 where (__id='".join("' OR __id='", array_column($show, 'id'))."')");

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/banners.htm"), $res);
}
