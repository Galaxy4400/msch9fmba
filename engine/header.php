<!DOCTYPE html>
<html class="<? if ($blind) { ?>_blind<? } ?>" lang="ru">

<head>
	<meta charset="utf-8">
	<meta name="format-detection" content="telephone=no">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $pageTitle ?></title>
	<meta name="keywords" content="<?= htmlspecialchars($kws) ?>">
	<meta name="description" content="<?= htmlspecialchars($desc) ?>">

	<!-- favicons -->
	<link rel="apple-touch-icon" href="<?= $dir_prefix ?>apple-touch-icon.png">
	<link rel="icon" href="<?= $dir_prefix ?>favicon.ico" sizes="any">
	<link rel="icon" href="<?= $dir_prefix ?>icon.png" type="image/png"> 
	<link rel="manifest" href="<?= $dir_prefix ?>manifest.webmanifest">
	<link rel="yandex-tableau-widget" href="<?= $dir_prefix ?>tableau.json">

	<script type="text/javascript" src="<?= $dir_prefix ?>admin/include/functions/phpimageeditor/lite/shared/javascript/jquery-1.7.1.min.js"></script>
	<script defer type="text/javascript" src="<?= $dir_prefix ?>images/js/files/responsivetable.js?v=<?= version ?>"></script>
	<link rel="stylesheet" href="<?= $dir_prefix ?>images/css/style.css?v=<?= version ?>">
</head>

<body>
	<div class="wrapper">
		<header class="header">
			<div class="header__content _container">
				<div class="header__top header-top">
					<?= displayLogo() ?>
					<div class="header-top__actions" data-da=".menu-line,767,first">
						<a class="header-top__btn header-top__btn_phone btn btn_big btn_green _icon-phone" href="tel:<?= phoneToLink(getSystemVariable($db, 'phone')) ?>" data-text="<?= getSystemVariable($db, 'phone-label') ?>"><?= getSystemVariable($db, 'phone') ?></a>
						<a class="header-top__btn btn btn_big btn_green _icon-enter" href="<?=$dir_prefix?>cabinet">Личный кабинет</a>
						<button class="header-top__btn btn btn_big btn_green _icon-eye" id="blind-btn" onclick="blindVersion()"><? if (!$blind) { ?>Версия для слабовидящих<? } else { ?>Обычная версия<? } ?></button>
						<div class="header-top__socials">
							<a class="header-top__social btn btn_big btn_green btn_social _icon-vk" href="https://vk.com/msch9dubna" target="_blank"></a>
						</div>
					</div>
					<button class="header-top__menu-icon btn btn_big btn_green icon-menu" type="button"></button>
				</div>
				<div class="header__bottom header-bottom">
					<div class="header-bottom__menu-line menu-line">
						<?= displayMainMenu() ?>
						<?= displaySearchForm() ?>
					</div>
					<div class="header-bottom__actions" data-da=".header-top,1200,2">
						<a class="header-bottom__btn btn btn_big" href="<?=$dir_prefix?>appointment">Запись на приём</a>
						<a class="header-bottom__btn btn btn_big btn_white _icon-plus" href="<?=$dir_prefix?>calling">Вызвать врача на дом</a>
					</div>
				</div>
			</div>
		</header>