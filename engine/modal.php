<div class="modal" id="modal">

		<div class="modal-send-message" data-modal="send-message">
			<button class="modal-send-message__close _icon-del" data-close></button>
			<div class="modal-send-message__content">
				<?= displayForm("_message") ?>
			</div>
		</div>

		<div class="modal-reception-record" data-modal="reception-record">
			<button class="modal-reception-record__close _icon-del" data-close></button>
			<div class="modal-reception-record__content">
				<h3>Запись на приём</h3>
			</div>
		</div>

		<div class="modal-call-doctor" data-modal="call-doctor">
			<button class="modal-call-doctor__close _icon-del" data-close></button>
			<div class="modal-call-doctor__content">
				<h3>Вызвать врача</h3>
			</div>
		</div>

		<div class="modal-attaching" data-modal="attaching">
			<button class="modal-attaching__close _icon-del" data-close></button>
			<div class="modal-attaching__content">
				<h3>Прикрепление к поликлинике</h3>
				<button type="button" class="btn" data-path="send-message">Test: открыть ещё одно модальное окно</button>
			</div>
		</div>

		<div class="form-sended" data-modal="form-sended">
			<button class="form-sended__close _icon-del" data-close></button>
			<div class="form-sended__content">
				<div class="form-sended__icon _icon-checkmark"></div>
				<h3 class="form-sended__title">Форма отправлена</h3>
				<button class="form-sended__btn btn" type="button" data-close>Продолжить</button>
			</div>
		</div>
	
	</div>