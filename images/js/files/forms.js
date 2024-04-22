/**
 * forms.js
 * 
 * Copyright (c) 2023 Moiseev Evgeny
 * Organization: WebisGroup
 */


const localization = 'Russian';


document.querySelectorAll('input[data-radio]').forEach(radio => {
	if (radio.type != 'radio') { console.error("Элемент должен являеться input[type='radio']"); return; }

	const isCustom = radio.dataset.custom !== undefined ? true : false;
	const radioClone = radio.cloneNode();
	const radioContainer = document.createElement('label');
	const radioLabel = isCustom ? radio.nextElementSibling : document.createElement('span');

	if (isCustom && (radioLabel.tagName.toLowerCase() != 'span' || radioLabel.dataset.radioCustom == undefined)) {
		console.error('Кастомное поле радиокнопки должно являться span-ом и иметь атрибут data-radio-custom');
		return;
	}

	radioContainer.classList.add('radio');
	radioContainer.classList.add(isCustom ? 'radio_custom' : 'radio_default');
	
	if (radio.className) {
		radioContainer.classList.add(radio.className.split(' ')[0]);
		radio.className = radio.className.split(' ')[0] + '__input';
	}

	if (!isCustom) {
		radioLabel.innerText = radioClone.dataset.label;
		radioLabel.className = 'radio__mark';
	}
	if (radioClone.checked) radioContainer.classList.add('_checked');

	radioContainer.append(radioClone);
	radioContainer.append(radioLabel);

	radio.after(radioContainer);
	radioClone.replaceWith(radio);

	radio.addEventListener('change', () => {
		document.querySelectorAll(`input[data-radio][name="${radio.name}"]`).forEach(radio => {
			radio.parentElement.classList.remove('_checked');
		});
		radio.parentElement.classList.add('_checked');
	});
});



document.querySelectorAll('input[data-check]').forEach(check => {
	if (check.type != 'checkbox') { console.error("Элемент должен являеться input[type='checkbox']"); return; }

	const isCustom = check.dataset.custom !== undefined ? true : false;
	const checkClone = check.cloneNode();
	const checkContainer = document.createElement('label');
	const checkLabel = isCustom ? check.nextElementSibling : document.createElement('span');

	if (isCustom && (checkLabel.tagName.toLowerCase() != 'span' || checkLabel.dataset.checkCustom == undefined)) {
		console.error('Кастомное поле радиокнопки должно являться span-ом и иметь атрибут data-check-custom');
		return;
	}

	checkContainer.classList.add('check');
	checkContainer.classList.add(isCustom ? 'check_custom' : 'check_default');
	
	if (check.className) {
		checkContainer.classList.add(check.className.split(' ')[0]);
		check.className = check.className.split(' ')[0] + '__input';
	}

	if (!isCustom) {
		checkLabel.innerText = checkClone.dataset.label;
		checkLabel.className = 'check__mark';
	}
	if (checkClone.checked) checkContainer.classList.add('_checked');

	checkContainer.append(checkClone);
	checkContainer.append(checkLabel);

	check.after(checkContainer);
	checkClone.replaceWith(check);

	check.addEventListener('change', () => {
		check.checked ? check.parentElement.classList.add('_checked') : check.parentElement.classList.remove('_checked');
	});
});



document.querySelectorAll('input[data-file]').forEach(input => {
	if (input.type != 'file') { console.error("Элемент должен являеться input[type='file']"); return; }

	const fileButtonLocalization = {
		English: 'Review',
		Russian: 'Обзор',
	};

	const inputClone = input.cloneNode();
	const inputContainer = document.createElement('label');
	const inputLabel = document.createElement('span');
	const inputButton = document.createElement('span');

	inputContainer.className = 'input-file';
	inputLabel.className = 'input-file__text';
	inputButton.className = 'input-file__btn';

	inputButton.innerText = fileButtonLocalization[localization];

	inputContainer.append(inputLabel);
	inputContainer.append(inputClone);
	inputContainer.append(inputButton);

	input.after(inputContainer);
	inputClone.replaceWith(input);

	input.addEventListener('change', () => {
		let files = [];
		Object.values(input.files).forEach((file) => files.push(file.name));
		const filesStr = files.join(', ');
		inputLabel.innerHTML = files.length ? filesStr : '';
		inputLabel.title = filesStr;
	});
});



