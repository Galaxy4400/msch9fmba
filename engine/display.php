<?php
// error_reporting(E_ALL);
require("engine_defs.php");

header("X-Powered-By: Apache\n");
header("Last-Modified: " . gmdate('D, d M Y H:i:s', time() - 3600 - $id * 60) . " GMT\n");
header("Expires: " . gmdate('D, d M Y H:i:s', time() + 24 * 3600 + $id) . " GMT\n");
header("Cache-Control: max-age=1, must-revalidate\n");
header("Content-type: text/html; charset=UTF-8");

define('version', 62);


$dp = "";
for ($i = 0; $i < $level; $i++)
	$dp .= "../";


$HTTP_HOST = $_SERVER['HTTP_HOST'];

define("dir_prefix", $dp);
$dir_prefix = $dp;
if ($dir_prefix == "") $dir_prefix = "./";

if($_REQUEST['moredp']>0)
  $dir_prefix .= "../";

require(dir_prefix . "admin/include/defs.php");
require(dir_prefix . "admin/include/dbconnect.php");

$ent['dir_prefix'] 	= $dir_prefix;
$db = new CDatabase();

require(dir_prefix . "engine/engine.php");

global $id, $l;

if ($id < 1)
	$id = page_index_rus;
$tp = getTopParent($id);

if ($tp == page_index_rus || $l == "0") {
	define("language", 0);
	define("page_index", page_index_rus);
	define("site_root", "");
	define("language_suffix", "");
} else {
	define("language", 1);
	define("page_index", page_index_eng);
	define("site_root", "eng/");
	define("language_suffix", "_eng");
}

$url = $_SERVER['REQUEST_URI'];

///////////////////////////////
$root_path = substr(strstr(strstr($url, 'mdetstva_new'), '/'), 1);
// $root_path = $url;
///////////////////////////////

if (!isset($levels)) {
	$levels = substr_count($root_path, '/');
}

define('ROOT_PATH', $root_path);
define('LEVELS', $levels);

$ent['root_path'] = $root_path;
$ent['levels'] = $levels;

define("site_root", "");

$hasAside = true;

$site_root 			= site_root;
$ent['site_root'] 	= $site_root;

define("site_name", getSystemVariable($db, "site_name"));/*.language_suffix*/
define("default_title", getSystemVariable($db, "default_title"));/*.language_suffix*/

unset($location_code);

$location_code[] = "pages:" . $id;

$windowed = false;

if ($print == 1)
	$windowed = true;

if (language != 1) {
	$monthArr_short = array('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек');
	$monthArr = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
	define("page_index", page_index_rus);
} else {
	$monthArr_short = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	$monthArr = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	define("page_index", page_index_eng);
}

$specialStyles = true;
$blind = $_COOKIE['blind'] ? true : false;

if ($id == page_index && $action == "" && $cid == "" && $pid == "" && $brand == "" && $special == "")
	$is_index = true;
else
	$is_index = false;

$oldAction 	= $action;



switch ($action) {
	//BASE FUNCTIONS
	case "search":
		$page = getSearchResults($s, $p);
		break;

	case "404":
		$page = display404();
		break;

	case "doLogin":
		$page = doLogin(false, $_REQUEST['redirect_to_cabinet']);
		break;

	case "cabinet":
		$page = displayCabinet();
		break;

	case "logout":
		$page = doLogout();
		break;

	case "addFeedback":
		die(addFeedback($_REQUEST['doctor'], $_REQUEST['Scope'], $_REQUEST['Feedback'], $_REQUEST['FullName'], $_REQUEST['Email']));
		break;

	// case "news":
	// 	$page = getNewsList();
	// 	break;

	default:
		if ($id < 1)
			$id = page_index;
		$page = getPage($id);
		break;
}


if($action=="doPostForm") 
	$page['Body'] = doProcessForm($formid);

if ($is_index)
	$pageTitle = getSystemVariable($db, "default_title");
else
	$pageTitle = $page['Header'];

if ($page['MetaTitle'] != "")
	$pageTitle = $page['MetaTitle'];

if ($altTitle != "")
	$pageTitle = $altTitle;

if ($page['altHeader'] != "" && $altHeader == "")
	$altHeader = $page['altHeader'];

if ($altHeader != "")
	$page['Header'] = $altHeader;

if ($altMetaDescription != "")
	$page['MetaDescription'] = $altMetaDescription;

if ($altMetaKeywords != "")
	$page['MetaKeywords'] = $altMetaKeywords;

if ($page['MetaKeywords'] != "")
	$kws = $page['MetaKeywords'];

if ($page['MetaDescription'] != "")
	$desc = $page['MetaDescription'];
else
	$desc = $pageTitle;
if ($altBreadcrumbs)
	$page['breadcrumbs'] = $altBreadcrumbs;

$kws = trim($kws);

if ($windowed)
	require("display_print.php");
else
	require("display_screen.php");

$db->close();
