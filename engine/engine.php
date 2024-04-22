<?php
// error_reporting(E_ALL);
require(dir_prefix."admin/include/templates.php");

$globalMailTo = getSystemVariable($db, "globalMailTo");

$res = $db->getData("select dt from last_update limit 0,1");

define("last_update", mysqlTimeStamp2Unix($res[0]['dt']));

require(dir_prefix."admin/include/glib.php");
require(dir_prefix."engine/get_sections.php");
require(dir_prefix."engine/get_generic.php");
require(dir_prefix."engine/get_pagination.php");
require(dir_prefix."engine/get_inline.php");
require(dir_prefix."engine/get_images.php");
require(dir_prefix."engine/get_galleries.php");
require(dir_prefix."engine/get_banners.php");
require(dir_prefix."engine/get_forms.php");
require(dir_prefix."engine/get_mail.php");
require(dir_prefix."engine/get_pages.php");
require(dir_prefix."engine/get_menu.php");
require(dir_prefix."engine/get_links.php");
require(dir_prefix."engine/get_search.php");
require(dir_prefix."engine/get_news.php");
// require(dir_prefix."engine/get_brands.php");
// require(dir_prefix."engine/get_shops.php");
// require(dir_prefix."engine/get_cart.php");
// require(dir_prefix."engine/get_orders.php");
require(dir_prefix."engine/get_orders_info.php");
require(dir_prefix."engine/get_catalogue.php");
require(dir_prefix."engine/get_filters.php");
require(dir_prefix."engine/get_users.php");

//only with get_users.php
$ui = getUserInfo();

$separator = parseTemplate(tplFromFile(dir_prefix."engine/templates/separator.htm"), $ent);
$ent['globalSeparator'] = $separator;
$ent['site_name'] = getSystemVariable($db, "site_name");
$ent['admin_title'] = getSystemVariable($db, "admin_title");