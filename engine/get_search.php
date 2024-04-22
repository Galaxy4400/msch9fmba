<?php

$foundItemsPerPage = 50;
$searchResultWidth = 100;

//===============================================================
function displaySearchForm() {
	global $dir_prefix;
	$res['dir_prefix'] = $dir_prefix;
	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/search.htm"), $res);
}

//===============================================================
function getSearchResults($s) {
	global $db, $page, $p, $ent, $dir_prefix, $foundItemsPerPage;

	$allFoundItems = [];
	$s = trim(strip_tags($s));
	$searchPatterns = explode(" ", mysql_real_escape_string($s));
	getSearchResultHeader();
	//---------------------------------------------------------------
	
	if (strlen($s) < 3) {
		if ($s != "") $ent['message'] = "Строка для поиска слишком короткая!<br><br>";
		$ent['sitemap'] = displaySiteMap()['Body'];
		$page['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/search_none.htm"), $ent);
		$page['specialStyles'] = "_special-styles";
		return $page;
	}
	
	//---------------------------------------------------------------

	// pages
	$query = getSearchQuery($searchPatterns, 'Pages', ['Header', 'Body', 'MetaTitle', 'MetaDescription', 'MetaKeywords'], 'Enabled');
	$FoundItems = $db->getData($query);
	increaseSearchResult($allFoundItems, $FoundItems, 'Header', 'Страница', 'page', ['Header', 'Body'], $searchPatterns);

	// News
	$query = getSearchQuery($searchPatterns, 'News', ['Header', 'Brief', 'Body'], 'Active');
	$FoundItems = $db->getData($query);
	increaseSearchResult($allFoundItems, $FoundItems, 'Header', 'Новость', 'news', ['Header', 'Brief', 'Body'], $searchPatterns);

	//---------------------------------------------------------------

	if (!count($allFoundItems)) {
		$ent['message'] = "Извините, по вашему запросу (<b>" . $s . "</b>) ничего не найдено.<br>Попробуйте воспользоваться картой сайта:<br><br>";
		$ent['sitemap'] = displaySiteMap()['Body'];
		$page['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/search_none.htm"), $ent);
		return $page;
	}
	
	//---------------------------------------------------------------

	$ent['s'] = $s;
	$ent['results'] = getSearchPageResult($allFoundItems);
	$ent['pagination'] = generatePagination(count($allFoundItems), $p, $foundItemsPerPage, $dir_prefix . "search/", "&s=" . $s);

	$page['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/search_rez.htm"), $ent);

	return $page;
}

//===============================================================
function getSearchQuery($searchPatterns, $tableName, $requiredFields = ["Name", "Body"], $activeField = "") {
	$where = [];
	if ($activeField) $where[] = $activeField . "='1'";
	unset($reqPart);
	foreach ($requiredFields as $fieldName) {
		$reqPart[] = "(".$fieldName." like '%" . join("%' AND ".$fieldName." like '%", $searchPatterns) . "%')";
	}
	$where[] = "(".join(" OR ", $reqPart).")";
	$where = "where " . join(" AND ", $where);
	$request = "select * from " . $tableName . " " . $where;
	return $request;
}

//===============================================================
function increaseSearchResult(&$allFoundItems, $foundResult, $nameField, $subjName, $linkSubj, $samplesFields = [], $searchPatterns = []) {
	foreach ($foundResult as $item) {
		unset($r);
		$r['href'] = getRealLinkURL($linkSubj.":" . $item['__id']);
		$r['Header'] = $subjName . " :: " . $item[$nameField];
		$r['Sample'] = getSamples(getSampleHaystack($item, $samplesFields), $searchPatterns);
		$allFoundItems[] = $r;
	}
}

//===============================================================
function getSearchPageResult($collectionSearchResults) {
	global $foundItemsPerPage, $p;

	$totalResults = count($collectionSearchResults);
	$totalPages = floor($totalResults / $foundItemsPerPage);

	if ($totalPages * $foundItemsPerPage < $totalResults) $totalPages++;
	if ($totalPages < 1) $totalPages = 1;
	if ($p < 1) $p = 1;
	if ($p > $totalPages) $p = $totalPages;

	$first = ($p - 1) * $foundItemsPerPage;
	$last = $first + $foundItemsPerPage - 1;

	if ($last >= $totalResults) $last = $totalResults - 1;

	unset($res);
	for ($i = $first; $i <= $last; $i++) {
		$res[] = $collectionSearchResults[$i];
	}

	return $res;
}

//===============================================================
function getSearchResultHeader() {
	global $http, $s, $page;
	displayStaticBreadcrumbs("Поиск");
	if ($http != 404 && $s == "") $page['Header'] = "Карта сайта";
	else if ($http != 404 && $s != "") $page['Header'] = "Результаты поиска";
	else $page['Header'] = "404 - страница не найдена";
}

//===============================================================
function getSampleHaystack($resItem, $fields) {
	unset($r);
	foreach ($fields as $field) {
		$str = removeSmartTags($resItem[$field]);
		$str = $resItem[$field];
		if ($str != "") {
			$r[] = $str;
		}
	}
	return implode(' ... ', $r);
}

//===============================================================
function getSamples($haystack, $searchPatterns) {
	global $searchResultWidth;

	$haystack = strip_tags($haystack);

	$searchPatterns = array_map('mb_strtolower', $searchPatterns);
	usort($searchPatterns, function($a, $b) { return mb_strlen($b) - mb_strlen($a); } );

	$startPos = mb_strposa(mb_strtolower($haystack), $searchPatterns) - intval($searchResultWidth / 2) + mb_strlen($searchPatterns[0]);
	
	if ($startPos < 0) $startPos = 0;
	if ($startPos + $searchResultWidth > mb_strlen($haystack)) $searchResultWidth = mb_strlen($haystack) - $startPos;

	$result = mb_substr($haystack, $startPos, $searchResultWidth);
	
	$patterns = array_map(function($word) { return "/$word/ui"; }, $searchPatterns);
	$replacements = array_map(function($word) { return "<b>$word</b>"; }, $searchPatterns);

	if ($startPos) $result = "..." .$result;
	if ($startPos + $searchResultWidth != mb_strlen($haystack)) $result .= "...";
	
	$result = preg_replace($patterns, $replacements, $result);

	return $result;
}

//===============================================================
function removeSmartTags($str) {
	return preg_replace("/<#.*#>/", "", $str);
}

function mb_strposa($haystack, $needles, $offset = 0) {
	foreach ($needles as $needle) {
		$res = mb_strpos($haystack, $needle, $offset);
		if ($res !== false) return $res;
	}
	return false;
}
