/**
 * LuxIdentification functions
 *
 * @class LuxMain
 */
function LuxIdentification() {
	'use strict';

	/**
	 * Fingerprint Id
	 *
	 * @type {string}
	 */
	this.fingerprint = '';

	/**
	 * @type {LuxMain}
	 */
	var that = this;

	/**
	 * @returns {string}
	 */
	this.getFingerprint = function() {
		if (this.fingerprint === '') {
			console.log('Fingerprint not yet calculated!');
		}
		return this.fingerprint;
	};

	/**
	 * @returns {void}
	 */
	this.setDisableForLinkCookie = function() {
		setCookie('luxDisableEmail4Link', true);
	};

	/**
	 * @returns {Boolean}
	 */
	this.isDisableForLinkCookieSet = function() {
		return getCookieByName('luxDisableEmail4Link') === 'true'
	};

	/**
	 * @returns {void}
	 */
	this.setTrackingOptOutStatus = function() {
		setCookie('luxTrackingOptOut', true);
	};

	/**
	 * @returns {void}
	 */
	this.removeTrackingOptOutStatus = function() {
		setCookie('luxTrackingOptOut', false);
	};

	/**
	 * @returns {Boolean} return true if trackingOptOut is set
	 */
	this.isOptOutStatusSet = function () {
		return getCookieByName('luxTrackingOptOut') === 'true';
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
	 * @returns {void}
	 */
	this.setFingerprint = function() {
		if (window.requestIdleCallback) {
			requestIdleCallback(function () {
				Fingerprint2.get(function (components) {
					var hashValue = getCombinedComponentValue(components);
					that.fingerprint = Fingerprint2.x64hash128(hashValue, 31);
				})
			})
		} else {
			setTimeout(function () {
				Fingerprint2.get(function (components) {
					var hashValue = getCombinedComponentValue(components);
					that.fingerprint = Fingerprint2.x64hash128(hashValue, 31);
				})
			}, 500)
		}
	};

	/**
	 * @param components
	 * @returns {string}
	 */
	var getCombinedComponentValue = function(components) {
		var hashValue = '';
		for (var i = 0; i < components.length; i++) {
			if (components[i].value instanceof Array) {
				var valueValueHash = '';
				for (var j = 0; j < components[i].value.length; j++) {
					valueValueHash = valueValueHash + components[i].value[j];
				}      hashValue = valueValueHash + hashValue;
			} else {
				hashValue = components[i].value + hashValue;
			}
		}
		return hashValue;
	};
}
