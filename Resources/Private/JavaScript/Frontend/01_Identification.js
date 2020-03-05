/**
 * LuxIdentification functions
 *
 * @class LuxMain
 */
function LuxIdentification() {
	'use strict';

	/**
	 * Cookie Id
	 *
	 * @type {string}
	 */
	this.idCookie = '';

	/**
	 * @type {string}
	 */
	var cookieName = 'luxId';

	/**
	 * @returns {void}
	 */
	this.setIdCookieProperty = function() {
		this.idCookie = this.getIdCookie();
	};

	/**
	 * @returns {void}
	 */
	this.setIdCookie = function() {
		this.idCookie = getRandomString(32);
		setCookie(cookieName, this.idCookie);
	};

	/**
	 * @returns {string}
	 */
	this.getIdCookie = function() {
		return getCookieByName(cookieName);
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
}
