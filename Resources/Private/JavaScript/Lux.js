/**
 * LuxMain functions
 *
 * @class LuxMain
 */
function LuxMain() {
	'use strict';

	/**
	 * @type {string}
	 */
	var cookieName = 'luxId';

	/**
	 * Cookie Id
	 *
	 * @type {string}
	 */
	var idCookie = '';

	/**
	 * @type {null}
	 */
	this.lightboxInstance = null;

	/**
	 * @type {LuxMain}
	 */
	var that = this;

	/**
	 * Initialize
	 *
	 * @returns {void}
	 */
	this.initialize = function() {
		trackingOptOutListener();
		if (isLuxActivated()) {
			setIdCookieProperty();
			setCookieIfNoCookieSetAndIfAllowed();
			pageRequest();
			addFieldListeners();
			addFormListeners();
			addDownloadListener();
		}
		addEmail4LinkListeners();
		doNotTrackListener();
		createIdCookieListener();
	};

	/**
	 * Close any lightbox
	 */
	this.closeLightbox = function() {
		if (that.lightboxInstance !== null) {
			that.lightboxInstance.close();
		}
	};

	/**
	 * @returns {void}
	 */
	var trackingOptOutListener = function() {
		var elements = document.querySelectorAll('[data-lux-trackingoptout="checkbox"]');
		for (var i = 0; i < elements.length; i++) {
			var element = elements[i];
			// check/uncheck checkbox with data-lux-trackingoptout="checkbox". Check if tracking is allowed.
			element.checked = isOptOutStatusSet() === false;
			element.addEventListener('change', function() {
				if (isOptOutStatusSet()) {
					removeTrackingOptOutStatus();
				} else {
					setTrackingOptOutStatus();
				}
			});
		}
	};

	/**
	 * @returns {void}
	 */
	var setIdCookieProperty = function() {
		idCookie = getIdCookie();
	};

	/**
	 * @returns {void}
	 */
	var pageRequest = function() {
		if (isPageTrackingEnabled()) {
			ajaxConnection({
				'tx_lux_fe[dispatchAction]': 'pageRequest',
				'tx_lux_fe[idCookie]': getIdCookie(),
				'tx_lux_fe[arguments][pageUid]': getPageUid(),
				'tx_lux_fe[arguments][referrer]': getReferrer(),
				'tx_lux_fe[arguments][currentUrl]': encodeURIComponent(window.location.href),
			}, getRequestUri(), 'generalWorkflowActionCallback', null);
		}
	};

	/**
	 * Callback and dispatcher function for all workflow actions (part of the Enterprise Edition)
	 *
	 * @params {Json} response
	 * @returns {void}
	 */
	this.generalWorkflowActionCallback = function(response) {
		for (var i = 0; i < response.length; i++) {
			if (response[i]['action']) {
				try {
					that[response[i]['action'] + 'WorkflowAction'](response[i]);
				} catch (error) {
					console.log(error);
				}
			}
		}
	};

	/**
	 * Callback for workflow action "LightboxContent" (part of the Enterprise Edition)
	 *
	 * @param response
	 */
	this.lightboxContentWorkflowAction = function(response) {
		var contentElementUid = response['configuration']['contentElement'];
		var uri = document.querySelector('[data-lux-contenturi]').getAttribute('data-lux-contenturi')
			|| '/index.php?id=1&type=1520192598';
		var html =
			'<div><iframe src="' + uri + '&luxContent=' + parseInt(contentElementUid) + '" width="800" height="600">' +
			'</iframe></div>';
		that.lightboxInstance = basicLightbox.create(html);
		setTimeout(function() {
			that.lightboxInstance.show();
		}, parseInt(response['configuration']['delay']));
	};

	/**
	 * Callback for workflow action "Redirect" (part of the Enterprise Edition)
	 *
	 * @param response
	 */
	this.redirectWorkflowAction = function(response) {
		if (response['configuration']['uri']) {
			window.location = response['configuration']['uri'];
		}
	};

	/**
	 * Callback for workflow action "AjaxContent" (part of the Enterprise Edition)
	 *
	 * @param response
	 */
	this.ajaxContentWorkflowAction = function(response) {
		var uri = document.querySelector('[data-lux-contenturiwithoutheader]')
				.getAttribute('data-lux-contenturiwithoutheader') || '/index.php?id=1&type=1560175278';
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState === 4 && this.status === 200) {
				var domSelection = document.querySelector(response['configuration']['domselection']);
				if (domSelection !== null) {
					domSelection.innerHTML = this.responseText;
				} else {
					console.log('Element ' + response['configuration']['domselection'] + ' could not be found in HTML');
				}
			}
		};
		xhttp.open(
			'POST',
			mergeUriWithParameters(uri, {'luxContent': parseInt(response['configuration']['contentElement'])}),
			true
		);
		xhttp.send();
	};

	/**
	 * Callback for workflow action "Showorhide" (part of the Enterprise Edition)
	 *
	 * @param response
	 */
	this.showorhideWorkflowAction = function(response) {
		var domSelection = document.querySelector(response['configuration']['domselection']);
		if (domSelection !== null) {
			if (response['configuration']['showorhide'] === 'hide') {
				domSelection.style.display = 'none';
			} else if (response['configuration']['showorhide'] === 'show') {
				domSelection.style.display = 'block';
			}
		} else {
			console.log('Element ' + response['configuration']['domselection'] + ' could not be found in HTML');
		}
	};

	/**
	 * Not a real workflowAction but more a finisher action to stop asking for email addresses on email4link clicks if
	 * the visitor is already known (only with a cookie "luxDisableEmail4Link"
	 *
	 * @param response
	 */
	this.disableEmail4LinkWorkflowAction = function(response) {
		setCookie('luxDisableEmail4Link', true);
	};

	/**
	 * @returns {void}
	 */
	var addFieldListeners = function() {
		var query = 'form:not([data-lux-form-identification]) input:not([data-lux-disable]), ';
		query += 'form:not([data-lux-form-identification]) textarea:not([data-lux-disable]), ';
		query += 'form:not([data-lux-form-identification]) select:not([data-lux-disable]), ';
		query += 'form:not([data-lux-form-identification]) radio:not([data-lux-disable]), ';
		query += 'form:not([data-lux-form-identification]) check:not([data-lux-disable])';
		var elements = document.querySelectorAll(query);
		for (var i = 0; i < elements.length; i++) {
			var element = elements[i];
			// Skip every password field and check if this field is configured for listening in TypoScript
			if (element.type !== 'password' && isFieldConfiguredInFieldMapping(element)) {
				element.addEventListener('change', function() {
					fieldListener(this);
				});
			}
		}
	};

	/**
	 * @returns {void}
	 */
	var addFormListeners = function() {
		var forms = document.querySelectorAll('form[data-lux-form-identification]');
		for (var i = 0; i < forms.length; i++) {
			forms[i].addEventListener('submit', function(event) {
				if (event.target.getAttribute('data-lux-form-identification') === 'preventDefault') {
					event.preventDefault();
				}
				sendFormValues(event.target);
			});
		}
	};

	/**
	 * @returns {void}
	 */
	var addEmail4LinkListeners = function() {
		var links = document.querySelectorAll('[data-lux-email4link-title]');
		for (var i = 0; i < links.length; i++) {
			var element = links[i];
			element.addEventListener('click', function(event) {
				email4LinkListener(this, event);
			});
		}
	};

	/**
	 * @returns {void}
	 */
	var addDownloadListener = function() {
		if (isDownloadTrackingEnabled()) {
			var links = document.querySelectorAll(getExpressionForLinkSelection());
			var href;
			for (var i = 0; i < links.length; i++) {
				if (!links[i].hasAttribute('data-lux-email4link-title')) {
					href = links[i].getAttribute('href');
					links[i].addEventListener('click', function() {
						ajaxConnection({
							'tx_lux_fe[dispatchAction]': 'downloadRequest',
							'tx_lux_fe[idCookie]': getIdCookie(),
							'tx_lux_fe[arguments][href]': this.getAttribute('href')
						}, getRequestUri(), null, null);
					});
				}
			}
		}
	};

	/**
	 * @param {Node} link
	 * @param event
	 * @returns {void}
	 */
	var email4LinkListener = function(link, event) {
		if (getCookieByName('luxDisableEmail4Link') !== 'true') {
			event.preventDefault();

			var title = link.getAttribute('data-lux-email4link-title') || '';
			var text = link.getAttribute('data-lux-email4link-text') || '';
			var href = link.getAttribute('href');
			var containers = document.querySelectorAll('[data-lux-container="email4link"]');
			if (containers.length > 0) {
				var container = containers[0].cloneNode(true);
				var html = container.innerHTML;
				html = html.replace('###TITLE###', title);
				html = html.replace('###TEXT###', text);
				html = html.replace('###HREF###', getFilenameFromHref(href));
				that.lightboxInstance = basicLightbox.create(html);
				that.lightboxInstance.element().querySelector('[data-lux-email4link="form"]').addEventListener('submit', function(event) {
					email4LinkLightboxSubmitListener(this, event, link);
				});
				that.lightboxInstance.show();
			}
		}
	};

	/**
	 * Callback function if lightbox should be submitted
	 *
	 * @param {Node} element
	 * @param event
	 * @param {Node} link
	 * @returns {void}
	 */
	var email4LinkLightboxSubmitListener = function(element, event, link) {
		event.preventDefault();
		var href = link.getAttribute('href');
		var sendEmail = link.getAttribute('data-lux-email4link-sendemail') || 'false';
		var email = that.lightboxInstance.element().querySelector('[data-lux-email4link="email"]').value;
		if (isEmailAddress(email)) {
			addWaitClassToBodyTag();
			ajaxConnection({
				'tx_lux_fe[dispatchAction]': 'email4LinkRequest',
				'tx_lux_fe[idCookie]': getIdCookie(),
				'tx_lux_fe[arguments][email]': email,
				'tx_lux_fe[arguments][sendEmail]': sendEmail === 'true',
				'tx_lux_fe[arguments][href]': href
			}, getRequestUri(), 'email4LinkLightboxSubmitCallback', {sendEmail:(sendEmail === 'true'), href:href});
		} else {
			showElement(that.lightboxInstance.element().querySelector('[data-lux-email4link="errorEmailAddress"]'));
		}
	};

	/**
	 * Callback for email4LinkLightboxSubmitListener
	 *
	 * @param response
	 * @param callbackArguments
	 * @returns {void}
	 */
	this.email4LinkLightboxSubmitCallback = function(response, callbackArguments) {
		removeWaitClassToBodyTag();

		if (response.error === true) {
			showElement(that.lightboxInstance.element().querySelector('[data-lux-email4link="errorEmailAddress"]'));
		} else {
			if (callbackArguments.sendEmail === true) {
				hideElement(that.lightboxInstance.element().querySelector('[data-lux-email4link="form"]'));
				showElement(
					that.lightboxInstance.element().querySelector('[data-lux-email4link="successMessageSendEmail"]')
				);
				setTimeout(function() {
					that.lightboxInstance.close();
				}, 2000);
			} else {
				setTimeout(function() {
					that.lightboxInstance.close();
					window.location = callbackArguments.href;
				}, 500);
			}
		}
	};

	/**
	 * @param {Node} field
	 * @returns {void}
	 */
	var fieldListener = function(field) {
		var key = getKeyOfFieldConfigurationToGivenField(field, getFieldMapping());
		var value = field.value;
		ajaxConnection({
			'tx_lux_fe[dispatchAction]': 'fieldListeningRequest',
			'tx_lux_fe[idCookie]': getIdCookie(),
			'tx_lux_fe[arguments][key]': key,
			'tx_lux_fe[arguments][value]': value
		}, getRequestUri(), 'generalWorkflowActionCallback', null);
	};

	/**
	 * @param {Node} form
	 * @returns {void}
	 */
	var sendFormValues = function(form) {
		var formArguments = {};
		for (var i = 0; i < form.elements.length; i++) {
			var field = form.elements[i];
			var key = getKeyOfFieldConfigurationToGivenField(field, getFormFieldMapping());
			if (key !== '') {
				formArguments[key] = field.value;
			}
		}

		ajaxConnection({
			'tx_lux_fe[dispatchAction]': 'formListeningRequest',
			'tx_lux_fe[idCookie]': getIdCookie(),
			'tx_lux_fe[arguments][values]': JSON.stringify(formArguments)
		}, getRequestUri(), 'generalWorkflowActionCallback', null);
	};

	/**
	 * @returns {void}
	 */
	var doNotTrackListener = function() {
		if (navigator.doNotTrack === '1') {
			var text = document.querySelectorAll('[data-lux-container-optout="text"]');
			for (var i = 0; i < text.length; i++) {
				hideElement(text[i]);
			}
			var textDoNotTrack = document.querySelectorAll('[data-lux-container-optout="textDoNotTrack"]');
			for (var j = 0; j < textDoNotTrack.length; j++) {
				showElement(textDoNotTrack[j]);
			}
		}
	};

	/**
	 * If an id cookie should be set manually, listen for clicks on dom elements with data-lux-action="createIdCookie"
	 *
	 * @returns {void}
	 */
	var createIdCookieListener = function() {
		var element = document.querySelector('[data-lux-action="createIdCookie"]');
		if (element !== null) {
			element.addEventListener('click', function() {
				if (idCookie === '') {
					setIdCookie();
				}
			});
		}
	};

	/**
	 * @param field
	 * @returns {boolean}
	 */
	var isFieldConfiguredInFieldMapping = function(field) {
		return getKeyOfFieldConfigurationToGivenField(field, getFieldMapping()) !== '';
	};

	/**
	 * Pass a field element and check if this field is configured in TypoScript in field mapping. If found get key of
	 * the configuration. Oherwise return an empty string.
	 *
	 * @param field
	 * @param fieldMapping
	 * @returns {string}
	 */
	var getKeyOfFieldConfigurationToGivenField = function(field, fieldMapping) {
		var keyConfiguration = '';
		var fieldName = field.name;
		for (var key in fieldMapping) {
			// iterate through fieldtypes
			if (fieldMapping.hasOwnProperty(key)) {
				// iterate through every fieldtype definition
				for (var i = 0; i < fieldMapping[key].length; i++) {
					if (matchStringInString(fieldName, fieldMapping[key][i])) {
						keyConfiguration = key;
					}
				}
			}
		}
		return keyConfiguration;
	};

	/**
	 * Return an expression for a querySelectorAll function to select all download links
	 * Like 'a[href$="jpg"],a[href$="pdf"]'
	 *
	 * @returns {String}
	 */
	var getExpressionForLinkSelection = function() {
		var extensions = getContainer().getAttribute('data-lux-downloadtracking-extensions').toLowerCase().split(',');
		return 'a[href$="' + extensions.join('"],a[href$="') + '"]';
	};

	/**
	 * Check if string is identically to another string. But if there is a "*", check if the string is part of another
	 * string
	 *
	 * @param haystack
	 * @param needle
	 * @returns {boolean}
	 */
	var matchStringInString = function(haystack, needle) {
		if (needle.indexOf('*') !== -1) {
			needle = needle.replace('*', '');
			var found = haystack.indexOf(needle) !== -1;
		} else {
			found = haystack === needle;
		}
		return found;
	};

	/**
	 * @returns {object}
	 */
	var getFieldMapping = function() {
		var json = {};
		try {
			json = JSON.parse(window.luxFieldMappingConfiguration);
		} catch(err) {
			console.log('Lux: No fieldmapping configuration given.');
		}
		return json;
	};

	/**
	 * @returns {object}
	 */
	var getFormFieldMapping = function() {
		var json = {};
		try {
			json = JSON.parse(window.luxFormFieldMappingConfiguration);
		} catch(err) {
			console.log('Lux: No formfieldmapping configuration given.');
		}
		return json;
	};

	/**
	 * @returns {boolean}
	 */
	var isPageTrackingEnabled = function() {
		var enabled = false;
		var container = getContainer();
		if (container !== null) {
			if (container.hasAttribute('data-lux-pagetracking')) {
				var pageTrackingEnabled = container.getAttribute('data-lux-pagetracking');
				enabled = pageTrackingEnabled === '1';
			}
		}
		return enabled;
	};

	/**
	 * @returns {boolean}
	 */
	var isDownloadTrackingEnabled = function() {
		var enabled = false;
		var container = getContainer();
		if (container !== null) {
			if (container.hasAttribute('data-lux-downloadtracking')) {
				var trackingEnabled = container.getAttribute('data-lux-downloadtracking');
				enabled = trackingEnabled === '1';
			}
		}
		return enabled;
	};

	/**
	 * @returns {int}
	 */
	var getPageUid = function() {
		var uid = 0;
		var container = getContainer();
		if (container !== null) {
			if (container.hasAttribute('data-lux-pageuid')) {
				var uidContainer = container.getAttribute('data-lux-pageuid');
				uid = parseInt(uidContainer);
			}
		}
		return uid;
	};

	/**
	 * @returns {string}
	 */
	var getReferrer = function() {
		return encodeURIComponent(document.referrer);
	};

	/**
	 * @returns {void}
	 */
	var setCookieIfNoCookieSetAndIfAllowed = function() {
		if (idCookie === '' && getContainer().getAttribute('data-lux-enableautocookie') === '1') {
			setIdCookie();
		}
	};

	/**
	 * @returns {string}
	 */
	var getRequestUri = function() {
		var container = getContainer();
		if (container !== null) {
			return container.getAttribute('data-lux-requesturi');
		}
		return '';
	};

	/**
	 * @params {object} parameters
	 * @params {string} uri
	 * @params {string} callback
	 * @params {object} callbackArguments
	 * @returns {void}
	 */
	var ajaxConnection = function(parameters, uri, callback, callbackArguments) {
		callbackArguments = callbackArguments || {};
		if (uri !== '') {
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState === 4 && this.status === 200) {
					if (callback !== null) {
						that[callback](JSON.parse(this.responseText), callbackArguments);
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
				uri += key + '=' + parameters[key];
			}
		}
		return uri;
	};

	/**
	 * Check if tracking is possible - when
	 * - optOutStatus is not set (cookie)
	 * - doNotTrack header ist not set
	 * - container with important serverside information is available in DOM
	 * - data-lux-enable="1"
	 *
	 * @returns {boolean}
	 */
	var isLuxActivated = function() {
		return isOptOutStatusSet() === false && navigator.doNotTrack !== '1' && getContainer() !== null
			&& getContainer().getAttribute('data-lux-enable') === '1';
	};

	/**
	 * @returns {object}
	 */
	var getContainer = function() {
		return document.getElementById('lux_container');
	};

	/**
	 * @returns {void}
	 */
	var addWaitClassToBodyTag = function() {
		document.body.className += ' ' + 'lux_waiting';
	};

	/**
	 * @returns {void}
	 */
	var removeWaitClassToBodyTag = function() {
		document.body.classList.remove('lux_waiting');
	};

	/**
	 * @param {Node} element
	 * @returns {void}
	 */
	var hideElement = function(element) {
		element.style.display = 'none';
	};

	/**
	 * @param {Node} element
	 * @returns {void}
	 */
	var showElement = function(element) {
		element.style.display = 'block';
	};

	/**
	 * @param {int} length
	 * @returns {string}
	 */
	var getRandomString = function(length) {
		var text = '';
		var possible = 'abcdefghijklmnopqrstuvwxyz0123456789';
		for (var i = 0; i < length; i++) {
			text += possible.charAt(Math.floor(Math.random() * possible.length));
		}
		return text;
	};

	/**
	 * Check if string is an email
	 *
	 * @param email
	 * @returns {boolean}
	 */
	var isEmailAddress = function(email) {
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	};

	/**
	 * Just show the filename instead of the complete path - but only for asset downloads and not for links to pages
	 * or folders
	 *
	 * @param {String} href
	 * @returns {String}
	 */
	var getFilenameFromHref = function(href) {
		var filename = href.replace(/^.*[\\\/]/, '');
		var fileExtensions = [
			'pdf',
			'txt',
			'doc',
			'docx',
			'xls',
			'xlsx',
			'ppt',
			'pptx',
			'jpg',
			'png',
			'zip'
		];
		if (inArray(getFileExtension(filename).toLowerCase(), fileExtensions)) {
			href = filename;
		}
		return href;
	};

	/**
	 * @param {String} needle
	 * @param {Array} haystack
	 * @returns {boolean}
	 */
	var inArray = function(needle, haystack) {
		var length = haystack.length;
		for (var i = 0; i < length; i++) {
			if (haystack[i] === needle) return true;
		}
		return false;
	};

	/**
	 * @param {String} filename
	 * @returns {String}
	 */
	var getFileExtension = function(filename) {
		if (filename.indexOf('.') !== -1) {
			return filename.split('.').pop();
		}
		return '';
	};

	/**
	 * @returns {Boolean} return true if trackingOptOut is set
	 */
	var isOptOutStatusSet = function () {
		return getCookieByName('luxTrackingOptOut') === 'true';
	};

	/**
	 * @returns {void}
	 */
	var setTrackingOptOutStatus = function() {
		setCookie('luxTrackingOptOut', true);
	};

	/**
	 * @returns {void}
	 */
	var removeTrackingOptOutStatus = function() {
		setCookie('luxTrackingOptOut', false);
	};

	/**
	 * @returns {string}
	 */
	var getIdCookie = function() {
		return getCookieByName(cookieName);
	};

	/**
	 * @returns {void}
	 */
	var setIdCookie = function() {
		idCookie = getRandomString(32);
		setCookie(cookieName, idCookie);
	};

	/**
	 * @param {string} name
	 * @param value
	 * @returns {void}
	 */
	var setCookie = function(name, value) {
		var now = new Date();
		var time = now.getTime();
		time += 3600 * 24 * 365 * 10000; // 10 years from now
		now.setTime(time);
		document.cookie = name + '=' + value + '; expires=' + now.toUTCString() + '; path=/';
	};

	/**
	 * Get cookie value by its name
	 *
	 * @param cookieName
	 * @returns {string}
	 */
	var getCookieByName = function(cookieName) {
		var name = cookieName + '=';
		var ca = document.cookie.split(';');
		for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) === 0) {
				return c.substring(name.length, c.length);
			}
		}
		return '';
	};
}

var Lux = new window.LuxMain();
Lux.initialize();