document.querySelectorAll('[data-slider]').forEach((range) => {
	const rangeName = range.dataset.slider;

	if (document.querySelectorAll(`[data-slider=${rangeName}]`).length > 1) { console.error(`Несколько диапазонов ${rangeName} на странице!`); return; }

	const rangeLocalization = {
		'from': {
			English: 'From',
			Russian: 'От',
		},
		'to': {
			English: 'to',
			Russian: 'до',
		},
		'value': {
			English: 'Value',
			Russian: 'Значение',
		},
	};

	const [min, max] = range.dataset.range.split(',').map(num => +(num.trim()));
	const start = range.dataset.start ? range.dataset.start.split(',').map(num => +(num.trim())) : [min];

	const rangeClone = range.cloneNode();
	const rangeContainer = document.createElement('div');
	const rangeLabel = document.createElement('div');
	const rangeInputsContainer = document.createElement('div');
	
	const rangeInputs = start.map((value, i) => {
		const rangeInput = document.createElement('input');
		if (start.length > 1) {
			switch (i) {
				case 0: rangeInput.name = `${rangeName}_min`; break;
				case 1: rangeInput.name = `${rangeName}_max`; break;
				default: rangeInput.name = `${rangeName}_${i}`; break;
			}
		} else {
			rangeInput.name = rangeName;
		}
		rangeInput.type = 'number';
		rangeInput.setAttribute('value', value);
		rangeInput.className = 'range__input';
		rangeInput.setAttribute('data-qty', "");
		rangeInputsContainer.append(rangeInput);
		return rangeInput;
	});

	rangeContainer.className = `range range_${rangeName}`;
	rangeLabel.className = `range__labels`;
	rangeInputsContainer.className = `range__inputs`;
	range.className = `range__slider`;

	rangeContainer.append(rangeInputsContainer);
	rangeContainer.append(rangeClone);
	rangeContainer.append(rangeLabel);

	range.after(rangeContainer);
	rangeClone.replaceWith(range);

	
	const beetwen = range.dataset.between?.split(/,\s*/);
	const rangeBetweenMin = (beetwen && +beetwen[0] !== 0) ? +beetwen[0] : false;
	const rangeBetweenMax = (beetwen && +beetwen[1] !== 0) ? +beetwen[1] : false;
	const rangePadding = range.dataset.padding ? +range.dataset.padding : false;
	const rangeStep = range.dataset.step ? +range.dataset.step : false;
	const rangeFraction = range.dataset.fraction ? +range.dataset.fraction : 0;
	const rangeTooltips = range.dataset.tooltips !== undefined ? true : false;
	const rangeShowInputs = range.dataset.inputs !== undefined ? true : false;

	
	noUiSlider.create(range, {
		start: start,
		range: {
			'min': min,
			'max': max,
		},
		format: {
			to: value => +Number(value).toFixed(rangeFraction),
			from: value => +Number(value).toFixed(rangeFraction),
		},
		connect: (start.length == 1) ? 'lower' : (start.length == 2) ? [false, true, false] : false, 
		...( rangeBetweenMin ? { margin: rangeBetweenMin } : {} ),
		...( rangeBetweenMax ? { limit: rangeBetweenMax } : {} ),
		...( rangeBetweenMax ? { behaviour: 'drag' } : {} ),
		...( rangePadding ? { padding: rangePadding } : {} ),
		...( rangeStep ? { step: rangeStep } : {} ),
		...( rangeTooltips ? { tooltips: start.length == 1 ? [true] : [true, true] } : {} ),
	});

	
	const hideInputs = () => {
		rangeInputsContainer.style.height = '0px';
		rangeInputsContainer.style.overflow = 'hidden';
		rangeInputsContainer.style.position = 'absolute';
	};

	
	const synchronWithSlider = () => {
		range.noUiSlider.on('update', (values, handle) => {
			if (!handle) {
				if (rangeInputs[0]) rangeInputs[0].value = Number(values[handle]);
			} else {
				if (rangeInputs[1]) rangeInputs[1].value = Number(values[handle]);
			}
		});
	};

	
	const synchronWithInputs = () => {
		if (rangeInputs[0]) rangeInputs[0].addEventListener('input', () => {
			range.noUiSlider.set([rangeInputs[0].value, null]);
		});
		if (rangeInputs[1]) rangeInputs[1].addEventListener('input', () => {
			range.noUiSlider.set([null, rangeInputs[1].value]);
		});
	};

	
	range.noUiSlider.on('update', (values) => {
		const isLabel = range.dataset.label === '' ? false : true;
		if (!isLabel) return;
		const suffix = range.dataset.suffix ? ' '+range.dataset.suffix : '';
		if (values.length == 1) {
			const label = range.dataset.label ? range.dataset.label : rangeLocalization['value'][localization];
			rangeLabel.innerHTML = `${label}: ${values.join('')}${suffix}`;
		} else {
			const label = range.dataset.label ? range.dataset.label + ': ' : '';
			rangeLabel.innerHTML = `${label}${rangeLocalization['from'][localization]} ${values.join(' '+rangeLocalization['to'][localization]+' ')}${suffix}`;
		}
	});

	synchronWithSlider();
	synchronWithInputs();

	if (!rangeShowInputs) hideInputs();
});



