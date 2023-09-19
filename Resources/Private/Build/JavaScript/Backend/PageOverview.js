/**
 * LuxOverview functions
 *
 * @class LuxBackend
 */
function LuxPageOverview() {
  'use strict';

  /**
   * @type {LuxBackend}
   */
  var that = this;

  /**
   * @param {Document} optional
   * @returns {void}
   */
  this.initialize = function(dom) {
    dom = dom || document;
    toggleListener(dom);
  };

  /**
   * @returns {void}
   */
  var toggleListener = function(dom) {
    var elements = dom.querySelectorAll('[data-lux-toggle]');
    for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('click', function(event) {
        var thisElement = event.currentTarget;
        var name = thisElement.getAttribute('data-lux-toggle');
        if (thisElement.tagName === 'I') {
          thisElement = thisElement.parentNode;
        }
        var iTag = thisElement.querySelector('i');
        if (iTag.classList.contains('lux-arrow-down')) {
          iTag.classList.remove('lux-arrow-down');
          iTag.classList.add('lux-arrow-up');
          persistStatus('close', name);
        } else {
          iTag.classList.remove('lux-arrow-up');
          iTag.classList.add('lux-arrow-down');
          persistStatus('open', name);
        }
        var target = thisElement.getAttribute('data-lux-toggle');
        var targetElements = document.querySelectorAll('[data-lux-toggle-target="' + target + '"]');

        for (var j = 0; j < targetElements.length; j++) {
          targetElements[j].classList.toggle('hide');
        }
      });
    }
  };

  /**
   * @param {String} status "open" or "close"
   * @param {String} name e.g. "pageOverview"
   * @returns {void}
   */
  var persistStatus = function(status, name) {
    ajaxConnection(TYPO3.settings.ajaxUrls['/lux/pageoverview'], {status: status, name: name}, null);
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
   *    {
   * 			'x': 123,
   * 			'y': 'abc'
   * 		}
   *
   *    =>
   *
   *    "?x=123&y=abc"
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
};

var LuxPageOverviewObject = new LuxPageOverview();
LuxPageOverviewObject.initialize();




