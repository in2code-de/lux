import { getFingerprint, setOption, getFingerprintData } from '@thumbmarkjs/thumbmarkjs'
import md5 from 'md5'

/**
 * LuxIdentification functions
 *
 * @class LuxMain
 */
export default function LuxIdentification() {
  'use strict';

  const localStorageNameTracking = 'luxTracking';
  const localStorageNameDisableEmail4Link = 'luxDisableEmail4Link';
  const localStorageNameLuxId = 'luxId';
  const coookieNameDebug = 'ENABLELUXDEBUG';

  /**
   * Fingerprint (32 characters) or Local Storage Id (33 characters)
   *
   * @type {string}
   */
  this.identificator = '';

  /**
   * @type {LuxMain}
   */
  var that = this;

  /**
   * @returns {string}
   */
  this.getIdentificator = function () {
    if (this.isIdentificatorSet() === false) {
      console.log('Identificator (Fingerprint?) not yet calculated!');
    }
    return this.identificator;
  };

  /**
   * @returns {boolean}
   */
  this.isIdentificatorSet = function () {
    return this.identificator !== '';
  };

  /**
   * @returns {void}
   */
  this.setDisableForLinkStorageEntry = function () {
    addLocalStorageEntry(localStorageNameDisableEmail4Link, true);
  };

  /**
   * @returns {Boolean}
   */
  this.isDisableForLinkStorageEntrySet = function () {
    return getLocalStorageEntryByName(localStorageNameDisableEmail4Link) === 'true'
  };

  /**
   * @returns {void}
   */
  this.setTrackingOptOutStatus = function () {
    addLocalStorageEntry(localStorageNameTracking, false);
  };

  /**
   * @returns {void}
   */
  this.setTrackingOptInStatus = function () {
    addLocalStorageEntry(localStorageNameTracking, true);
  };

  /**
   * @returns {Boolean} return true if trackingOptOut is set
   */
  this.isOptOutStatusSet = function () {
    return getLocalStorageEntryByName(localStorageNameTracking) === 'false';
  };

  /**
   * @returns {Boolean} return true if trackingOptIn is set
   */
  this.isOptInStatusSet = function () {
    return getLocalStorageEntryByName(localStorageNameTracking) === 'true';
  };

  /**
   * @returns {Boolean} return true if trackingOptIn is set
   */
  this.isDebugCookieSet = function () {
    return getCookieByName(coookieNameDebug) !== '';
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
   * @param type 0=fingerprint, 2=localstorage
   * @returns {void}
   */
  this.setIdentificator = function(type) {
    if (type === 2) {
      setLocalStorageIdentificator();
    } else {
      setFingerprintIdentificator();
    }
  };

  /**
   * @returns {void}
   */
  var setLocalStorageIdentificator = function () {
    var identificator = getLocalStorageEntryByName(localStorageNameLuxId);
    if (identificator === null) {
      identificator = getRandomString(33);
      addLocalStorageEntry(localStorageNameLuxId, identificator);
    }
    that.identificator = identificator;
  }

  /**
   * Preflight for setting fingerprint from calculated hash after a timeout
   *
   * @returns {void}
   */
  var setFingerprintIdentificator = function () {
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
      this.identificator = overruleFingerprint;
    }
  };

  /**
   * Set fingerprint from calculated hash without browser version
   *
   * @returns {void}
   */
  var callFingerprintFunctionAndSetValue = function () {
    setOption('exclude', ['system.browser.version'])
    getFingerprint().then((fingerprint) => {
      that.identificator = md5(fingerprint);

      if (isDebugMode() === true) {
        console.log('Debug: Fingerprint values', getFingerprintData());
        console.log('Debug: Fingerprint is "' + that.identificator + '"');
      }
    })
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
   * Is debug mode activated?
   * - Check if a cookie with name "ENABLELUXDEBUG" is given
   * - Search for text "ENABLELUXDEBUG" anywhere on the website
   *
   * @returns {boolean}
   */
  var isDebugMode = function () {
    return that.isDebugCookieSet() || document.body.innerHTML.search(coookieNameDebug) !== -1;
  }
}
