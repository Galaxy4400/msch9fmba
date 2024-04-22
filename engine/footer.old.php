		<footer class="footer">
			<div class="footer__content _container">
				<nav class="footer__body">
					<div class="footer__column">
						<?= displayMainMenu(page_index, 0, 0, "mainmenu_footer", "submenu_footer", "AND __id IN(23,24,25)") ?>
					</div>
					<div class="footer__column">
						<?= displayMainMenu(page_index, 0, 0, "mainmenu_footer", "submenu_footer", "AND __id IN(26)", "AND __id NOT IN(29)") ?>
					</div>
					<div class="footer__column">
						<?= displayMainMenu(26, 0, 0, "mainmenu_footer", "submenu_footer", "AND __id IN(29)") ?>
					</div>
					<div class="footer__column">
						<?= displayMainMenu(page_index, 0, 0, "mainmenu_footer", "submenu_footer", "AND __id IN(27,28)") ?>
						<div class="footer__actions">
							<a class="footer__btn btn btn_big" href="<?=$dir_prefix?>appointment">Запись на приём</a>
							<a class="footer__btn btn btn_big" href="<?=$dir_prefix?>calling">Вызвать врача на дом</a>
							<a class="footer__btn btn btn_big" href="<?=$dir_prefix?>attachment">Прикрепление к поликлинике</a>
							<button class="footer__btn btn btn_big" data-path="send-message">Отправить обращение</button>
						</div>
						<span class="footer__label"><span><?= getSystemVariable($db, 'copy') ?></span></span>
					</div>
				</nav>
			</div>
		</footer>
	</div>

	<a class="move-up _move-up _goto _fix-block" href="#header"></a>

	<div class="loader"></div>


	<? require_once('modal.php') ?>

	<? require_once('scripts.php') ?>

</body>
</html>