document.querySelectorAll('input[data-qty]').forEach(qtyInput => {
	if (qtyInput.type != 'number') { console.error("Элемент должен являеться input[type='number']"); return; }

	const isValue = qtyInput.value !== '' ? true : false;
	const min = qtyInput.min ? +qtyInput.min : -Infinity;
	const max = qtyInput.max ? +qtyInput.max : +Infinity;

	if (min > max) { console.error('data-min не может быть больше data-max'); return }

	if (!isValue) qtyInput.value = 0;
	if (qtyInput.value < min) qtyInput.value = min;
	if (qtyInput.value > max) qtyInput.value = max;

	const inputClone = qtyInput.cloneNode();
	const inputContainer = document.createElement('div');
	const inputMinus = document.createElement('button');
	const inputPlus = document.createElement('button');
	const inputWrapper = document.createElement('div');

	inputMinus.type = 'button';
	inputPlus.type = 'button';
	inputContainer.className = 'quantity' + (inputClone.hasAttribute('readonly') ? ' _readonly' : '');
	inputMinus.className = 'quantity__button quantity__button_minus';
	inputPlus.className = 'quantity__button quantity__button_plus';
	inputWrapper.className = 'quantity__input';

	inputWrapper.append(inputClone);
	inputContainer.append(inputMinus);
	inputContainer.append(inputWrapper);
	inputContainer.append(inputPlus);

	qtyInput.after(inputContainer);
	inputClone.replaceWith(qtyInput);

	qtyInput.autocomplete = 'off';

	
	
	const counterStep = qtyInput.hasAttribute('step') ? +qtyInput.getAttribute('step') : 1;
	const counterTimeoutSpeedMin = 20;
	const counterSpeedDivider = 2;
	let counterTimeoutSpeed = 500;
	let mousedownTimeout = null;
	
	
	const qtyTimeoutReset = (value = qtyInput.value) => {
		clearInterval(mousedownTimeout);
		counterTimeoutSpeed = 500;
		qtyInput.value = value;
		qtyInput.dispatchEvent(new Event("input"));
	};

	
	const qtyTimeoutSpeed = () => {
		counterTimeoutSpeed =
			counterTimeoutSpeed / counterSpeedDivider > counterTimeoutSpeedMin
				? counterTimeoutSpeed / counterSpeedDivider
				: counterTimeoutSpeedMin;
	};

	
	const qtyIncreasing = () => {
		if ((+qtyInput.value + counterStep) > max) { qtyTimeoutReset(max); return; }
		qtyInput.value = +qtyInput.value + counterStep;
		qtyTimeoutSpeed();
		qtyInput.dispatchEvent(new Event("input"));
		mousedownTimeout = setTimeout(qtyIncreasing, counterTimeoutSpeed);
	};

	
	const qtyReducing = () => {
		if ((+qtyInput.value - counterStep) < min) { qtyTimeoutReset(min); return; }
		qtyInput.value = qtyInput.value - counterStep;
		qtyTimeoutSpeed();
		qtyInput.dispatchEvent(new Event("input"));
		mousedownTimeout = setTimeout(qtyReducing, counterTimeoutSpeed);
	};

	
	inputContainer.querySelectorAll('.quantity__button').forEach(qtyBtn => {
		qtyBtn.addEventListener('mousedown', () => {
			if (qtyInput.hasAttribute('readonly')) return;
			if (qtyBtn.classList.contains('quantity__button_plus')) {
				qtyIncreasing();
			} else {
				qtyReducing();
			}
			qtyInput.dispatchEvent(new Event("input"));
		});

		
		qtyBtn.addEventListener('mouseup', () => qtyTimeoutReset());
		qtyBtn.addEventListener('mouseout', () => qtyTimeoutReset());
	});

	
	qtyInput.addEventListener('input', () => {
		let value = +qtyInput.value;
		if (isNaN(value)) value = 0;
		if (value < min) value = min;
		if (value > max) value = max;
		qtyInput.value = value;
	});
});



