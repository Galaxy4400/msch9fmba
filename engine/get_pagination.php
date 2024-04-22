<?php

//=======================================================================
function generatePagination($totalItems, $page, $pageLimit, $dir = "./", $suffix = "", $showArrows = false, $adjacentBtnsCount = 1) {
	global $is_admin;

	$root = ($is_admin == "1") ? ("./home.php") : $dir;
	
	$totalPages = floor($totalItems / $pageLimit);
	$prev = $page - 1;
	$next = $page + 1;
	$leftCut = false;
	$rightCut = false;

	if ($totalPages * $pageLimit < $totalItems) $totalPages++;
	if ($totalPages < 1) $totalPages = 1;
	if ($page < 1) $page = 1;
	if ($page > $totalPages) $page = $totalPages;
	if ($prev < 1) $prev = 1;
	if ($next > $totalPages) $next = $totalPages;
	if ($$adjacentBtnsCount < 0) $adjacentBtnsCount = 0;

	$adjacentBtnsLeft = $page - $adjacentBtnsCount - 1;
	$adjacentBtnsRight = $page + $adjacentBtnsCount;

	if ($adjacentBtnsLeft < 0) $adjacentBtnsRight += abs($adjacentBtnsLeft);
	if ($totalPages - $adjacentBtnsRight < 0) $adjacentBtnsLeft -= abs($totalPages - $adjacentBtnsRight);

	if ($adjacentBtnsLeft > 1) $leftCut = true;
	if ($adjacentBtnsRight < $totalPages - 1) $rightCut = true;

	for ($i = 1; $i <= $totalPages; $i++) {
		$inRange = ($i > $adjacentBtnsLeft && $i <= $adjacentBtnsRight) ? true : false;
		$showFirst = ($adjacentBtnsLeft >= 1 && $i == 1) ? true : false;
		$showLast = ($adjacentBtnsRight < $totalPages && $i == $totalPages) ? true : false;
		
		if ($inRange || $showFirst || $showLast) {
			unset($p);
			$p['name'] 		= $i;
			$p['link'] 		= $root."?p=".$i.$suffix;
			$p['class'] 	= ($i == $page) ? "_active" : "";
			$paggingItems[] = $p;
		}
	}

	$prevLink = $root."?p=".$prev.$suffix;
	$nextLink = $root."?p=".$next.$suffix;

	if ($leftCut) {
		unset($pg);
		$p['name'] 		= '...';
		$p['link'] 		= $prevLink;
		$p['class'] 	= "";
		$pg[] = $p;
		array_splice($paggingItems, 1, 0, $pg);
	}

	if ($rightCut) {
		unset($pg);
		$p['name'] 		= '...';
		$p['link'] 		= $nextLink;
		$p['class'] 	= "";
		$pg[] = $p;
		array_splice($paggingItems, -1, 0, $pg);
	}
	
	if ($showArrows) {
		$ent['prev'] = $prevLink;
		$ent['next'] = $nextLink;
	}
	$ent['paggination'] = $paggingItems;

	$res = parseTemplate(tplFromFile(dir_prefix."engine/templates/paggination.htm"), $ent);

	if(count($paggingItems)>1) return $res;
}