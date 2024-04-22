<?php

$pageLimitNews = 20;

function processNews($el) {
  global $c, $dir;
	
  if($dir!="") {
    $c = getIDFromURL("News", $dir);
    if(!$c) headerNotFound();
  }
  if($c>0) 
    return getNewsDetails($c);
  else 
    return getNewsList($el);
} 

//===============================================================
function getNewsList(){
  global $db, $dir_prefix, $ent, $year, $month, $day, $monthArr, $monthArr1, $p, $Category, $specialStyles;

	$specialStyles = false;

  $ent['news'] = getNews("", 0, $month, $year, $day, $p);
	
  if(count($ent['news'])<1) 
    headerNotFound();

	$categories = getSystemVariable($db, 'news_categories', null, false, true);

	$categoriesBtns = [];

	$allNewsBtn['name'] = 'Все новости';
	$allNewsBtn['link'] = $dir_prefix.'news/';
	if (!$Category) {
		$allNewsBtn['class'] = 'btn_submit';
	} else {
		$allNewsBtn['class'] = 'btn_green';
	}

	$categoriesBtns[] = $allNewsBtn;
	
	foreach ($categories as $category) {
		unset($c);
		$c['name'] = trim($category);
		$c['link'] = $dir_prefix.'news/?Category='.urlencode($c['name']);
		if (trim($c['name']) === trim($Category)) {
			$c['class'] = 'btn_submit';
		} else {
			$c['class'] = 'btn_green';
		}
		$categoriesBtns[] = $c;
	}

	$ent['categories'] = $categoriesBtns;

  return parseTemplate(tplFromFile(dir_prefix."engine/templates/news.htm"), $ent);
}

//===============================================================
function getNewsDetails($id){
  global $ent, $news_per_column, $location_code, $group, $MetaTitle, $MetaKeywords, $MetaDescription, $altHeader;
  
  $location_code = "news:".$id;
  
  $news = getNews($id);
  $ent = array_merge($ent, $news);

  $altHeader = ($news['altHeader']!="") ? $news['altHeader'] : $news['Header'];
  $MetaTitle = ($news['MetaTitle']!="") ? $news['MetaTitle'] : $news['Header'];
  $MetaKeywords = ($news['MetaKeywords']!="") ? $news['MetaKeywords'] : $news['Header'];
  $MetaDescription = ($news['MetaDescription']!="")?$news['MetaDescription'] : $news['Header'];
  
	displayNewsBreadcrumbs($news['Header']);

  return parseTemplate(tplFromFile(dir_prefix."engine/templates/news_details.htm"), $ent);
}

//===============================================================
function displayNewsSlider(){
  global $db, $dir_prefix;

	$news = $db->getData("select * from News where DateTime <= '".timeStr()."' and Category='Новости МСЧ-9' order by DateTime DESC");

	foreach ($news as $new) {
		unset($n);
		$date = processDate($new['DateTime']);
		$n['date'] = twoDigits($date['dd']) . "." . twoDigits($date['mm']) . "." . $date['yy'];
		$n['dateFormat'] = $date['yy'] . "-" . twoDigits($date['mm']) . "-" . twoDigits($date['dd']);
		$n['name'] = $new['Header'];
		$n['desc'] = $new['Brief'];
		$n['img']  = $new['smallImage'] ? $dir_prefix . $new['smallImage'] : $dir_prefix . "images/img/plug.jpg";
		$n['href'] = getRealLinkURL('news:'.$new['__id']);
		$nn[] = $n;
	}

	$res['dir_prefix'] = $dir_prefix;
	$res['news'] = $nn;

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/news_for_main.htm"), $res);
}

//===============================================================
function getNewsColumn($suffix=""){
  global $db, $ent, $currentCity, $currentCityName, $monthArr;
  
  $news_per_column = 4;
  
  $news = getNews("", 0, 0, 0, 0, 1, $news_per_column, $currentCity."");

  $last = count($news);
  if($last>$news_per_column) $last = $news_per_column;
  unset($nws);
  unset($rows);
  unset($r);
  
  for ($i=0; $i<$last; $i++){
    $nws[] = $news[$i];
  }
  $ent['news'] = $nws;
  
  if(count($news)<1)
      return "";
  else
    return parseTemplate(tplFromFile(dir_prefix."engine/templates/news_col.htm"), $ent);
}

