<?php

DEFINE("location_root", 1);
DEFINE("IS_TESTING", false);
DEFINE("home_prefix", "index.php");
DEFINE("page_index_rus", 1);
DEFINE("page_index_eng", 6);
DEFINE("num_headers", 3);
DEFINE("image_desc_bgcolor", "#f2f2f2");
DEFINE("entries_per_page", 20);

function validateInput() { // validate input against malicious activity
	if($_REQUEST['dir_prefix']!=""||$_REQUEST['level']!="") 
		return false;
	global $id, $action;
	if(is_array($id)||is_array($action)) return false;
	if(strstr($action, "../")||strstr($action, chr(0))) return false;
	if($id!=""&&!is_numeric($id)) return false;
	if($_REQUEST['c']!=""&&!is_numeric($_REQUEST['c'])) return false;
	if($_REQUEST['cid']!=""&&!is_numeric($_REQUEST['cid'])&&$_REQUEST['cid']!="special"&&$_REQUEST['cid']!="new") return false;
	if($_REQUEST['pid']!=""&&!is_numeric($_REQUEST['pid'])) return false;
	
	if($action==""&&!is_numeric($id)) return false;
	
	return true;
}

if(!validateInput()) die("Request blocked.");