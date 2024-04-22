		<footer class="footer">
			<div class="footer__content _container">
				<nav class="footer__body">
					<div class="footer__actions">
						<a class="footer__btn btn btn_big" href="<?=$dir_prefix?>appointment">Запись на приём</a>
						<a class="footer__btn btn btn_big" href="<?=$dir_prefix?>calling">Вызвать врача на дом</a>
						<a class="footer__btn btn btn_big" href="<?=$dir_prefix?>attachment">Прикрепление к поликлинике</a>
						<button class="footer__btn btn btn_big" data-path="send-message">Отправить обращение</button>
					</div>
					<div class="flex-container">
						<a class="" href="https://fmba.gov.ru/"><img src="<?= $dir_prefix ?>images/data/b_images/fl1.jpg"></a>
						<a class="" href="https://minzdrav.gov.ru/"><img src="<?= $dir_prefix ?>images/data/b_images/fl2.jpg"></a>
						<a class="" href="https://roszdravnadzor.gov.ru/"><img src="<?= $dir_prefix ?>images/data/b_images/fl3.jpg"></a>
						<a class="" href="https://bus.gov.ru/info-card/356057"><img src="<?= $dir_prefix ?>images/data/b_images/fl4.jpg"></a>
					</div>
					<div class="footer-info">
						<p>Размещенная информация на даннм сайте предоставляется исключительно в информационных целях и не является медицинскими рекомендациями. Определение диагноза и выбор методики лечения должны осуществляться только вашим лечащим врачом.</p> 
						<p>ФБУЗ МСЧ №9 ФМБА России не несет ответственности за возможные отрицательные последствия, возникшие в результате использования информации, размещенной на сайте www.msch9fmba.ru.</p> 
						<p>«ИМЕЮТСЯ ПРОТИВОПОКАЗАНИЯ. НЕОБХОДИМА КОНСУЛЬТАЦИЯ СПЕЦИАЛИСТА» </p> 
					</div>
					<div class="footer__bottom">
						<span class="footer__label"><span><?= getSystemVariable($db, 'copy') ?></span></span>
						<?= displayWebis() ?>
					</div>
				</nav>
			</div>
		</footer>
	</div>

	<a class="move-up _move-up _goto _fix-block" href="#header"></a>

	<div class="loader"></div>


	<? require_once('modal.php') ?>

	<? require_once('scripts.php') ?>
	
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript" >
	   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
	   m[i].l=1*new Date();
	   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
	   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
	   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

	   ym(95220106, "init", {
			clickmap:true,
			trackLinks:true,
			accurateTrackBounce:true,
			webvisor:true
	   });
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/95220106" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->

</body>
</html>