//===============================================================
function getNews($id="", $startDate=0, $month=0, $year=0, $day=0){
  global $db, $monthArr, $ent, $dir_prefix, $pageLimitNews, $p, $Category;
  
  $where  = " where DateTime <= '".timeStr()."'";
  $desc   = "";
  if($month>0) {
    if($day<1) {
      $sd = timeStr(mktime(0, 0, 0, $month, 1, $year));
      $ed = timeStr(mktime(0, 0, 0, $month+1, 1, $year));
    } else {
      $sd = timeStr(mktime(0, 0, 0, $month, $day, $year));
      $ed = timeStr(mktime(0, 0, 0, $month, $day+1, $year));
    }
    $where .= " AND DateTime>='".$sd."' AND DateTime<'".$ed."'";
  } else if($year>0) {
      $sd = timeStr(mktime(0, 0, 0, 1, 1, $year));
      $ed = timeStr(mktime(0, 0, 0, 12, 31, $year));
      $where .= " AND DateTime>='".$sd."' AND DateTime<'".$ed."'";
  } else $desc = " DESC";
  

  if($startDate>0) {
    $where.=" AND DateTime >= '".timeStr($startDate)."'";
  }
  
  if($Category) $where .= " AND Category='".mysql_real_escape_string($Category)."'";
  if($id>0) $where .= " AND __id='".intval($id)."'";
  else $where .= " order by DateTime DESC, __id".$desc;
  $res    = $db->getData("select count(__id) as cnt from News ".$where);


  $total  = $res[0]['cnt'];
	
  if($p < 1) $p = 1;

  $first = ($p-1) * $pageLimitNews;
  if($first<0) $first = 0;

	$categorySuffix = $Category ? '&Category='.urlencode($Category) : '';
  
  $ent['pagination'] = generatePagination($total, $p, $pageLimitNews, $dir_prefix."news/", $categorySuffix);

  $query = "select * from News".$where." limit ".$first.", ".$pageLimitNews;

  $res       = $db->getData($query);
  unset($news);
  for ($i=0; $i<count($res); $i++){
    unset($n);
    $n['id']              = $res[$i]['__id'];
    $n['Header']          = nl2br($res[$i]['Header']);
    $n['Brief']           = processText($res[$i]['Brief'],1);
    $n['MetaTitle']       = $res[$i]['MetaTitle'];
    $n['MetaKeywords']    = $res[$i]['MetaKeywords'];
    $n['MetaDescription'] = $res[$i]['MetaDescription'];
    $n['altHeader']       = $res[$i]['altHeader'];

		$date = processDate($res[$i]['DateTime']);

    $n['Date']            = $date;
    $n['DateFull']        = processDate($res[$i]['DateTime'], true);
    $n['DateFormat']      = $date['yy'] . "-" . twoDigits($date['mm']) . "-" . twoDigits($date['dd']);
    $n['link']            = getRealLinkURL("news:".$res[$i]['__id']);

		$n['smallImageURL']		= $res[$i]['smallImage'] ? $dir_prefix.$res[$i]['smallImage'] : $dir_prefix."images/img/plug.jpg";
		$n['bigImageURL']		= $res[$i]['bigImage'] ? $dir_prefix.$res[$i]['bigImage'] : $dir_prefix."images/img/plug.jpg";
		if ($res[$i]['bigImage'])
			$n['isImg'] = true;

    if($id != "") 
        $n['Body']        = processText($res[$i]['Body']);

    $news[] = $n;
  }
  
  if(count($news) < 1)
    header("HTTP/1.1 404 Not Found");

  if($id=='') 
    return $news;
  else 
    return $news[0];
}

//===============================================================
function displayNewsBreadcrumbs($currBreadcrumbs = ""){
	global $dir_prefix, $altBreadcrumbs, $id;

	$b['href'] 	= $dir_prefix;
	$b['name'] = getHomePageName();
	$breadcrumbs[] = $b;

	$b['href'] 	= $dir_prefix . "news/";
	$b['name'] = getPageName($id);
	$breadcrumbs[] = $b;

	$res['breadcrumbs'] = $breadcrumbs;
	$res['current'] = $currBreadcrumbs; 

	$altBreadcrumbs = parseTemplate(tplFromFile(dir_prefix."engine/templates/breadcrumbs.htm"), $res);
}