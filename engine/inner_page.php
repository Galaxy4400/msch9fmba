<div class="page__inner page-inner">
	<div class="page-inner__container _container">
		<div class="page-inner__body <?= $page['leftAside'] ?>">
			<? if ($page['leftAside']) { ?>
				<aside class="page-inner__aside aside">
					<?= $page['submenu'] ?>
				</aside>
			<? } ?>
			<section class="page-inner__content">
				<div class="inner-content">
					<header class="inner-content__head inner-head">
						<div class="inner-head__breadcrumbs">
							<?= $page['breadcrumbs'] ?>
						</div>
						<h2 class="inner-head__title"><?= $page['Header'] ?></h2>
					</header>
					<div class="inner-content__body <?= $page['rightAside'] ?> <?= $page['specialStyles'] ?>">
						<div class="inner-content__content">
							<?= $page['Body'] ?>
						</div>
						<? if ($page['rightAside']) { ?>
							<div class="inner-content__aside">
								<?= $page['Body2'] ?>
							</div>
						<? } ?>
					</div>
				</div>
			</section>
		</div>
	</div>
</div>