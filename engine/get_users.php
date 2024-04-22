<?php

$statuses = array("0" => "Новый", "1" => "Обработан", "2" => "Оплачен", "3" => "Отправлен", "4" => "Отклонён");

require(dir_prefix . "admin/include/sessions.php");

//===================================================================================

function displayUserInfo() {
	global $ui, $ent, $db;
	if (!$ui) {
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/login.htm"), $ent);
	} else {
		$ent = my_array_merge($ent, $ui);
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/loggedin.htm"), $ent);
	}
}

function checkEmailExist($Email) {
	global $db;
	$Email	= trim($Email);
	$Email	= mysql_real_escape_string($Email);
	$res	= $db->getData("select count(__id) as cnt from Users where Email='" . $Email . "'");
	$result = array("result" => ($res[0]['cnt'] > 0) ? "true" : "false");
	return 	$result;
}


//===================================================================================

function doRegisterUser($return_only = false) {
	global $ent, $db, $Password, $CreateDateTime, $LastActiveDateTime, $Email, $Name, $Phone, $Address, $dir_prefix, $page, $altBreadcrumbs;
	unset($error);

	$title = "Регистрация";
	$page['Header'] = $title;
	$altBreadcrumbs = getCabinetBreadcrumbs($title);

	if (strlen($Email) < 4)
		$error[] = "Выбранный Email слишком короткий";

	if (strlen($Password) < 4)
		$error[] = "Выбранный пароль слишком короткий";

	$res = $db->getData("select count(__id) as cnt from Users where Email='" . mysql_real_escape_string($Email) . "'")[0]['cnt'];

	if ($res)
		$error[] = "Пользователь с таким Email (<b>" . $Email . "</b>) уже зарегистрирован. Пожалуйста выберите другой Email.";

	if (count($error) > 0) {
		$ent['message'] = join("<br>", $error);

		if (!$return_only)
			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
		else
			return $error;
	} else {
		$CreateDateTime 	= timeStr();
		$LastActiveDateTime = $CreateDateTime;

		$Email 				= $db->protectXSS($Email);

		$UserID 			= $db->insert("Users");

		$hashPassword		= hashPswd($Password, $UserID);

		$db->query("update Users set Password='" . $hashPassword . "' where __id='" . $UserID . "'");

		$ent['message'] 	= "Регистрация прошла успешно. Вам открыт доступ к личному кабинету .<br>Не забудьте войти в систему со своими регистрационными данными.<br><br><a class='btn btn-success' href='".$dir_prefix."?action=cabinet'>Войти в кабинет</a>";
		$ent['messageTitle'] = "Регистрация завершена";

		//письмо админу
		$mailSubject 	= "Регистрация нового клиента на сайте " . getSystemVariable($db, "site_name");
		$mailTo 		= getSystemVariable($db, "globalMailTo");

		$mailBody = "Здравствуйте!<br>
								На сайте зарегистрировался новый клиент:<br>
								Имя: <b>" . $Name . "</b><br>
								Email: <b>" . $Email . "</b><br>
								Телефон: <b>" . $Phone . "</b><br>
								---------------------------------<br>
								Всего наилучшего!<br>
								Это письмо сгенерировано роботом.";

		doMail($mailTo, $mailSubject, $mailBody, getSystemVariable($db, "globalMailTo"), getSystemVariable($db, "site_name"));


		//письмо клиенту
		$mailSubject 	= "Регистрация на сайте " . getSystemVariable($db, "site_name");
		$mailTo 		= $Email;

		$mailBody = "Здравствуйте!<br>
								Вы зарегистрировались на сайте " . getSystemVariable($db, "site_name") . ":<br>
								Данные для доступа:<br>
								Email: <b>" . $Email . "</b><br>
								Пароль: <b>" . $Password . "</b><br>
								---------------------------------<br>
								Всего наилучшего!<br>
								Это письмо сгенерировано роботом.";

		doMail($mailTo, $mailSubject, $mailBody, getSystemVariable($db, "globalMailTo"), getSystemVariable($db, "site_name"));

		doLogin(true);

		if (!$return_only)
			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/ok.htm"), $ent);
		else
			return NULL;
	}
}
//===================================================================================


