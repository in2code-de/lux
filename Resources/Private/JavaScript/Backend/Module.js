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
      addLeadListDetailViewListener();
      addAnalysisContentDetailPageViewListener();
      addAnalysisNewsDetailPageViewListener();
      addAnalysisContentDetailDownloadViewListener();
      addAnalysisLinkListenerDetailViewListener();
      addWorkflowUrlShortenerDetailViewListener();
      addDescriptionListener();
      addLinkMockListener();
      addDatePickers();
      addConfirmListeners();
    };

    /**
     * Add listener for lead/list detail ajax view
     *
     * @returns {void}
     */
    var addLeadListDetailViewListener = function() {
      var elements = document.querySelectorAll('[data-lux-action-leadlistdetail]');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var visitor = this.getAttribute('data-lux-action-leadlistdetail');

          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/leadlistdetail'], {
            visitor: visitor
          }, 'generalDetailCallback');
        });
      }
    };

    /**
     * Add listener for analysis/content (page) detail ajax view
     *
     * @returns {void}
     */
    var addAnalysisContentDetailPageViewListener = function() {
      var elements = document.querySelectorAll('[data-lux-action-analysiscontentdetailpage]');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var page = this.getAttribute('data-lux-action-analysiscontentdetailpage');

          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/analysiscontentdetailpage'], {
            page: page
          }, 'generalDetailCallback');
        });
      }
    };

    /**
     * Add listener for analysis/content (news) detail ajax view
     *
     * @returns {void}
     */
    var addAnalysisNewsDetailPageViewListener = function() {
      var elements = document.querySelectorAll('[data-lux-action-analysisnewsdetailpage]');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var news = this.getAttribute('data-lux-action-analysisnewsdetailpage');

          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/analysisnewsdetailpage'], {
            news: news
          }, 'generalDetailCallback');
        });
      }
    };

    /**
     * Add listener for analysis/content (download) detail ajax view
     *
     * @returns {void}
     */
    var addAnalysisContentDetailDownloadViewListener = function() {
      var elements = document.querySelectorAll('[data-lux-action-analysiscontentdetaildownload]');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var download = this.getAttribute('data-lux-action-analysiscontentdetaildownload');

          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/analysiscontentdetaildownload'], {
            download: download
          }, 'generalDetailCallback');
        });
      }
    };

    /**
     * Add listener for analysis/linklistener detail ajax view
     *
     * @returns {void}
     */
    var addAnalysisLinkListenerDetailViewListener = function() {
      var elements = document.querySelectorAll('[data-lux-action-analysislinklistenerdetail]');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var linkListener = this.getAttribute('data-lux-action-analysislinklistenerdetail');

          ajaxConnection(TYPO3.settings.ajaxUrls['/lux/analysislinklistenerdetail'], {
            linkListener: linkListener
          }, 'generalDetailCallback');
        });
      }
    };

    /**
     * Add listener for workflow/urlshortener detail ajax view
     *
     * @returns {void}
     */
    var addWorkflowUrlShortenerDetailViewListener = function() {
      var elements = document.querySelectorAll('[data-lux-action-workflowurlshortenerdetail]');
      for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        element.addEventListener('click', function() {
          removeClassFromElements(elements, 'lux-action-detail');
          this.classList.add('lux-action-detail');
          var urlShortener = this.getAttribute('data-lux-action-workflowurlshortenerdetail');

          ajaxConnection(TYPO3.settings.ajaxUrls['/luxenterprise/workflowurlshortenerdetail'], {
            urlShortener: urlShortener
          }, 'generalDetailCallback');
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
