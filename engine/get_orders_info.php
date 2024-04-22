<?php

$noPriceLable = "Нет цены";

function getOrderInfo($orderItems, $user_id = 0) {
	global $db, $dir_prefix, $noPriceLable;

	if (!$orderItems) return false;

	$calculateError = false;

	$uniqueItemsCount = count($orderItems);
	$allItemsCost = 0;
	$totalItemsCount = 0;
	$totalOrderCost = 0;
	$discount = 0; // Тестовое поле
	$shippingCost = 0; // Тестовое поле

	foreach ($orderItems as $i => $orderItem) {
		$query = "
			select
				CatItems.*,
				Images.smallURL as smallImage,
				CatCategories.Name as CatName 
			from 
				CatItems 
			left join 
				CatCategories 
			on 
				CatCategories.__id = CatItems.Category 
			left join 
				( select __id, smallURL, Category, CustomOrder from Images ) Images
			on 
				Images.Category = CatItems.Gallery and Images.Category>0 and Images.CustomOrder=(select min(CustomOrder) from Images where Images.Category=CatItems.Gallery)
			where 
				CatItems.__id='".intval($orderItem['ItemID'])."'
		";
		$item = $db->getData($query)[0];

		$price = $orderItem['Price'] ? $orderItem['Price'] : $item['Price']; // Если корзина - берётся текущая цена товара. Если существующий заказ - то берётся цена на момент заказа.
		$actualPrice = $item['Price']; // Актуальная цена товара в каталоге
		$qty = $orderItem['qty'];
		$multyPrice = $price * $qty;

		$isPrice = true;
		$isActualPrice = true;

		if (!floatval($price)) {
			$calculateError = true;
			$isPrice = false;
		} 
		if (!floatval($actualPrice)) {
			$isActualPrice = false;
		} 

		$item['no'] = $i + 1;
		$item['qty'] = $qty;
		$item['price'] = $isPrice ? $price : $noPriceLable;
		$item['priceFormat'] = $isPrice ? format_price($price) : $noPriceLable;
		$item['multyPrice'] = $isPrice ? $multyPrice : $noPriceLable;
		$item['multyPriceFormat'] = $isPrice ? format_price($multyPrice) : $noPriceLable;
		$item['actualPrice'] = $isActualPrice ? $actualPrice : $noPriceLable;
		$item['actualPriceFormat'] = $isActualPrice ? format_price($actualPrice) : $noPriceLable;
		$item['href'] = getRealLinkURL('pid:'.$orderItem['ItemID']);
		$item['smallImage'] = $item['smallImage'] ? $dir_prefix . $item['smallImage'] : $dir_prefix."images/img/plug.jpg";
		if ($isPrice)
			$item['isPrice'] = true;
		$items[] = $item;

		$totalItemsCount += $qty;
		$allItemsCost += $multyPrice;
	}

	$totalOrderCost = $allItemsCost + $shippingCost; // Тестовое поле

	$res['discount'] = $discount;  // Тестовое поле
	$res['uniqueItemsCount'] = $uniqueItemsCount;
	$res['totalItemsCount'] = $totalItemsCount;
	$res['shippingCost'] = $shippingCost;  // Тестовое поле
	$res['shippingCostFormat'] = format_price($shippingCost);  // Тестовое поле
	$res['allItemsCost'] = $calculateError ? "" : $allItemsCost;
	$res['allItemsCostFormat'] = $calculateError ? "" : format_price($allItemsCost);
	$res['totalOrderCost'] = $calculateError ? "" : $totalOrderCost;
	$res['totalOrderCostFormat'] = $calculateError ? "" : format_price($totalOrderCost);
	$res['items'] = $items;

	if ($calculateError) 
		$res['calculateError'] = true;

	return $res;
}


function getCostInfo($orderInfo) {
	$costInfo = "";
	if($orderInfo['calculateError']) {
		$costInfo = "Общая стоимость заказа потребует уточнения";
	} else {
		$costInfo = "Общая стоимость: ".$orderInfo['totalOrderCostFormat']." руб.";
		if($orderInfo['shippingCost'] > 0){
			$costInfo = "Стоимость товаров: ".$orderInfo['allItemsCostFormat']." руб.";
			$costInfo .= "<br>Cтоимость доставки: ".$orderInfo['shippingCostFormat']." руб.";
			$costInfo .= "<br><h3>К оплате: ".$orderInfo['totalOrderCostFormat']." руб.</h3>";
		}
	}
	return $costInfo;
}


function getCatItemParams($params) {
	$params = explode("\r\n", trim($params));

	foreach ($params as $param) {
		unset($p);
		list($p['name'], $p['value']) = array_map('trim', explode(":", $param));
		$pp[] = $p;
	}

	return $pp;
}



function getCatItemOptions($options) {
	// read options
	$ps = explode("\r\n", trim($options));
	unset($params);

	for ($i = 0; $i < count($ps); $i++) {
		unset($p);
		$param = trim($ps[$i]);
		$pname = trim(substr($param, 0, strpos($param, "(")));
		$pvals = trim(substr($param, strpos($param, "(") + 1));
		$pvals = explode(",", trim(substr($pvals, 0, strpos($pvals, ")"))));

		if ($pname != "" && count($pvals) > 0) {
			$p['param'] = $pname;
			unset($options);

			for ($j = 0; $j < count($pvals); $j++) {
				list($value, $pdiff) = explode("|", trim($pvals[$j]));

				$value = trim($value);
				$pdiff = trim($pdiff);

				unset($o);
				$o['id'] = $j;
				$o['pdiff'] = $pdiff * 1;
				$o['value_noprice'] = $value;
				$o['value'] = $value;
				if ($pdiff != 0) {
					if ($pdiff > 0)
						$o['value'] .= " (+" . str_replace("+", "", $pdiff) . " руб.)";
					else
						$o['value'] .= " (" . $pdiff . " руб.)";
				}

				$options[] = $o;
			}

			$p['options'] = $options;
			$params[] = $p;
		}
	}
	return $params;
}