document.querySelectorAll('[data-choice]').forEach((select) => {
	
	const choicesLocalization = {
		noResultsText: {
			English: 'No results found',
			Russian: 'Ничего не найдено',
		},
	};

	
	const switcherOptions = {};
	select.querySelectorAll('[data-switcher]').forEach((option) => {
		switcherOptions[option.value] = option.dataset.switcher;
	});

	const selectClass = select.className;
	select.removeAttribute('class');
	
	const choice = new Choices(select, {
		searchEnabled: (select.dataset.search !== undefined) ? true : false,
		placeholderValue: select.getAttribute('placeholder'),
		searchPlaceholderValue: (select.dataset.placeholder !== undefined) ? select.dataset.placeholder : '',
		noResultsText: choicesLocalization.noResultsText[localization],
		shouldSort: false,
		itemSelectText: '',
		classNames: {
			containerOuter: `choices ${selectClass}`,
		},
	});

	
	choice.switcherOptions = switcherOptions;

	
	const switcherList = Object.entries(switcherOptions).map(switcher => `${switcher[0]}:${switcher[1]}`).join(', ');
	choice.passedElement.element.setAttribute('data-switcher-list', switcherList);

	
	const processChoiceSwitcher = (value) => {
		if (choice.switcherOptions[value]) {
			choice.passedElement.element.setAttribute('data-switcher', choice.switcherOptions[value]);
		} else {
			choice.passedElement.element.removeAttribute('data-switcher');
		}
	}

	
	

	
	processChoiceSwitcher(choice.getValue(true));
	
	
	choice.passedElement.element.addEventListener('change', event => {
		processChoiceSwitcher(event.detail.value)
	});
});



document.querySelectorAll('input[data-mask]').forEach(input => {
	const letterSpacing = input.dataset.maskBetween ? input.dataset.maskBetween : false;
	if (letterSpacing) input.style.letterSpacing = `${letterSpacing}px`;

	new Inputmask({
		mask: input.dataset.mask,
		placeholder: input.dataset.maskPlaceholder ? input.dataset.maskPlaceholder : " ",
		clearIncomplete: true,
		clearMaskOnLostFocus: true,
	}).mask(input);
});


document.querySelectorAll('[data-form]').forEach((form) => {
	setRecaptcha(form);
	validateForm(form);
});



