define(['jquery', 'TYPO3/CMS/Lux/Vendor/Chart.min'], function($) {
	'use strict';

	/**
	 * LuxBackend functions
	 *
	 * @class LuxBackend
	 */
	function LuxBackend($) {
		'use strict';

		/**
		 * @type {LuxBackend}
		 */
		var that = this;

		/**
		 * Initialize
		 *
		 * @returns {void}
		 */
		this.initialize = function() {
			addDetailViewListener();
			addDescriptionListener();
			addLinkMockListener();
			addDatePickers();
			addWizardForm();
			addTriggers();
			addActions();
			addDeleteListener();
		};

		/**
		 * @returns {void}
		 */
		var addDetailViewListener = function() {
			var elements = document.querySelectorAll('[data-lux-action-detail]');
			for (var i = 0; i < elements.length; i++) {
				var element = elements[i];
				element.addEventListener('click', function() {
					removeClassFromElements(elements, 'lux-action-detail');
					this.classList.add('lux-action-detail');
					var visitor = this.getAttribute('data-lux-action-detail');

					ajaxConnection(TYPO3.settings.ajaxUrls['/lux/detail'], {
						visitor: visitor
					}, 'showDetailCallback');
				});
			}
		};

		/**
		 * @returns {void}
		 */
		var addDescriptionListener = function() {
			var container = document.querySelector('[data-lux-container="detail"]');
			if (container !== null) {
				container.addEventListener('click', function(event) {
					var clickedElement = event.target;
					if (clickedElement.getAttribute('data-lux-visitor-description') > 0) {
						if (clickedElement.classList.contains('lux-textarea__default')) {
							clickedElement.classList.remove('lux-textarea__default');
							clickedElement.value = '';
						}
						var visitor = clickedElement.getAttribute('data-lux-visitor-description');
						clickedElement.addEventListener('blur', function() {
							ajaxConnection(TYPO3.settings.ajaxUrls['/lux/visitordescription'], {
								visitor: visitor,
								value: this.value
							}, null);
						});
					}
				});
			}
		};

		/**
		 * @returns {void}
		 */
		var addLinkMockListener = function() {
			var container = document.querySelector('[data-lux-container="detail"]');
			if (container !== null) {
				container.addEventListener('click', function (event) {
					var clickedElement = event.target;
					if (clickedElement.getAttribute('data-lux-linkmock-event') !== null) {
						var name = clickedElement.getAttribute('data-lux-linkmock-event');
						var target = document.querySelector('[data-lux-linkmock-link="' + name + '"]');
						if (target !== null) {
							target.click();
						}
					}
				});
			}
		};

		/**
		 * @params {Json} response
		 */
		this.showDetailCallback = function(response)
		{
			document.querySelector('[data-lux-container="detail"]').innerHTML = response.html;

			var container = document.querySelector('[data-lux-container="detailchart"]');
			var ctx = container.getContext('2d');
			new Chart(ctx, {
				type: 'line',
				data: {
					datasets: [{
						label: container.getAttribute('data-chart-label'),
						data: container.getAttribute('data-chart-data').split(','),
						borderColor: 'rgb(77, 231, 255)',
						"lineTension": 0.5
					}],
					labels: container.getAttribute('data-chart-labels').split(',')
				},
				options: {
					legend: {
						display: false,
						position: 'right',
						labels: {
							fontSize: 18
						}
					},
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}
			});
		};

		/**
		 * @returns {void}
		 */
		var addDatePickers = function() {
			if (document.querySelector('.t3js-datetimepicker') !== null) {
				require(['TYPO3/CMS/Backend/DateTimePicker'], function(DateTimePicker) {
					DateTimePicker.initialize();
				});
			}
		};

		/**
		 * @returns {void}
		 */
		var addWizardForm = function() {
			var fieldsets = document.querySelectorAll('.wizardform > fieldset');
			var buttons = document.querySelectorAll('[data-wizardform-gotostep]');
			var wizardLinks = document.querySelectorAll('.wizard > a');

			for (var i = 1; i < fieldsets.length; i++) {
				fieldsets[i].style.display = 'none';
			}
			for (var j = 0; j < buttons.length; j++) {
				buttons[j].addEventListener('click', function(event) {
					event.preventDefault();
					var step = this.getAttribute('data-wizardform-gotostep');

					removeClassFromElements(wizardLinks, 'current');
					wizardLinks[step-1].classList.add('current');

					for (var k = 0; k < fieldsets.length; k++) {
						fieldsets[k].style.display = 'none';
					}
					fieldsets[step-1].style.display = 'block';
				});
			}
		};

		/**
		 * @returns {void}
		 */
		var addTriggers = function() {
			var button = document.querySelector('[data-lux-action-trigger="add"]');
			if (button !== null) {
				button.addEventListener('click', function(event) {
					event.preventDefault();
					var trigger = document.querySelector('[data-lux-action-trigger="trigger"]').value;
					var index = document.querySelector('[data-lux-triggers]').getAttribute('data-lux-triggers');
					var conjunction = document.querySelector('[data-lux-action-trigger="conjunction"]').value;

					if (trigger !== '') {
						ajaxConnection(TYPO3.settings.ajaxUrls['/lux/addtrigger'], {
							trigger: trigger,
							index: index,
							conjunction: conjunction
						}, 'showHtmlInTriggerAreaCallback');
					} else {
						alert('Please choose a trigger first!');
					}
				});
			}
		};

		/**
		 * @param response
		 * @returns {void}
		 */
		this.showHtmlInTriggerAreaCallback = function(response) {
			var triggerArea = document.querySelector('[data-lux-container="triggerarea"]');
			if (triggerArea !== null) {
				triggerArea.innerHTML += response.html;
				increaseTriggerIndex();
				addDatePickers();
			}
		};

		/**
		 * @returns {void}
		 */
		var addActions = function() {
			var button = document.querySelector('[data-lux-action-action="add"]');
			if (button !== null) {
				button.addEventListener('click', function(event) {
					event.preventDefault();
					var action = document.querySelector('[data-lux-action-action="action"]').value;
					var index = document.querySelector('[data-lux-actions]').getAttribute('data-lux-actions');

					if (action !== '') {
						ajaxConnection(TYPO3.settings.ajaxUrls['/lux/addaction'], {
							action: action,
							index: index
						}, 'showHtmlInActionAreaCallback');
					} else {
						alert('Please choose an action first!');
					}
				});
			}
		};

		/**
		 * @param response
		 * @returns {void}
		 */
		this.showHtmlInActionAreaCallback = function(response) {
			var actionArea = document.querySelector('[data-lux-container="actionarea"]');
			if (actionArea !== null) {
				actionArea.innerHTML += response.html;
				increaseActionIndex();
				addDatePickers();
			}
		};

		/**
		 * @returns {void}
		 */
		var addDeleteListener = function() {
			var deleteButton = document.querySelectorAll('[data-lux-action="deleteWorkflow"]');
			for (var i = 0; i < deleteButton.length; i++) {
				deleteButton[i].addEventListener('click', function(event) {
					event.preventDefault();
					ajaxConnection(this.getAttribute('href'), {}, null);
					fadeOut(this.closest('tr'));
				});
			}
		};

		/**
		 * @returns {void}
		 */
		var increaseTriggerIndex = function() {
			var index = document.querySelector('[data-lux-triggers]').getAttribute('data-lux-triggers');
			document.querySelector('[data-lux-triggers]').setAttribute('data-lux-triggers', parseInt(index)+1)
		};

		/**
		 * @returns {void}
		 */
		var increaseActionIndex = function() {
			var index = document.querySelector('[data-lux-actions]').getAttribute('data-lux-actions');
			document.querySelector('[data-lux-actions]').setAttribute('data-lux-actions', parseInt(index)+1)
		};

		/**
		 * @param {string} elements
		 * @param {string} className
		 * @returns {void}
		 */
		var removeClassFromElements = function(elements, className) {
			for (var i = 0; i < elements.length; i++) {
				elements[i].classList.remove(className);
			}
		};

		/**
		 * @params {string} uri
		 * @params {object} parameters
		 * @params {string} target callback function name
		 * @returns {void}
		 */
		var ajaxConnection = function(uri, parameters, target) {
			if (uri !== undefined && uri !== '') {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState === 4 && this.status === 200) {
						if (target !== null) {
							that[target](JSON.parse(this.responseText));
						}
					}
				};
				xhttp.open('POST', mergeUriWithParameters(uri, parameters), true);
				xhttp.send();
			} else {
				console.log('No ajax URI given!');
			}
		};

		/**
		 * Build an uri string for an ajax call together with params from an object
		 * 		{
		 * 			'x': 123,
		 * 			'y': 'abc'
		 * 		}
		 *
		 * 		=>
		 *
		 * 		"?x=123&y=abc"
		 *
		 * @params {string} uri
		 * @params {object} parameters
		 * @returns {string} e.g. "index.php?id=123&type=123&x=123&y=abc"
		 */
		var mergeUriWithParameters = function(uri, parameters) {
			for (var key in parameters) {
				if (parameters.hasOwnProperty(key)) {
					if (uri.indexOf('?') !== -1) {
						uri += '&';
					} else {
						uri += '?';
					}
					uri += key + '=' + encodeURIComponent(parameters[key]);
				}
			}
			return uri;
		};

		/**
		 * @param element
		 * @returns {void}
		 */
		var fadeOut = function(element) {
			element.style.opacity = 1;
			(function fade() {
				if ((element.style.opacity -= .1) < 0) {
					element.style.display = 'none';
				} else {
					requestAnimationFrame(fade);
				}
			})();
		};

		/**
		 * @param element
		 * @param display Normally "block" or "inline"
		 */
		var fadeIn = function(element, display) {
			element.style.opacity = 0;
			element.style.display = display || 'block';

			(function fade() {
				var val = parseFloat(element.style.opacity);
				if (!((val += .1) > 1)) {
					element.style.opacity = val;
					requestAnimationFrame(fade);
				}
			})();
		};
	}


	/**
	 * Init
	 */
	$(document).ready(function () {
		var LuxBackendObject = new LuxBackend($);
		LuxBackendObject.initialize();
	})
});
