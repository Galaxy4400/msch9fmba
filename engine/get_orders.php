<?php

//===============================================================
function displayOrder(){
	global $subAction, $cookie_cartID, $special_styles;

	$special_styles = false;

	switch ($subAction) {
		case 'process': 
			$pg = doProcessOrder($cookie_cartID);
			break;

		default:
			$pg = displayOrderPage($cookie_cartID);
			break;
	}
		
	return $pg;
}

//===============================================================
function displayOrderPage($cartID) {
	global $db, $dir_prefix, $ui, $deliveryMethods, $paymentMethods;

	$title = "Оформление заказа";
	$pg['Header'] = $title;
	displayStaticBreadcrumbs($title);

	$total = 0;
	$count = 0;

	if ($cartID != "") {
		$items = $db->getData("select * from CartItems where CartID = " . intval($cartID));
		$orderInfo = getOrderInfo($items, $ui);
		$count = $orderInfo['uniqueItemsCount'];
		$total = $orderInfo['allItemsCost'];
		$res['count'] = $count;
		$res['productWord'] = sufixFromNumber($count, array('товар', 'товара', 'товаров'));
		$res['totalCost'] = $orderInfo['totalOrderCostFormat'];
		$res['items'] = $orderInfo['items'];
	}
	$res['dir_prefix'] = $dir_prefix;

	if ($total > 0 || $count > 0) {
		$res['deliveryMethods'] = radioListFromArray($deliveryMethods);
		$res['paymentMethods'] = radioListFromArray($paymentMethods);
		if ($ui) {
			$res = my_array_merge($res, $ui);
			$res['class'] = ($ui['isFirm'] == 1) ? "_active" : "";
		} else {
			$res['unreg'] = true;
		}
		$res['form'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/order_form.htm"), $res);
		$pg['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/order.htm"), $res);
	} else {
		$pg['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/cart_empty.htm"), $res);
	}

	return $pg;
}

//===============================================================
function doProcessOrder($cartID){
	global $db, $ui, $dir_prefix, $Register, $ui;

	$title = "Оформление заказа";
	$pg['Header'] = $title;
	displayStaticBreadcrumbs($title);

	$cartItems = $db->getData("select * from CartItems where CartID = '".intval($cartID)."'");
	$orderInfo = getOrderInfo($cartItems, $ui['UserID']);
	$items = $orderInfo['items'];
	$itemsCount = $orderInfo['uniqueItemsCount'];

	$res['dir_prefix'] = $dir_prefix;
	$res['items'] = $items;
	$res = my_array_merge($res, populateFromPost());

	if (!$itemsCount) {
		$pg['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/cart_empty.htm"), $res);
		return $pg;
	}

	if ($Register) {
		$res['reg_message'] = doAutoUserRegister();
	}

	$id = createOrder($orderInfo, $cartID);
	
	processOrderEmails($id, $orderInfo);

	$res['order_done_body']	= getSystemVariable($db, "order_done_body");
	$body = parseTemplate(tplFromFile(dir_prefix."engine/templates/order_done.htm"), $res);
	
	$pg['Body'] = $body;

	return $pg;
}

//===============================================================
function createOrder($orderInfo, $cartID) {
	global $db, $HTTP_HOST, $Date, $Name, $Email, $Date, $Total, $UserID, $ui;

	$Name = $db->protectXSS($Name);
	$Email = $db->protectXSS($Email);
	$Date = timeStr();
	$Total = $orderInfo['totalOrderCost'];
	$UserID = $ui['__id'];

	$id = $db->insert("Orders");
	foreach ($orderInfo['items'] as $item) {
		unset($data);
		$data['OrderID'] = $id;
		$data['ItemID'] = $item['__id'];
		$data['qty'] = $item['qty'];
		$data['Price'] = $item['Price'];
		$db->my_insert('OrderItems', $data);
	}

	my_setCookie("cookie_cartID", 0, time() - 1, "/", $HTTP_HOST);
	$db->query("delete from Cart where __id = '" .intval($cartID). "'");
	$db->query("delete from CartItems where CartID = '" .intval($cartID). "'");

	return $id;
}

//===============================================================
function processOrderEmails($orderID, $orderInfo) {
	global $db, $globalMailTo, $Name, $Phone, $Email, $Comments;

	$res['id'] = $orderID;
	$res['items'] = $orderInfo['items'];
	$res['total'] = $orderInfo['totalOrderCost'];
	$res['totalFormat'] = $orderInfo['totalOrderCostFormat'];
	$res['DateStr'] = fmtDate(timeStr());
	$res['Name'] = $Name;
	$res['Phone'] = $Phone;
	$res['Email'] = $Email;
	$res['Comments'] = $Comments;
	$res['siteName'] = getSystemVariable($db, "site_name");
	$res['orderCostInfo'] = getCostInfo($orderInfo);

	// Письмо админу
	$mailFrom = $Email;
	$mailSubject = "Заказ на сайте ".getSystemVariable($db, "admin_title");
	$mailBody = "<html><body>".getSystemVariable($db, "order_admin_email", $res)."</body></html>";
	doMail($globalMailTo, $mailSubject, $mailBody, $mailFrom, $Name);

	// Письмо покупателю
	$mailFrom = $globalMailTo;
	$mailFromName = getSystemVariable($db, "site_name");
	$mailTo = $Email;
	$mailSubject = getSystemVariable($db, "order_done_email_subject", $res);
	$mailBody = "<html><body>".getSystemVariable($db, "order_done_email_body", $res)."</body></html>";
	doMail($mailTo, $mailSubject, $mailBody, $mailFrom, $mailFromName);
}

//===============================================================
function BackURL(){
	global $dir_prefix;
	$ent['messageTitle'] = "Заказ успешно сформирован";
	$ent['message'] = "Оплатить заказ, отслеживать его статус и скачать архив всегда можно в <a href='".$dir_prefix.".?action=cabinet'>личном кабинете</a>.";
	$ent['back'] = "<a class='btn btn-primary' href='".$dir_prefix.".?action=cabinet'>Перейти в личный кабинет</a>";
	$page['Body'] = parseTemplate(tplFromFile(dir_prefix."engine/templates/ok.htm"), $ent);
	$page['Header'] = "Статус оплаты заказа";
	return $page;
}

//===============================================================
function showBill(){
	global $db, $id, $ent;
	$res = $db->getData("select * from Orders where __id='".mysql_real_escape_string($id)."'");
	$res = $res[0];
	$items = explode("|", $res['Items']);
	$date = fmtDate($res['Date'], false);
	$count = count($items);
	$total = 0;
	for ($i=0; $i<$count; $i++){
		list($pos, $qty) = explode("-", $items[$i]);
		$res1 = mysql_fetch_array($db->query("select __id, Price from CatItems where __id='".mysql_real_escape_string($pos)."'"));
		$price = $res1['Price'];
		$cost = $price*$qty;
		$total+=$cost;
	}
	$ent['id'] = $id;
	$ent['total_rur'] = str_replace(".", ",", number_format($total*getSystemVariable($db, "exchange_rate"), 2));
	$ent['date'] = $date;
	$ent['Name'] = $res['Name'];
	$ent['Address'] = nl2br($res['Address']);
	$ent['City'] = $res['City'];
	echo parseTemplate(tplFromFile(dir_prefix."engine/templates/sberbank.htm"), $ent);
	die();
}

//======================================================================================================
//Обработка оплаты
//======================================================================================================
function checkout($doMail = false){
	global $db, $ent, $dir_prefix;
	
	$PaymentSystem = "IntellectMoney";
	
	
	// Секретный ключ
						   
	$secretKey					= getSystemVariable($db, "secretKey");
	
	// чтение полученных параметров
	$in_eshopId 				= $_REQUEST["eshopId"];
	$in_orderId 				= $_REQUEST["orderId"];
	$in_serviceName 			= $_REQUEST["serviceName"];
	$in_eshopAccount 			= $_REQUEST["eshopAccount"];
	$in_recipientAmount 		= $_REQUEST["recipientAmount"];
	$in_recipientCurrency 		= $_REQUEST["recipientCurrency"];
	$in_paymentStatus 			= $_REQUEST["paymentStatus"];
	$in_userName 				= $_REQUEST["userName"];
	$in_userEmail 				= $_REQUEST["userEmail"];
	$in_paymentData 			= $_REQUEST["paymentData"];
	$in_secretKey 				= $_REQUEST["secretKey"];		// нужен для проверки по HTTPS хотя в любом случае проверка по
												//контрольной подписи предпочтительна, по этому просто игнорируем его.
	$in_hash 					= strtoupper($_REQUEST["hash"]);	// контрольная подпись со стороны IntellectMoney - основной способ
	
	
	
	$for_hash = 
		$in_eshopId."::".
		$in_orderId."::".
		$in_serviceName."::".
		$in_eshopAccount."::".
		$in_recipientAmount."::".
		$in_recipientCurrency."::".
		$in_paymentStatus."::".
		$in_userName."::".
		$in_userEmail."::".
		$in_paymentData."::".
		$secretKey; // Очень ВАЖНО проверять подпись используя свой секретный ключ, а не тот что пришел в запросе
	// Получаем наш вариант контрольной подписи
	$my_hash = strtoupper(md5($for_hash));
	
	
	// проверка корректности подписи нашей подписи и подписи пришедшей в запросе. Если они различаются, значит прошла подменна данных в ходе
	// передачи данных от сервера IntellectMoney до вашего сервера.
	
	//echofile("my_hash= ".$my_hash, "_checkout");
	//echofile("in_hash= ".$in_hash, "_checkout");
	//echofile("----------------", "_checkout");
	
	if ($my_hash == $in_hash)
		$checksum = true;
	else
		$checksum = false;
	
	
	// ! ВАЖНО проверить сумму платежа и валюту по данным, хранимым в вашей базе данных по номеру заказа
	// и если сумма или валюта отличаются от тех что сгенерировали вы задайте переменной checksum значение false
	// здесь ваш код для сравнения значения в ваше базе и информации принятой от IntellectMoney
	
	
	if($checksum){
		$order 	= $db->getData("select * from Orders where __id='".intval($in_orderId)."'");
		$UserID = (int)$order[0]['UserID'];
		//if($order=="" || ($in_recipientCurrency!="RUR" && $in_recipientCurrency!="RUB" ) || ($order[0]['Total']*1 != $in_recipientAmount*1) || $order[0]['Status']==1) //Status - не уверен что 
		if($order=="" || ($in_recipientCurrency!=getSystemVariable($db, "im_currency") ) || ($order[0]['Total']*1 != $in_recipientAmount*1) || $order[0]['Status']==1) //Status - не уверен что 
			$checksum = false;
		
	}
	//echofile("checksum=".$checksum, "_checkout"); 
	//echofile($order."||".$in_recipientCurrency."||".($order[0]['Total']*1)."||".($in_recipientAmount*1)."||".$order[0]['Status'], "_checkout");
	
	if (!$checksum)
		return "ERROR";
	
	
	
	//если все успешно то смотрим какой статус у платежа.
	
	// Внутренние операции по обработке платежа
	if ($in_paymentStatus == 3){
		// Платеж принят на обработку
		// ВНИМАНИЕ это не означает что денги от клиента получены
		// но это означает что процес создания счета прошел успешно
		// здесь ваш код по обработке этого статуса. Рекомендуется просто 
		// принимать к сведенью, но НИ В КОЕМ РОДЕ не считать данный
		// статус как результат совершения платежа
		$db->getData("update Orders set IntellectMoneyStatus='1' where __id='".intval($in_orderId)."'");
		//return BackURL();
		return "OK";
	}
	
	if ($in_paymentStatus == 5){
		//количество отчислений считаем ДО того, как сменили статус, на всякий случай
		
		// Платеж прошел, можно отгружать товар / оказывать услугу
		// здесь ваш код по обработке этого статуса
		$db->query("update Orders set Status = '1' where __id='".intval($in_orderId)."'");
		
		return "OK";
	} else {
		return "ERROR";
	}
}


function paymentSuccess($OrderID) {
	global $dir_prefix, $ui;
	
	$OrderID = intval($OrderID); 
	
	$ent['messageTitle']		= "Товар оплачен";
	
	$ent['back']				= "<a class='btn btn-primary' href='".$dir_prefix.".?action=cabinet'>Перейти в кабинет</a>";
	
	$page['Body']  				= parseTemplate(tplFromFile(dir_prefix."engine/templates/ok.htm"), $ent);
	$page['Header']  			= "Статус оплаты заказа";
	return $page;
}


function paymentFailed() {
	global $dir_prefix;
	
	$ent['messageTitle']	= "Товар не оплачен";
	$ent['message']			= "Заказы всегда можно оплатить в <a href='".$dir_prefix.".?action=cabinet'>личном кабинете</a>.";
	$ent['back']			= "<a class='btn btn-primary' href='".$dir_prefix.".?action=cabinet'>Перейти в кабинет</a>";
	
	
	$page['Body']  			= parseTemplate(tplFromFile(dir_prefix."engine/templates/error.htm"), $ent);
	$page['Header']  		= "Статус оплаты заказа";
	return $page;
}

//===============================================================
function getPaymentLink($OrderID){
	global $ui, $db;
	$OrderID 	= intval($OrderID);
	$res 		= $db->getData("select Total from Orders where __id='".$OrderID."'");// and UserID='".intval($ui['UserID'])."'
	if($res=="")
		return "";
	
	$secretKey			= getSystemVariable($db, "secretKey");
	$shop_id 			= getSystemVariable($db, "shop_id");
	$serviceName 		= "Оплата заказа №".$OrderID." на сайтe ".getSystemVariable($db, "site_name");
	$recipientAmount 	= $res[0]['Total'];
	$recipientCurrency 	= getSystemVariable($db, "im_currency");//"TST";//"RUR";
	
	$link[] = "eshopId=".$shop_id;
	$link[] = "serviceName=".$serviceName;
	$link[] = "recipientAmount=".$recipientAmount;
	$link[] = "recipientCurrency=".$recipientCurrency;
	$link[] = "SuccessURL=http://server2.webisgroup.ru/shopofsites.ru/?PaymentS=".$OrderID;//"successUrl=http://xversion.ru/?action=PaymentS";
	$link[] = "FailUrl=http://server2.webisgroup.ru/shopofsites.ru/?action=PaymentF";//"failUrl=http://xversion.ru/?action=PaymentF";
	$link[] = "hash=".md5($shop_id."::".$OrderID."::".$serviceName."::".$recipientAmount."::".$recipientCurrency."::".$secretKey);
	
	$result = "https://merchant.intellectmoney.ru/ru/?orderId=".$OrderID."&".join("&", $link);
	return $result;
}

//===============================================================

function getOrderLoginPage($prefix=""){
	global $ent;
	unset($pg);
	$pg['Header'] 		= "Для зарегистрированных пользователей.";
	$pg['PlainHeader'] 	= $pg['Header'];
	$pg['Body'] 		= $prefix.parseTemplate(tplFromFile(dir_prefix."engine/templates/order_unreg.htm"), $ent);
	return $pg;
}

//===============================================================
function getOrderBreadcrumbs($currBreadcrumbs){
	global $dir_prefix;

	$b['href'] 	= $dir_prefix;
	$b['name'] = "Главная";
	$breadcrumbs[] = $b;

	$res['breadcrumbs'] = $breadcrumbs;
	$res['current'] = $currBreadcrumbs; 

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/breadcrumbs.htm"), $res);
}