function setRecaptcha(form) {
	const isRecaptcha = form.querySelector('input[name="siteKey"]') ? true : false;
	if (isRecaptcha) {
		document.addEventListener("DOMContentLoaded", () => {
			const siteKey = form.querySelector('input[name="siteKey"]').value;
			grecaptcha.ready(() => {
				grecaptcha.execute(siteKey, { action: 'homepage' }).then(token => {
					form.querySelector('input[name="token"]').value = token;
				});
			});
		});
	}
}



function validateForm(form) {
	const validator = new JustValidate(form, {
		errorFieldCssClass: '_error',
		successFieldCssClass: '_success',
		errorLabelCssClass: 'error-label',
	}, getValidatorLocalization());

	
	validator.setCurrentLocale(localization);

	
	const addFieldValidation = (field) => {
		const rules = getFieldRules(field);
		const config = getFieldConfig(field);
		if (rules.length) {
			validator.addField(field, rules, config);
		}
	};

	
	const addGroupValidation = (group) => {
		if (group.closest('.choices')) return; 
		const config = getFieldConfig(group);
		validator.addRequiredGroup(group, 'You should select at least one communication channel', config);
	};

	
	const initValidation = (fields, groups) => {
		fields.forEach(field => addFieldValidation(field));
		groups.forEach(group => addGroupValidation(group));
		[...fields, ...groups].forEach(elem => elem.closest('[data-switch], [data-switch-rev]')?.removeAttribute('data-disable')); 
	}

	
	const removeValidation = (fields, groups) => {
		fields.forEach(field => {
			validator.removeField(field);
			if (field.type != 'radio' || field.type != 'checkbox') field.value = "";
		});
		groups.forEach(group => validator.removeGroup(`[data-group="${group.dataset.group}"]`)); 
		[...fields, ...groups].forEach(elem => elem.closest('[data-switch], [data-switch-rev]')?.setAttribute('data-disable', '')); 
	}
	
	const allFields = form.querySelectorAll('input, select, textarea');
	const allGroups = form.querySelectorAll('[data-group]');

	initValidation(allFields, allGroups);

	
	const switchers = form.querySelectorAll('[data-switcher]');
	switchers.forEach(switcher => {
		
		let switcherType = false;
		if (switcher.type == 'checkbox' || switcher.type == 'radio') switcherType = 'check';
		if (switcher.type == 'select-one' || switcher.type == 'select-multiple') switcherType = 'choice';
		if (switcher.tagName.toLowerCase() == 'option') switcherType = 'option';

		if (!switcherType) { console.error("Недопустимый тип переключателя"); return; }

		
		const switchesSelector = switcher.dataset.switcher.split(/,\s*/).map((switchName) => `[data-switch="${switchName}"]`).join(',');
		const switchElems = document.querySelectorAll(switchesSelector);

		
		const fields = Object.values(switchElems).reduce((elems, elem) => elems.concat(...elem.querySelectorAll('input, select, textarea')), []).filter(elem => !elem.closest('[data-group]'));
		const groups = Object.values(switchElems).reduce((groups, elem) => groups.concat(...elem.querySelectorAll('[data-group]')), []);

		
		const switchesRevSelector = switcher.dataset.switcher.split(/,\s*/).map((switchName) => `[data-switch-rev="${switchName}"]`).join(',');
		const switchRevElems = document.querySelectorAll(switchesRevSelector);

		
		const fieldsRev = Object.values(switchRevElems).reduce((elems, elem) => elems.concat(...elem.querySelectorAll('input, select, textarea')), []).filter(elem => !elem.closest('[data-group]'));
		const groupsRev = Object.values(switchRevElems).reduce((groups, elem) => groups.concat(...elem.querySelectorAll('[data-group]')), []);

		
		const checkSwitcher = () => {
			switch (switcherType) {
				case 'check': if (switcher.checked) return true; break;
				case 'option': if (switcher.selected) return true; break;
				case 'choice': if (switcher.dataset.switcher) return true; break;
			}
			return false;
		}
		
		
		const redeclareValidation = (checked) => {
			if (checked) {
				initValidation(fields, groups);
				removeValidation(fieldsRev, groupsRev)
			} else {
				initValidation(fieldsRev, groupsRev);
				removeValidation(fields, groups)
			}
		}

		
		const clickSwitcherChange = () => {
			const switcherElems = form.querySelectorAll(`[name="${switcher.name}"]`);
			switcherElems.forEach(elem => {
				elem.addEventListener('change', () => redeclareValidation(switcher.checked));
			});
		}

		
		const choiceSwitcherChange = () => {
			switcher.addEventListener('change', () => redeclareValidation(switcher.dataset.switcher !== undefined));
		}
		
		
		const optionSwitcherChange = () => {
			const selector = switcher.closest('select');
			selector.addEventListener('change', () => redeclareValidation(selector.options[selector.selectedIndex].dataset.switcher !== undefined));
		}

		
		if (!checkSwitcher()) removeValidation(fields, groups);
		if (checkSwitcher()) removeValidation(fieldsRev, groupsRev);

		switch (switcherType) {
			case 'check': clickSwitcherChange(); break;
			case 'choice': choiceSwitcherChange(); break;
			case 'option': optionSwitcherChange(); break;
			default: break;
		}
		
		
	});



	form.querySelectorAll('[data-submitter]').forEach(field => {
		field.addEventListener('change', () => validator.revalidate());
	});

	
	validator.onValidate((validationData) => {
		
		if (!validationData.isSubmitted) return;
		
		const processFieldValidClass = (elem, isValid) => {
			if (isValid) {
				elem.classList.add(validator.globalConfig.successFieldCssClass);
				elem.classList.remove(validator.globalConfig.errorFieldCssClass);
			} else {
				elem.classList.add(validator.globalConfig.errorFieldCssClass);
				elem.classList.remove(validator.globalConfig.successFieldCssClass)
			}
		}

		Object.values(validationData.fields).forEach((field) => {
			const elem = field.elem;
			const parent = elem.parentElement;
			const isChoics = elem.closest('.choices') ? true : false;
			const isCustomFile = elem.closest('.input-file') ? true : false;
			
			if (isCustomFile || isChoics || parent.tagName.toLowerCase() == 'label' && (elem.type == 'checkbox' || elem.type == 'radio')) {
				processFieldValidClass(parent, field.isValid);
			}
		});

		Object.values(validationData.groups).forEach((group) => {
			group.elems.forEach(elem => {
				const parent = elem.parentElement;
				
				if (parent.tagName.toLowerCase() == 'label' && (elem.type == 'checkbox' || elem.type == 'radio')) {
					processFieldValidClass(parent, group.isValid);
				}
			});
		});
	});

	validator.onSuccess(() => doSubmitForm(form));
}

