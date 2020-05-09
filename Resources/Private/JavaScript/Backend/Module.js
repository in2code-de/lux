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
      addDetailViewListener();
      addDescriptionListener();
      addLinkMockListener();
      addDatePickers();
    };

    /**
     * Add listener
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
    this.showDetailCallback = function(response) {
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
