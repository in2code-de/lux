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
   * Initialize
   *
   * @returns {void}
   */
  this.initialize = function() {
    toggleListener();
  };

  /**
   * @returns {void}
   */
  var toggleListener = function() {
    var elements = document.querySelectorAll('[data-lux-toggle]');
    for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('click', function(event) {
        var thisElement = event.target;
        if (thisElement.tagName === 'I') {
          thisElement = thisElement.parentNode;
        }
        var iTag = thisElement.querySelector('i');
        if (iTag.classList.contains('fa-chevron-down')) {
          iTag.classList.remove('fa-chevron-down');
          iTag.classList.add('fa-chevron-up');
          persistStatus('close');
        } else {
          iTag.classList.remove('fa-chevron-up');
          iTag.classList.add('fa-chevron-down');
          persistStatus('open');
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
   * @returns {void}
   */
  var persistStatus = function(status) {
    ajaxConnection(TYPO3.settings.ajaxUrls['/lux/pageoverview'], {status: status}, null);
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