function getFieldRules(input) {
	let rules = [];

	const type = input.type ? input.type : false;
	const required = input.dataset.required !== undefined ? true : false;
	const repeat = input.dataset.repeat !== undefined ? true : false;
	const number = input.dataset.number !== undefined ? true : false;
	const min = input.dataset.min ? input.dataset.min : false;
	const max = input.dataset.max ? input.dataset.max : false;
	const regexp = input.dataset.regexp ? input.dataset.regexp : false;

	
	if (required) {
		rules.push({
			rule: 'required',
			errorMessage: 'Field is required',
		});
	}

	
	if (type == 'email') {

		
		rules.push({
			rule: 'email',
			errorMessage: 'Field is invalid',
		});

		
		if (input.dataset.action) {
			rules.push({
				validator: (value) => () =>
					new Promise((resolve) => {
						
						const action = input.dataset.action.includes('?')
							? `${input.dataset.action}&email=${value}`
							: `${input.dataset.action}?email=${value}`;
						
						fetch(action)
						.then(response => response.text())
						.then(isExist => resolve(isExist.toLowerCase() === 'true')) 
						.catch(error => console.log(error));
					}),
				errorMessage: 'Email already exists!',
			});
		}
	}

	
	if (type == 'password') {
		rules.push({
			rule: 'password',
			errorMessage: `Password must contain minimum eight characters, at least one letter and one number`,
		});
	}

	
	if (repeat) {
		rules.push({
			validator: (value, fields) => {
				const field = Object.values(fields).find(field => field.elem.dataset.link !== undefined);
				if (field) {
					return value === field.elem.value;
				}
				return true;
			},
			errorMessage: 'Passwords should be the same',
		});
	}

	
	if (number) {
		rules.push({
			rule: 'number',
			errorMessage: `Value should be a number`,
		});
	}

	
	if (min) {
		rules.push({
			rule: 'minLength',
			value: parseInt(min),
			errorMessage: `Field is too short`,
		});
	}

	
	if (max) {
		rules.push({
			rule: 'maxLength',
			value: parseInt(max),
			errorMessage: `Field is too long`,
		});
	}

	
	if (regexp) {
		rules.push({
			rule: 'customRegexp',
			value: new RegExp(regexp, "gi"),
			errorMessage: `Field is invalid`,
		});
	}
	
	
	if (type == 'file') {
		
		
		rules.push({
			rule: 'files',
			value: {
				files: {
					extensions: input.dataset.ext ? input.dataset.ext.split(/,\s*/) : null,
					minSize: input.dataset.sizeMin ? +input.dataset.sizeMin * 1024 : null,
					maxSize: input.dataset.sizeMax ? +input.dataset.sizeMax * 1024 : null,
					types: input.dataset.type ? input.dataset.type.split(/,\s*/) : null,
				},
			},
			errorMessage: `Uploaded files have one or several invalid properties (extension/size/type etc).`,
		},);
		
		
		if (min) {
			rules.push({
				rule: 'minFilesCount',
				value: parseInt(min),
				errorMessage: input.multiple ? `Files count should be more` : `No file selected`,
			});
		}
		
		
		if (max) {
			rules.push({
				rule: 'maxFilesCount',
				value: parseInt(max),
				errorMessage: `Files count should be less`,
			});
		}
	}

	return rules;
}