function doAutoUserRegister() {
	global $db, $dir_prefix, $Email, $Name, $Password, $Phone, $ui;

	$res = $db->getData("select count(__id) as cnt from Users where Email='" . $Email . "'");

	if ($res[0]['cnt'] > 0) {
		$res['error'] = "Пользователь с таким email <b>" . $Email . "</b> уже зарегистророван в системе";
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/autoreg_error.htm"), $res);
	} else {
		$Password = generatePass(8, true);

		$Email = $Email;
		$Name  = $Name;

		global $CreateDateTime, $LastActiveDateTime;
		$CreateDateTime = timeStr();
		$LastActiveDateTime = $CreateDateTime;

		$UserID = $db->insert("Users");
		$ui['__id'] = $UserID;

		$Password_hashed 	= hashPswd($Password, $UserID);
		$db->query("update Users set Password='" . mysql_real_escape_string($Password_hashed) . "' where __id='" . intval($UserID) . "'");

		$mailSubject = "Доступ на сайт " . getSystemVariable($db, "site_name");
		$mailTo = $Email;
		$mailBody = "Здравствуйте!<br><br>Вы были зарегистрированы на сайте " . getSystemVariable($db, "site_name") . ".<br>Пароль для доступа в личный кабинет:<br>Email: " . $Email . "<br>Пароль: " . $Password . "<br><br><br>---------------------------------<br>Всего наилучшего!<br>Это письмо сгенерировано роботом.";

		doMail($mailTo, $mailSubject, $mailBody, getSystemVariable($db, "globalMailTo"), getSystemVariable($db, "site_name"));

		$res['dir_prefix'] = $dir_prefix;
		$res['Email'] = $Email;
		$res['Password'] = $Password;
		$res['UserID'] = $UserID;

		doLogin(true);

		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/autoreg_message.htm"), $res);
	}
}

