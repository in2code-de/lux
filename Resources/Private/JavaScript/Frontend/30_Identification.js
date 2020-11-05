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
  this.getFingerprint = function () {
    if (this.isFingerprintSet() === false) {
      console.log('Fingerprint not yet calculated!');
    }
    return this.fingerprint;
  };

  /**
   * @returns {boolean}
   */
  this.isFingerprintSet = function () {
    return this.fingerprint !== '';
  };

  /**
   * @returns {void}
   */
  this.setDisableForLinkStorageEntry = function () {
    addLocalStorageEntry('luxDisableEmail4Link', true);
  };

  /**
   * @returns {Boolean}
   */
  this.isDisableForLinkStorageEntrySet = function () {
    return getLocalStorageEntryByName('luxDisableEmail4Link') === 'true'
  };

  /**
   * @returns {void}
   */
  this.setTrackingOptOutStatus = function () {
    addLocalStorageEntry('luxTrackingOptOut', true);
  };

  /**
   * @returns {void}
   */
  this.removeTrackingOptOutStatus = function () {
    addLocalStorageEntry('luxTrackingOptOut', false);
  };

  /**
   * @returns {Boolean} return true if trackingOptOut is set
   */
  this.isOptOutStatusSet = function () {
    return getLocalStorageEntryByName('luxTrackingOptOut') === 'true';
  };

  /**
   * @returns {void}
   */
  this.setTrackingOptInStatus = function () {
    addLocalStorageEntry('luxTrackingOptIn', true);
  };

  /**
   * @returns {void}
   */
  this.removeTrackingOptInStatus = function () {
    addLocalStorageEntry('luxTrackingOptIn', false);
  };

  /**
   * @returns {Boolean} return true if trackingOptIn is set
   */
  this.isOptInStatusSet = function () {
    return getLocalStorageEntryByName('luxTrackingOptIn') === 'true';
  };

  /**
   * @param {string} key
   * @returns {string}
   */
  var getLocalStorageEntryByName = function (key) {
    return localStorage.getItem(key);
  };

  /**
   * @param {string} key
   * @param value
   * @returns {void}
   */
  var addLocalStorageEntry = function (key, value) {
    localStorage.setItem(key, value);
  };

  /**
   * Preflight for setting fingerprint from calculated hash after a timeout
   *
   * @returns {void}
   */
  this.setFingerprint = function () {
    var overruleFingerprint = getOverruleFingerprint();
    if (overruleFingerprint === '') {
      if (window.requestIdleCallback) {
        requestIdleCallback(function () {
          callFingerprintFunctionAndSetValue();
        })
      } else {
        setTimeout(function () {
          callFingerprintFunctionAndSetValue();
        }, 500)
      }
    } else {
      this.fingerprint = overruleFingerprint;
    }
  };

  /**
   * Set fingerprint from calculated hash without browser version
   *
   * @returns {void}
   */
  var callFingerprintFunctionAndSetValue = function () {
    Fingerprint2.get({
      preprocessor: function (key, value) {
        if (key === 'userAgent') {
          var parser = new UAParser(value);
          var userAgentWithoutVersion = parser.getOS().name + ' ' + parser.getBrowser().name;
          return userAgentWithoutVersion;
        }
        return value
      }
    }, function (components) {
      var hashValue = getCombinedComponentValue(components);
      that.fingerprint = Fingerprint2.x64hash128(hashValue, 31);
      if (isDebugMode() === true) {
        console.log('Debug: Fingerprint values', components);
        console.log('Debug: Fingerprint is "' + that.fingerprint + '"');
      }
    });
  };

  /**
   * Overrule from GET param ?luxfingerprint=abc
   * or from cookie with name "luxfingerprint"
   *
   * @returns {string}
   */
  var getOverruleFingerprint = function () {
    var overruleFingerprint = findGetParameter('luxfingerprint');
    if (overruleFingerprint === '') {
      overruleFingerprint = getCookieByName('luxfingerprint');
    }
    return overruleFingerprint;
  };

  /**
   * @param components
   * @returns {string}
   */
  var getCombinedComponentValue = function (components) {
    var hashValue = '';
    for (var i = 0; i < components.length; i++) {
      if (components[i].value instanceof Array) {
        var valueValueHash = '';
        for (var j = 0; j < components[i].value.length; j++) {
          valueValueHash = valueValueHash + components[i].value[j];
        }
        hashValue = valueValueHash + hashValue;
      } else {
        hashValue = components[i].value + hashValue;
      }
    }
    return hashValue;
  };

  /**
   * Get value from GET param
   *
   * @param {string} parameterName
   * @returns {string}
   */
  var findGetParameter = function (parameterName) {
    var result = '',
      tmp = [];
    location.search
      .substr(1)
      .split('&')
      .forEach(function (item) {
        tmp = item.split('=');
        if (tmp[0] === parameterName) {
          result = decodeURIComponent(tmp[1]);
        }
      });
    return result;
  };

  /**
   * Get cookie value by its name
   *
   * @param cookieName
   * @returns {string}
   */
  var getCookieByName = function (cookieName) {
    var name = cookieName + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
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
   * Search for text "ENABLELUXDEBUG" anywhere on the website to show some debug information
   *
   * @returns {boolean}
   */
  var isDebugMode = function () {
    return document.body.innerHTML.search('ENABLELUXDEBUG') !== -1;
  }
}
