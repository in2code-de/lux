define(['jquery', 'TYPO3/CMS/Lux/Vendor/Chart.min'], function($) {
  'use strict';

  /**
   * @constructor
   */
  function LuxDiagram($) {
    'use strict';

    /**
     * Initialize
     *
     * @returns {void}
     */
    this.initialize = function() {
      diagramListener();
    };

    /**
     * @returns {void}
     */
    var diagramListener = function() {
      var diagrams = document.querySelectorAll('[data-chart]');
      for (var i = 0; i < diagrams.length; i++) {
        var type = diagrams[i].getAttribute('data-chart');
        if (type === 'doughnut') {
          diagramDoughnut(diagrams[i]);
        } else if (type === 'bar') {
          diagramBar(diagrams[i]);
        } else if (type === 'bardouble') {
          diagramBarDouble(diagrams[i]);
        } else if (type === 'line') {
          diagramLine(diagrams[i]);
        }
      }
    };

    /**
     * @returns {void}
     */
    var diagramDoughnut = function(element) {
      new Chart(element.getContext('2d'), {
        type: 'doughnut',
        data: {
          datasets: [{
            data: element.getAttribute('data-chart-data').split(','),
            backgroundColor: [
              'rgba(2, 122, 202, 1)',
              'rgba(242, 182, 2, 1)',
              'rgba(209, 35, 53, 1)',
              'rgba(73, 159, 104, 1)',
              'rgba(18, 38, 58, 1)',
              'rgba(242, 182, 2, 0.6)',
              'rgba(209, 35, 53, 0.6)',
              'rgba(73, 159, 104, 0.6)',
              'rgba(242, 182, 2, 0.3)',
              'rgba(209, 35, 53, 0.3)',
              'rgba(73, 159, 104, 0.3)',
            ]
          }],
          labels: element.getAttribute('data-chart-labels').split(',')
        },
        options: {
          legend: {
            display: true,
            position: 'right',
            labels: {
              fontSize: 14
            }
          }
        }
      });
    };

    /**
     * @returns {void}
     */
    var diagramBar = function(element) {
      new Chart(element.getContext('2d'), {
        type: 'bar',
        data: {
          datasets: [{
            label: element.getAttribute('data-chart-label') || 'Leads',
            data: element.getAttribute('data-chart-data').split(','),
            backgroundColor: [
              'rgba(2, 122, 202, 1)',
              'rgba(221, 221, 221, 1)'
            ]
          }],
          labels: element.getAttribute('data-chart-labels').split(',')
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
    var diagramBarDouble = function(element) {
      new Chart(element.getContext('2d'), {
        type: 'bar',
        data: {
          datasets: [
            {
              label: element.getAttribute('data-chart-labelbottom'),
              data: element.getAttribute('data-chart-databottom').split(','),
              backgroundColor: [
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
                'rgba(2, 122, 202, 1)',
              ]
            },
            {
              label: element.getAttribute('data-chart-labeltop'),
              data: element.getAttribute('data-chart-datatop').split(','),
              backgroundColor: [
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
                'rgba(242, 182, 2, 1)',
              ]
            }
          ],
          labels: element.getAttribute('data-chart-labels').split(',')
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
            xAxes: [{
              ticks: {
                beginAtZero: true
              },
              stacked: true
            }],
            yAxes: [{
              ticks: {
                beginAtZero: true
              },
              stacked: true
            }]
          }
        }
      });
    };

    /**
     * @returns {void}
     */
    var diagramLine = function(element) {
      var datasets = [{
        label: element.getAttribute('data-chart-label'),
        data: element.getAttribute('data-chart-data').split(','),
        borderColor: 'rgba(2, 122, 202, 1)',
        "lineTension": 0.5
      }];
      for (var i = 2; i < 7; i++) {
        if (element.hasAttribute('data-chart-data' + i) && element.hasAttribute('data-chart-label' + i)) {
          datasets.push({
            label: element.getAttribute('data-chart-label' + i),
            data: element.getAttribute('data-chart-data' + i).split(','),
            borderColor: '#F2B602'
          });
        }
      }

      var yAxes = [{
        ticks: {
          beginAtZero: true
        }
      }];

      // Use a logarithmic y-axes (normally only if there is more than only one line with a big difference)
      if (element.hasAttribute('data-chart-max-y') && element.hasAttribute('data-chart-max-y') > 0) {
        yAxes = [{
          type: 'logarithmic',
          ticks: {
            min: 0,
            max: parseInt(element.getAttribute('data-chart-max-y')),
            callback: function (value, index, values) {
              // Show only this values in the y-axes
              var allowed = [1, 3, 5, 10, 20, 50, 60, 100, 500, 1000, 5000, 10000, 100000, 1000000];
              if (allowed.indexOf(value) !== -1) {
                return value;
              }
              return null;
            }
          }
        }];
      }

      new Chart(element.getContext('2d'), {
        type: 'line',
        data: {
          datasets: datasets,
          labels: element.getAttribute('data-chart-labels').split(',')
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
            yAxes: yAxes
          }
        }
      });
    };
  }


  /**
   * Init
   */
  $(document).ready(function() {
    window.LuxDiagramObject = new LuxDiagram($);
    window.LuxDiagramObject.initialize();
  })
});