function doRecallPassword() {
	global $ent, $db, $Email, $User, $dir_prefix;
	if ($User != "") { //обработка данных, полученных из ссылки, сброшенной на мыло
		$User		= base64_decode($User);
		$User_arr	= explode("|", $User);
		$res 		= $db->getData("
			select 
				__id, 
				Password, 
				Email 
			from 
				Users 
			where 
				__id='" . intval($User_arr[0]) . "' and 
				Email='" . mysql_real_escape_string($User_arr[1]) . "' and 
				Password = '" . $User_arr[2] . "'
			");


		if (count($res) > 0) {
			$new_password	= generatePass(15, true);
			$crp 			= $res[0]['__id'];
			$pass_to_db 	= hashPswd($new_password, $crp);

			$db->getData("update Users set Password='" . mysql_real_escape_string($pass_to_db) . "' where Email='" . mysql_real_escape_string($User_arr[1]) . "' and __id='" . intval($User_arr[0]) . "' and Password = '" . $User_arr[2] . "'");

			$ent['message'] 		= "Новый пароль: <b>" . $new_password . "</b><br>Пароль Вы всегда можете изменить в личном кабинете.";
			$ent['messageTitle'] 	= "Пароль был успешно изменен";


			$mailSubject 	= "Доступ на сайт " . getSystemVariable($db, "site_name");
			$mailTo 		= $User_arr[1];
			$mailBody 		= "Здравствуйте!<br><br>Пароль для доступа в личный кабинет был изменен.<br>Новые данные для доступа:<br>Email: " . $mailTo . "<br>Пароль: " . $new_password . "<br><br><br>---------------------------------<br>Всего наилучшего!<br>Это письмо сгенерировано роботом.";

			doMail($mailTo, $mailSubject, $mailBody, getSystemVariable($db, "globalMailTo"), getSystemVariable($db, "site_name"));

			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/ok.htm"), $ent);
		} else {

			$ent['message'] = "Такого пользователя не существует!";
			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
		}
	} else {

		if (trim($Email) == "") {
			$ent['message'] 	= "Укажите Email!";
			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
		}


		$res 			= $db->getData("
			select 
				__id, 
				Password, 
				Email 
			from 
				Users 
			where 
				Email='" . mysql_real_escape_string($Email) . "'
		");



		if ($res == "")
			$error[] = "Такой адрес Email не зарегистрирован в системе";


		if (count($error) > 0) {
			$ent['message'] = join("<br>", $error);
			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
		} else {
			$mailSubject 	= "Восстановление пароля на сайте " . getSystemVariable($db, "site_name");
			$mailTo 		= $Email;
			$site 			= "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$link1 			= "<a href='" . $site . "?action=doRecallPassword&User=" . base64_encode($res[0]['__id'] . "|" . $Email . "|" . $res[0]['Password']) . "'>";
			$link2 			= "</a>";
			$mailBody 		= "Здравствуйте!<br><br>Для смены пароля перейдите по ссылке: " . $link1 . "смена пароля" . $link2 . "<br><br><br>---------------------------------<br>Всего наилучшего!<br>Это письмо сгенерировано роботом.";
			doMail($mailTo, $mailSubject, $mailBody, getSystemVariable($db, "globalMailTo"), getSystemVariable($db, "site_name"));


			$lang['dir_prefix'] 	= $dir_prefix;
			$ent['message'] 		= "Данные для доступа высланы на <b>" . $Email . "</b>";
			$ent['messageTitle'] 	= "Данные отправлены";

			return parseTemplate(tplFromFile(dir_prefix . "engine/templates/ok.htm"), $ent);
		}
	}
}

//===================================================================================
function doLogin($return_only = false, $return_to_cabinet = false) {
	global $db, $ent, $Email, $Password, $cookie_user_id, $cookie_session, $HTTP_HOST, $dir_prefix;

	$page['Header'] 		= "Вход в личный кабинет";
	$page['PlainHeader'] 	= $page['Header'];

	$Email = mysql_real_escape_string($Email);
	$test_user 				= $db->getData("select __id from Users where Email='" . $Email . "'");

	if ($test_user != "") {
		$Password_hashed		= hashPswd($Password, $test_user[0]['__id']);
		$res 					= $db->getData("
			select 
				* 
			from 
				Users 
			where 
				Email='" . $Email . "' AND 
				Password='" . mysql_real_escape_string($Password_hashed) . "'
		");
	}

	if (count($res) < 1) {
		$ent['message'] 	= "Неверная пара Email / пароль!<br><a class='login-form__lost-pass-link _popup-link' href='#recall'>Забыли пароль?</a>";
		$page['Body'] 		= parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
		return $page;
	} else {
		my_setcookie("cookie_user_id", $UserID, time() + 30 * 24 * 3600, "/", $HTTP_HOST); // expires in 30 days;

		cleanupSessions();

		unset($params);
		$params['Email'] 		= $Email;
		$params['UserID'] 		= $res[0]['__id'];
		$params['FullName'] 	= $res[0]['Name'];

		$ses = new CSession(0, $params);
		my_setcookie("cookie_session", $ses->key, time() + 30 * 24 * 3600, "/", $HTTP_HOST); // expires in 30 days;
		if (!$return_only) {
			if ($return_to_cabinet)
				$from = "http://" . $_SERVER['HTTP_HOST'] . "/?action=cabinet";
			else
				$from = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			header("Location: " . $from);
		} else
			return $ses->key;
	}
}
//===================================================================================

function displayCabinetOrders() {
	global $db, $ent, $ui, $statuses;
	$ent 			= my_array_merge($ent, $ui);
	$query 			= "
		select 
			Orders.* 
		from 
			Orders 
		inner join 
			Users 
		on 
			Users.__id = Orders.UserID 
		where 
			Users.__id='" . intval($ui['UserID']) . "' 
		order by 
			Orders.Date desc
	";
	$res 			= $db->getData($query);
	$total 			= count($res);
	$ent['total'] 	= $total;
	for ($i = 0; $i < count($res); $i++) {
		$res[$i]['DateTime'] = fmtDate($res[$i]['Date']);
		$res[$i]['Status'] = $statuses[$res[$i]['Status']];
		$res[$i]['Total'] = format_price($res[$i]['Total']);
	}

	$ent['orders'] 	= populateFromDB($res, true);

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet_orders.htm"), $ent);
}

//===================================================================================

function displayCabinetOrderDetails() {
	global $db, $ent, $ui, $oid;

	$ent = my_array_merge($ent, $ui);

	$order = $db->getData("select * from Orders where __id = '".intval($oid)."' and UserID = '".$ui['UserID']."'")[0];
	$orderItems = $db->getData("select * from OrderItems where OrderID = " . intval($oid));

	if (!$order || !$orderItems) {
		$ent['message'] = "Неправильный код заказа";
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
	} else {
		$orderInfo = getOrderInfo($orderItems, $ui['UserID']);
		$ent['items'] = $orderInfo['items'];
		$ent['oid'] = $order['__id'];
		$ent['total'] = $orderInfo['totalOrderCostFormat'];
		$ent['shipping'] = $orderInfo['totalOrderCostFormat'];
		$ent['costInfo'] = getCostInfo($orderInfo);

		// if ($order['status'] == 0) {
		// 	$ent['payment_button'] = "<a class='btn btn-primary pull-right' target='_blank' href='" . getPaymentLink($oid) . "'>Перейти на страницу онлайн-оплаты <span class='fa fa-forward'></span></a>";
		// }

		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet_order_details.htm"), $ent);
	}
}


//===================================================================================


function displayCabinetHome() {
	global $db, $ent, $ui;

	$ent 	= my_array_merge($ent, $ui);

	$pg 	= $db->getData("
		select 
			Users.*, 
			sum(Orders.Total) as TotalOrdered, 
			count(Orders.__id) as cnt 
		from 
			Users 
		left join 
			Orders 
		on 
			Orders.UserId = Users.__id 
		where 
			Users.__id='" . intval($ui['UserID']) . "' 
		group by 
			Users.__id
	");

	$ent['orders_total'] = $pg[0]['cnt'] * 1;
	$ent['orders_total_cost'] = format_price($pg[0]['TotalOrdered'] * 1);

	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet_home.htm"), $ent);
}

//===============================================================

function displayCabinetEdit() {
	global $db, $ent, $ui;

	$ent 	= my_array_merge($ent, $ui);
	$pg 	= $db->getData("select * from Users where __id='" . intval($ui['UserID']) . "'");
	$ent 	= my_array_merge($ent, populateFromDB($pg));
	return parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet_edit.htm"), $ent);
}

//===================================================================================

function doCabinetUpdate() {
	global $ent, $db, $Password, $Password1, $Email, $Name, $Phone, $Address, $ui, $isFirm;

	unset($error);
	$res = $db->getData("select * from Users where __id='" . intval($ui['UserID']) . "'");

	$Password = trim($Password);
	if ($Password != "") {
		if (strlen($Password) < 4) $error[] = "Выбранный пароль слишком короткий";
		if ($Password != $Password1) $error[] = "Пароли должны совпадать";
		$Password	= hashPswd($Password, $res[0]['__id']);
	} else {
		$Password = $res[0]['Password'];
	}

	if (strlen($Email) < 4) $error[] = "Выбранный логин слишком короткий";
	if (trim($Name) == "") $error[] = "Не задано имя";
	if (trim($Email) == "") $error[] = "Не задан email";
	if (trim($Phone) == "") $error[] = "Не задан телефон";

	$Name = $db->protectXSS($Name);
	$Email = $db->protectXSS($Email);
	$Phone = $db->protectXSS($Phone);
	$Address = $db->protectXSS($Address);

	$res = $db->getData("select count(__id) as cnt from Users where Email='" . mysql_real_escape_string($Email) . "' AND __id<>'" . $ui['UserID'] . "'");
	if ($res[0]['cnt'] > 0)
		$error[] = "Пользователь с таким Email (<b>" . $Email . "</b>) уже зарегистрирован. Пожалуйста выберите другой логин.";

	if (count($error) > 0) {
		$ent['message'] = join("<br>", $error);
		return parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
	} else {
		$db->update("Users", "__id='" . mysql_real_escape_string($ui['UserID']) . "'");
		header("Location: ./?action=cabinet&actionSub=edit");
		die;
	}
}

//===================================================================================

function displayCabinet() {
	global $ent, $ui, $cookie_session, $actionSub, $altBreadcrumbs, $special_styles;

	$special_styles = false;

	$title = 'Личный кабинет';
	$page['Header'] = $title;
	$altBreadcrumbs = getCabinetBreadcrumbs($title);

	if (!$ui) {
		$ent['message'] 	= "Для доступа к личному кабинету Вам необходимо сначала <a class='_popup-link' href='#register'>зарегистрироваться</a>, либо войти под своими данными.";
		$page['Body'] 		= parseTemplate(tplFromFile(dir_prefix . "engine/templates/error.htm"), $ent);
	} else {
		$ent['active' . htmlspecialchars($actionSub)] = "btn_white";
		$ent['cabinet_menu'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet_menu.htm"), $ent);
		$ent['cabinet_settings_menu']	= parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet_settings_menu.htm"), $ent);

		switch ($actionSub) {
			case "edit": $ent['cabinet_body'] = displayCabinetEdit(); break;
			case "orders": $ent['cabinet_body'] = displayCabinetOrders(); break;
			case "order": $ent['cabinet_body'] = displayCabinetOrderDetails(); break;
			case "update": $ent['cabinet_body'] = doCabinetUpdate(); break;
			default:
				$ent['cabinet_body'] = displayCabinetHome(); break;
		}
		$page['Body'] = parseTemplate(tplFromFile(dir_prefix . "engine/templates/cabinet.htm"), $ent);
	}


	return $page;
}


//======================================================================================================
// Переделываем дату в sql формат
//======================================================================================================
//date	-	дата в формате DD.MM.YY
//time	-	время в формате HH:II:SS
//======================================================================================================
function dateToSql($date, $time = "00:00:00") { // dd:mm:yy

	list($ds, $ms, $ys) = explode(".", $date);
	$ds = intval($ds);
	$ds = ($ds < 10) ? ("0" . $ds) : $ds;

	$ms = intval($ms);
	$ms = ($ms < 10) ? ("0" . $ms) : $ms;

	$ys = intval($ys);
	$ys = ($ys < 10) ? ("0" . $ys) : $ys;

	//2013-08-14
	return $ys . "-" . $ms . "-" . $ds . " " . $time;
}


//===================================================================================

function getUserInfo() {
	global $cookie_session, $db;

	$ses = sessionByID($cookie_session);
	
	if (!$ses)
		return false;
	else {
		$result = $ses->getParams();
		$res = $db->getData("select * from Users where __id='" . $result['UserID'] . "'");
		if (count($res) > 0) {
			UpdateLastActive($result['UserID']);
			$result = my_array_merge($result, populateFromDB($res));
			return $result;
		} else
			return false;
	}
}

function UpdateLastActive($UserID) {
	global $db;
	$db->query("update Users set LastActiveDateTime = '" . timestr() . "' where __id='" . intval($UserID) . "'");
	return;
}

function doLogout() {
	global $db, $cookie_session, $HTTP_HOST, $dir_prefix;
	$ses 		= sessionByID($cookie_session);
	if ($ses)
		$ses->close();

	my_setcookie("cookie_session", "", time() - 1, "/", $HTTP_HOST);


	header("Location: " . $dir_prefix);

	die;
}


//===============================================================
function getCabinetBreadcrumbs($currBreadcrumbs = ""){
	global $dir_prefix;

	$b['href'] 	= $dir_prefix;
	$b['name'] = "Главная";
	$breadcrumbs[] = $b;

	$res['breadcrumbs'] = $breadcrumbs;
	$res['current'] = $currBreadcrumbs; 

	return parseTemplate(tplFromFile(dir_prefix."engine/templates/breadcrumbs.htm"), $res);
}