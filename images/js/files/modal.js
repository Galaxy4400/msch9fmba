class Modal {
	constructor(options) {
		let defaultOptions = {
			isOpen: () => {},
			isClose: () => {},
			speed: 600,
			animation: 'fadeInUp',
			lockClass: '_lock',
		}
		this.options = Object.assign(defaultOptions, options);
		this.modal = document.getElementById('modal');
		this.isOpen = false;
		this.modalContainer = false;
		this.previousActiveElement = false;
		this.fixBlocks = document.querySelectorAll('._fix-block');
		this.focusElements = [
			'a[href]',
			'input',
			'button',
			'select',
			'textarea',
			'[tabindex]'
		];
		this.events();
	}

	events() {
		if (this.modal) {
			document.addEventListener('click', function(e) {
				const clickedElement = e.target.closest('[data-path]');
				if (clickedElement) {
					this.close();
					setTimeout(() => {
						let target = clickedElement.dataset.path;
						let animation = clickedElement.dataset.animation;
						let speed = clickedElement.dataset.speed;
						if (animation) this.options.animation = animation;
						if (speed) this.options.speed = speed;
						this.modalContainer = document.querySelector(`[data-modal="${target}"]`);
						this.open();
					}, this.isOpen ? this.options.speed + 1 : 0);
					return;
				}

				if (e.target.closest('[data-close]')) {
					this.close();
					return;
				}
			}.bind(this));

			window.addEventListener('keydown', function(e) {
				if (e.keyCode == 27) {
					if (this.isOpen) {
						this.close();
					}
				}

				if (e.keyCode == 9 && this.isOpen) {
					this.focusCatch(e);
					return;
				}

			}.bind(this));

			this.modal.addEventListener('mousedown', function(e) {
				if (!e.target.classList.contains('[data-modal]') && !e.target.closest('[data-modal]') && this.isOpen) {
					this.close();
				}
			}.bind(this));
		}
	}

	openModal(modal, options = {}) {
		let isOpen = this.isOpen;
		this.close();
		setTimeout(() => {
			this.modalContainer = document.querySelector(`[data-modal="${modal}"]`);
			this.options = Object.assign(this.options, options);
			if (!this.modalContainer) return;
			this.open();
		}, isOpen ? this.options.speed + 1 : 0);
	}

	open() {
		this.previousActiveElement = document.activeElement;
		this.modal.style.setProperty('--transition-time', `${this.options.speed / 1000}s`);
		this.modal.classList.add('is-open');
		this.disableScroll();
		this.modalContainer.classList.add('modal-open');
		this.modalContainer.classList.add(this.options.animation);
		setTimeout(() => {
			this.modalContainer.classList.add('animate-open');
		}, 1);
		setTimeout(() => {
			this.isOpen = true;
			this.focusTrap();
			this.options.isOpen(this);
		}, this.isOpen ? this.options.speed : 0);
	}

	close() {
		if (this.modalContainer) {
			this.modal.classList.remove('is-open');
			this.modalContainer.classList.remove('animate-open');
			setTimeout(() => {
				this.modalContainer.classList.remove(this.options.animation);
				this.modalContainer.classList.remove('modal-open');
				this.isOpen = false;
				this.enableScroll();
				this.focusTrap();
				this.options.isClose(this);
			}, this.isOpen ? this.options.speed : 0);
		}
	}

	focusCatch(e) {
		const focusable = this.modalContainer.querySelectorAll(this.focusElements);
		const focusArray = Array.prototype.slice.call(focusable);
		const focusedIndex = focusArray.indexOf(document.activeElement);

		if (e.shiftKey && focusedIndex === 0) {
			focusArray[focusArray.length - 1].focus();
			e.preventDefault();
		}

		if (!e.shiftKey && focusedIndex === focusArray.length - 1) {
			focusArray[0].focus();
			e.preventDefault();
		}
	}

	focusTrap() {
		const focusable = this.modalContainer.querySelectorAll(this.focusElements);
		if (this.isOpen) {
			focusable[0].focus();
		}
	}

	disableScroll() {
		this.lockPadding();
		document.body.classList.add(this.options.lockClass);
	}

	enableScroll() {
		this.unlockPadding();
		document.body.classList.remove(this.options.lockClass);
	}

	lockPadding() {
		let paddingOffset = window.innerWidth - document.body.offsetWidth + 'px';
		this.fixBlocks.forEach((el) => {
			el.style.marginRight = paddingOffset;
		});
		document.body.style.paddingRight = paddingOffset;
	}

	unlockPadding() {
		this.fixBlocks.forEach((el) => {
			el.style.marginRight = '0px';
		});
		document.body.style.paddingRight = '0px';
	}
}

const modal = new Modal();