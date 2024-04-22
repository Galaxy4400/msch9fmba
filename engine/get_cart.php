<?php

$cartInterval = 30 * 24 * 3600; // 30 days

function displayCart() {
	global $db, $cookie_cartID, $ui, $dir_prefix, $special_styles;

	$special_styles = false;
	$title = "Корзина заказа";
	$page['Header'] = $title;
	displayStaticBreadcrumbs($title);

	$total = 0;
	$count = 0;

	if ($cookie_cartID != "") {
		$items = $db->getData("select * from CartItems where CartID = " . intval($cookie_cartID));
		$orderInfo = getOrderInfo($items, $ui);
		$count = $orderInfo['uniqueItemsCount'];
		$total = $orderInfo['allItemsCost'];
		$res['items'] = $orderInfo['items'];
		$res['totalCost'] = $orderInfo['totalOrderCostFormat'];
		$res['shippingCost'] = $orderInfo['shippingCostFormat'];
		$res['itemsCount'] = $orderInfo['uniqueItemsCount'];
	}
	$res['dir_prefix'] = $dir_prefix;

	if ($total > 0 || $count > 0)
		$page['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/cart.htm"), $res);
	else
		$page['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/cart_empty.htm"), $res);

	cleanOldCarts();
	

	return $page;
}

//===============================================================
function doAddToCart($cartID, $itemID, $qty) {

	if (!$itemID && !$qty) return;
	
	$cartID = checkCartExsist($cartID);

	if (checkCartItemExist($cartID, $itemID)) {
		cartItemUpdate($cartID, $itemID, $qty);
	} else {
		cartItemInsert($cartID, $itemID, $qty);
	}

	doSaveCart($cartID);
}

//===============================================================
function changeCartItemCount($cartID, $itemID, $qty) {
	global $db;

	$data['qty'] = intval($qty);
	$where = "CartID = ".intval($cartID)." and ItemID = ".intval($itemID);
	$db->my_update('CartItems', $where, $data);

	$cartItems = $db->getData("select * from CartItems where CartID = " . intval($cartID));
	$orderInfo = getOrderInfo($cartItems);

	return $orderInfo;
}

//===============================================================
function cartItemInsert($cartID, $itemID, $qty) {
	global $db;

	$data['CartID'] = intval($cartID);
	$data['ItemID'] = intval($itemID);
	$data['qty'] = intval($qty);

	$db->my_insert('CartItems', $data);
}

//===============================================================
function cartItemUpdate($cartID, $itemID, $qty) {
	global $db;
	$where = "CartID = ".intval($cartID)." and ItemID = ".intval($itemID);
	$db->query("update CartItems set qty = qty + " . intval($qty) . " where " . $where);
}

//===============================================================
function checkCartExsist($cartID) {
	global $db, $__ctime, $HTTP_HOST, $cartInterval;

	$__ctime 	= timeStr();
	
	$newCart = false;
	if ($cartID) $newCart = true;
	else {
		$res = $db->query("select * from Cart where __id='" . intval($cartID) . "'");
		if (!$res) $newCart = true;
	}

	if (!$newCart) {
		$cookie_cartID = $db->insert("Cart");
		setcookie("cookie_cartID", $cookie_cartID, time() + $cartInterval, "/", $HTTP_HOST);

		return $cookie_cartID;
	}

	return $cartID;
}

//===============================================================
function checkCartItemExist($cartID, $itemID) {
	global $db;
	$res = $db->getData("select * from CartItems where CartID = '".$cartID."' and ItemID = '".$itemID."'");
	return $res ? true : false;
}

//===============================================================
function doSaveCart($cartID) {
	global $db, $__ctime;
	$__ctime 	= timeStr();
	$db->query("update Cart set __ctime='" . $__ctime . "' where __id='" . intval($cartID) . "'");
}

//===============================================================
function getCountItemsInCart() {
	global $cookie_cartID, $db, $ui;
	$items = $db->getData("select * from CartItems where CartID = " . intval($cookie_cartID));
	$orderInfo = getOrderInfo($items, $ui);
	$res['count'] = $orderInfo ? $orderInfo['uniqueItemsCount'] : 0;
	$res['totalCount'] = $orderInfo ? $orderInfo['totalItemsCount'] : 0;
	$res['totalCost'] = $orderInfo ? $orderInfo['allItemsCostFormat'] : 0;
	return $res;
}

//===============================================================
function doDeleteFromCart($cartID, $itemID) {
	global $db;

	$db->query("delete from CartItems where CartID = ".intval($cartID)." and ItemID = ".intval($itemID));

	$cartItems = $db->getData("select * from CartItems where CartID = " . intval($cartID));
	$orderInfo = getOrderInfo($cartItems);

	if (!$orderInfo) return displayCart();

	return $orderInfo;
}

//===============================================================
function cleanOldCarts() {
	global $db, $cartInterval;
	
	$now = time();
	$carts = $db->getData("select * from Cart");
	
	foreach ($carts as $cart) {
		$cartTime =	strtotime($cart['__ctime']) + $cartInterval;
		if ($now > $cartTime) {
			$db->query("delete from CartItems where CartID = " . intval($cart['__id']));
			$db->query("delete from Cart where __id = " . intval($cart['__id']));
		}
	}
}