function getFieldConfig(elem) {
	const config = {};

	const type = (elem.dataset.group !== undefined) ? 'group' : elem.type;

	if (type == 'hidden') return;


	const createErrorContainer = (type) => {
		const newErrorsContainer =  document.createElement('div');
		if (elem.dataset.hideError !== undefined) newErrorsContainer.style.display = 'none';
		newErrorsContainer.className = `form__validate-error form__validate-error_${type}`;
		return newErrorsContainer;
	};


	const onlyOneErrorContainer = (container) => {
		const containerSelector = '.'+container.className.split(/ /g,).join('.');
		let containers = Array.from(container.parentElement.querySelectorAll(containerSelector));
		if (containers.length > 1) {
			container = containers.pop();
			containers.forEach(el => el.remove());
		}
		return container;
	};

	let errorsContainer;

	switch (type) {
		case 'group':
			errorsContainer = createErrorContainer('group');
			elem.after(errorsContainer);
			config.errorsContainer = onlyOneErrorContainer(errorsContainer);
			break;

		case 'checkbox':
			if (elem.closest('[data-group]')) break;
			errorsContainer = createErrorContainer('check');
			const checkboxParent = elem.parentElement;
			checkboxParent.tagName.toLowerCase() == 'label' ? checkboxParent.after(errorsContainer) : elem.after(errorsContainer);
			config.errorsContainer = onlyOneErrorContainer(errorsContainer);
			break;

		case 'file':
			errorsContainer = createErrorContainer('file');
			const fileParent = elem.parentElement;
			fileParent.tagName.toLowerCase() == 'label' ? fileParent.after(errorsContainer) : elem.after(errorsContainer);
			config.errorsContainer = onlyOneErrorContainer(errorsContainer);
			break;

		case 'select-one':
		case 'select-multiple':
			const choice = elem.closest('.choices');
			errorsContainer = createErrorContainer('select');
			choice ? choice.after(errorsContainer) : elem.after(errorsContainer);
			config.errorsContainer = onlyOneErrorContainer(errorsContainer);
			break;

		case 'radio':
			break;
		
		default:
			errorsContainer = createErrorContainer('input');
			const inputParent = elem.parentElement;
			inputParent.tagName.toLowerCase() == 'label' ? inputParent.after(errorsContainer) : elem.after(errorsContainer);
			config.errorsContainer = onlyOneErrorContainer(errorsContainer);
			break;
	}

	return config;
}


