define(['jquery'], function($) {
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
      addDetailViewListener('leadlistdetail', 'visitor');
      addDetailViewListener('analysiscontentdetailpage', 'page');
      addDetailViewListener('analysisnewsdetailpage', 'news');
      addDetailViewListener('analysisutmdetailpage', 'visitor');
      addDetailViewListener('analysissearchdetailpage', 'searchterm');
      addDetailViewListener('analysiscontentdetaildownload', 'download');
      addDetailViewListener('analysislinklistenerdetail', 'linkListener');
      addDetailViewListener('workflowdetail', 'workflow', 'luxenterprise');
      addDetailViewListener('abtestingdetail', 'abTesting', 'luxenterprise');
      addDetailViewListener('workflowurlshortenerdetail', 'urlShortener', 'luxenterprise');
      addDescriptionListener();
      addLinkMockListener();
      addConfirmListeners();
      asynchronousImageLoading();
      asynchronousLinkListenerPerformanceLoading();
      asynchronousCompaniesInformationLoading();
      addToggleListener();
    };

    /**
     * Add listener for different detail ajax views
     *
     * @params {string} name e.g. "detail" for "lux-action-detail"
     * @params {string} propertyName e.g. "visitor"
     * @returns {void}
     */
    var addDetailViewListener = function(name, propertyName, extension) {
      extension = extension || 'lux';
      var elements = document.querySelectorAll('[data-lux-action-' + name + ']');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var parameters = {};
          parameters[propertyName] = this.getAttribute('data-lux-action-' + name);
          ajaxConnection(TYPO3.settings.ajaxUrls['/' + extension + '/' + name], parameters, 'generalDetailCallback');
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
        container.addEventListener('click', function(event) {
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
    this.generalDetailCallback = function(response) {
      document.querySelector('[data-lux-container="detail"]').innerHTML = response.html;
      window.LuxDiagramObject.initialize();
    };

    /**
     * @returns {void}
     */
    var addConfirmListeners = function() {
      var elements = document.querySelectorAll('[data-lux-confirm]');
      for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('click', function(event) {
          var message = event.currentTarget.getAttribute('data-lux-confirm');
          if (confirm(message) === false) {
            event.preventDefault();
          }
        });
      }
    };

    /**
     * This allows to get visitor images (maybe from google or gravatar) as asynchronous request, to not block page
     * rendering.
     * This function is used in LUX backend modules and also in PageOverview.html
     *
     * @returns {void}
     */
    const asynchronousImageLoading = function() {
      const elements = document.querySelectorAll('[data-lux-asynchronous-image]');
      for (let i = 0; i < elements.length; i++) {
        let visitorIdentifier = elements[i].getAttribute('data-lux-asynchronous-image');
        if (visitorIdentifier > 0) {
          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/visitorimage'], {
            visitor: visitorIdentifier
          }, 'asynchronousImageLoadingCallback', {element: elements[i]});
        }
      }
    };

    /**
     * @params {Json} response
     */
    this.asynchronousImageLoadingCallback = function(response, callbackArguments) {
      if (callbackArguments.element instanceof HTMLImageElement) {
        callbackArguments.element.setAttribute('src', response.url)
      }
    };

    /**
     * Because performance column in linklistener list view table is a real downside for the web performance, we try
     * to load those values via AJAX
     *
     * @returns {void}
     */
    const asynchronousLinkListenerPerformanceLoading = function() {
      const elements = document.querySelectorAll('[data-lux-getlinklistenerperformance]');
      elements.forEach(function(element) {
        let linkListener = element.getAttribute('data-lux-getlinklistenerperformance');
        if (linkListener > 0) {
          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/linklistenerperformance'], {
            linkListener: linkListener
          }, 'asynchronousLinkListenerPerformanceLoadingCallback', {element: element});
        }
      });
    };

    /**
     * @params {Json} response
     */
    this.asynchronousLinkListenerPerformanceLoadingCallback = function(response, callbackArguments) {
      const performance = (response.performance * 100).toFixed(1);
      callbackArguments.element.innerHTML = performance + ' %';
    };

    /**
     * @returns {void}
     */
    const asynchronousCompaniesInformationLoading = function() {
      const elements = document.querySelectorAll('[data-lux-getcompaniesinformation-numberofvisits]');
      elements.forEach(function(element) {
        let company = element.getAttribute('data-lux-getcompaniesinformation-numberofvisits');
        if (company > 0) {
          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/companiesinformation'], {
            company: company
          }, 'asynchronousCompaniesInformationLoadingCallback', {element: element});
        }
      });
    };

    /**
     * @params {Json} response
     */
    this.asynchronousCompaniesInformationLoadingCallback = function(response, callbackArguments) {
      const target = callbackArguments.element;
      target.innerHTML = response.numberOfVisits;
      const target2 = target.closest('tr').querySelector('[data-lux-getcompaniesinformation-numberofvisitors]');
      target2.innerHTML = response.numberOfVisitors;
    };

    /**
     * Toggle elements
     *
     * Switches with [data-lux-action-toggleaction="anything"]
     * Toggles all targets with [data-lux-action-togglecontainer="anything"]
     *
     * @returns {void}
     */
    const addToggleListener = function() {
      const elements = document.querySelectorAll('[data-lux-action-toggleaction]');
      elements.forEach(function(element) {
        element.addEventListener('click', function(event) {
          let identifier = event.currentTarget.getAttribute('data-lux-action-toggleaction');
          let targetElements = document.querySelectorAll('[data-lux-action-togglecontainer="' + identifier + '"]');
          targetElements.forEach(function(targetElement) {
            targetElement.classList.toggle('hidden');
          });
        });
      });
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
     * @params {string} callback function name
     * @returns {void}
     */
    var ajaxConnection = function(uri, parameters, callback, callbackArguments) {
      callbackArguments = callbackArguments || {};
      if (uri !== undefined && uri !== '') {
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
  }


  /**
   * Init
   */
  $(document).ready(function() {
    var LuxBackendObject = new LuxBackend($);
    LuxBackendObject.initialize();
  })
});
