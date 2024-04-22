<!DOCTYPE HTML>
<html>
    <head>
    	<meta charset="utf-8"><!-- for local use -->
    	<title><?=$pageTitle?></title>
    	<link rel="shortcut icon" href="/favicon.ico" />
    	<link rel="stylesheet" href="<?=dir_prefix?>images/stylesheet.php">
    </head>
    <body <? if($print==1) { ?>onLoad="window.print()" <? } ?>>
        <div>
            <h1><?=$page['Header']?></h1><br>
            <?=$page['Body']?>
        </div>
        <? if($print!=1) { ?>
        <hr>
        <div>
            <a href="javascript:window.close();" style="color: #000000;">Закрыть окно и вернуться в каталог</a>
        </div>
        <? } ?>
    </body>
</html>