function doSubmitForm(form) {
	const sendForm = form.dataset.send;
	const beforeSubmit = form.dataset.before;
	const afterSubmit = form.dataset.after;

	if (beforeSubmit && !window[beforeSubmit]()) return;

	switch (sendForm) {
		case 'ajax':
			submitByAjax(form, afterSubmit);
			break;

		case 'test':
			modal.openModal('form-sended');
			if (afterSubmit) window[afterSubmit]();
			alert('Форма отправлена');
			break;

		default:
			form.submit();
			break;
	}
}


function submitByAjax(form, afterSubmit = false) {
	const formAction = form.action ? form.getAttribute('action').trim() : '#';
	const formMethod = form.method ? form.getAttribute('method').trim() : 'GET';

	const formData = new FormData(form);
	formData.append('ajax', true);

	fetch(formAction, {
		method: formMethod,
		body: formData,
	})
	.then(response => response.json())
	.then(data => {
		if (afterSubmit) window[afterSubmit].call(null, data);
		modal.openModal('form-sended');
	})
	.catch(error => console.log(error.message));
}



function getValidatorLocalization() {
	return [
		{
			key: `Field is required`,
			dict: {
				Russian: `Поле обязательно`,
				Spanish: `El campo es obligatorio`,
			},
		},
		{
			key: `Field is too short`,
			dict: {
				Russian: `Поле слишком короткое`,
				Spanish: `El campo es demasiado corto`,
			},
		},
		{
			key: `Field is too long`,
			dict: {
				Russian: `Поле слишком длинное`,
				Spanish: `El campo es demasiado largo`,
			},
		},
		{
			key: `Field is invalid`,
			dict: {
				Russian: `Недопустимый формат поля`,
				Spanish: `El campo no es válido`,
			},
		},
		{
			key: `Password must contain minimum eight characters, at least one letter and one number`,
			dict: {
				Russian: `Пароль должен содержать минимум восемь символов. По крайней мере одну букву и одну цифру`,
				Spanish: `La contraseña debe contener un mínimo de ocho caracteres, al menos una letra y un número`,
			},
		},
		{
			key: `Passwords should be the same`,
			dict: {
				Russian: `Пароли должны совпадать`,
				Spanish: `Las contraseñas deben ser las mismas`,
			},
		},
		{
			key: `You should select at least one communication channel`,
			dict: {
				Russian: `Вы должны выбрать хотя бы одину опцию`,
				Spanish: `Debe seleccionar al menos un canal de comunicación`,
			},
		},
		{
			key: `Files count should be less`,
			dict: {
				Russian: `Файлов должно быть меньше`,
				Spanish: `El recuento de archivos debería ser menor`,
			},
		},
		{
			key: `Files count should be more`,
			dict: {
				Russian: `Файлов должно быть больше`,
				Spanish: `El recuento de archivos debería ser mayor`,
			},
		},
		{
			key: `No file selected`,
			dict: {
				Russian: `Файл не выбран`,
				Spanish: `No hay ningún archivo seleccionado`,
			},
		},
		{
			key: `Uploaded files have one or several invalid properties (extension/size/type etc).`,
			dict: {
				Russian: `Загруженные файлы имеют одно или несколько недопустимых свойств (расширение/размер/тип и т.д.).`,
				Spanish: `Los archivos cargados tienen una o varias propiedades no válidas (extensión/tamaño/tipo, etc.).`,
			},
		},
		{
			key: `Email already exists!`,
			dict: {
				Russian: `Почта уже существует!`,
				Spanish: `¡El correo electrónico ya existe!`,
			},
		},
		{
			key: `Field should be a number`,
			dict: {
				Russian: `Должно быть число`,
				Spanish: `El campo debe ser un número`,
			},
		},
	];
}