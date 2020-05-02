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
							'rgba(77, 231, 255, 1)',
							'rgba(221, 221, 221, 1)',
							'rgba(77, 231, 255, 0.8)',
							'rgba(77, 231, 255, 0.6)',
							'rgba(77, 231, 255, 0.4)',
							'rgba(77, 231, 255, 0.2)'
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
						label: 'Leads',
						data: element.getAttribute('data-chart-data').split(','),
						backgroundColor: [
							'rgba(77, 231, 255, 1)',
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
								'rgba(77, 231, 255, 1)',
								'rgba(77, 231, 255, 1)'
							]
						},
						{
							label: element.getAttribute('data-chart-labeltop'),
							data: element.getAttribute('data-chart-datatop').split(','),
							backgroundColor: [
								'rgba(221, 221, 221, 1)',
								'rgba(221, 221, 221, 1)'
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
			new Chart(element.getContext('2d'), {
				type: 'line',
				data: {
					datasets: [{
						label: element.getAttribute('data-chart-label'),
						data: element.getAttribute('data-chart-data').split(','),
						borderColor: 'rgb(77, 231, 255)',
						"lineTension": 0.5
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
	}


	/**
	 * Init
	 */
	$(document).ready(function () {
		var LuxDiagramObject = new LuxDiagram($);
		LuxDiagramObject.initialize();
	})
});
