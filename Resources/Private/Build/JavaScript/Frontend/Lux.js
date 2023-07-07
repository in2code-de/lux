import * as basicLightbox from 'basiclightbox';
import LuxIdentification from './Identification';

/**
 * LuxMain functions
 *
 * @class LuxMain
 */
function LuxMain() {
  'use strict';

  /**
   * @type {LuxMain}
   */
  var that = this;

  /**
   * That allows to call to prevent (e.g.) more then one eventlistener on email4link links
   *
   * @type {boolean}
   */
  var isInitialized = false;

  /**
   * Status if tracking is already initialized to prevent duplicated tracking initialization
   *
   * @type {boolean}
   */
  var isTrackingInitialized = false;

  /**
   * @type {null}
   */
  this.lightboxInstance = null;

  /**
   * @type {LuxIdentification}
   */
  var identification = null;

  /**
   * @type {number}
   */
  var trackIteration = 0;

  /**
   * Save form for formfieldlistening so submit is not triggered twice
   *
   * @type {null}
   */
  var formDelayStop = null;

  /**
   * @returns {void}
   */
  this.initialize = function() {
    if (isInitialized === false) {
      identification = new LuxIdentification();
      checkFunctions();

      trackingOptOutListener();
      trackingOptInListener();
      if (isLuxActivated()) {
        initializeTracking();
      } else {
        addRedirectListener();
        addAbTestingListener();
      }
      addEmail4LinkListeners();
      doNotTrackListener();
      isInitialized = true;
    }
  };

  /**
   * Close any lightbox
   *
   * @returns {void}
   */
  this.closeLightbox = function() {
    if (that.lightboxInstance !== null) {
      that.lightboxInstance.close();
    }
  };

  /**
   * OptIn (probably relevant if autoenable is disabled)
   *
   * @returns {void}
   */
  this.optIn = function() {
    identification.setTrackingOptInStatus();
    initializeTracking();
  };

  /**
   * Store if someone opts out (don't track any more)
   *
   * @returns {void}
   */
  this.optOut = function() {
    identification.setTrackingOptOutStatus();
  };

  /**
   * Reloads page after opting out to ensure that there are no more JS events binded to any elements
   *
   * @returns {void}
   */
  this.optOutAndReload = function() {
    this.optOut();
    location.reload();
  };

  /**
   * Callback and dispatcher function for all workflow actions (part of the Enterprise Edition)
   *
   * @params {Json} response
   * @returns {void}
   */
  this.generalWorkflowActionCallback = function(response) {
    for (var i = 0; i < response.length; i++) {
      if (response[i]['action']) {
        try {
          that[response[i]['action'] + 'WorkflowAction'](response[i]);
        } catch (error) {
          console.log(error);
        }
      }
    }
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * "LightboxContent" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.lightboxContentWorkflowAction = function(response) {
    var contentElementUid = response['configuration']['contentElement'];
    var uri = document.querySelector('[data-lux-contenturi]').getAttribute('data-lux-contenturi')
      || '/index.php?id=1&type=1520192598';
    if (uri.indexOf('?') === -1) {
      uri += '?luxContent=';
    } else {
      uri += '&luxContent=';
    }
    var html = '<button class="basicLightbox__close" data-lux-action-lightbox="close">close</button>';
    html += '<iframe src="' + uri + parseInt(contentElementUid) + '" width="1000" height="800"></iframe>';
    that.lightboxInstance = basicLightbox.create(html, {
      className: 'basicLightbox--iframe',
    });
    delayFunctionDispatcher(response['configuration']['delay'], 'lightboxOpen', response);
  };

  /**
   * Callback for lightboxContentWorkflowAction() (after a delay function (e.g. pageLoadDelayFunction())
   *
   * @returns {void}
   */
  this.lightboxOpenAfterDelay = function() {
    that.lightboxInstance.show();
    lightboxCloseListener();
  };

  /**
   * @returns {void}
   */
  var lightboxCloseListener = function() {
    var elements = document.querySelectorAll('[data-lux-action-lightbox="close"]');
    for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('click', function() {
        that.lightboxInstance.close();
      });
    }
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * "Redirect Workflow" and "Redirect with Shortener" (parts of the Enterprise Edition)
   *
   * @param response
   */
  this.redirectWorkflowAction = function(response) {
    delayFunctionDispatcher(response['configuration']['delay'], 'redirect', response);
  };

  /**
   * Callback for redirectWorkflowAction() (after a delay function (e.g. pageLoadDelayFunction())
   *
   * @param response
   */
  this.redirectAfterDelay = function(response) {
    if (response['configuration']['uri']) {
      window.location = response['configuration']['uri'];
    }
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * "AjaxContent" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.ajaxContentWorkflowAction = function(response) {
    var uri = document.querySelector('[data-lux-contenturiwithoutheader]')
      .getAttribute('data-lux-contenturiwithoutheader') || '/index.php?id=1&type=1560175278';
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState === 4 && this.status === 200) {
        var domSelection = document.querySelector(response['configuration']['domselection']);
        if (domSelection !== null) {
          delayFunctionDispatcher(
            response['configuration']['delay'],
            'showAjaxContent',
            {domSelection: domSelection, responseText: this.responseText}
          );
        } else {
          console.log('Element ' + response['configuration']['domselection'] + ' could not be found in HTML');
        }
      }
    };
    xhttp.open(
      'POST',
      mergeUriWithParameters(uri, {'luxContent': parseInt(response['configuration']['contentElement'])}),
      true
    );
    xhttp.send();
  };

  /**
   * Callback for ajaxContentWorkflowAction() (after a delay function (e.g. pageLoadDelayFunction())
   *
   * @param actionArguments
   * @returns {void}
   */
  this.showAjaxContentAfterDelay = function(actionArguments) {
    let domSelection = actionArguments['domSelection'];
    let responseText = actionArguments['responseText'];
    domSelection.innerHTML = responseText;
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * "Showorhide" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.showorhideWorkflowAction = function(response) {
    delayFunctionDispatcher(response['configuration']['delay'], 'showorhide', response);
  };

  /**
   * Callback for showorhideWorkflowAction() (after a delay function (e.g. pageLoadDelayFunction())
   *
   * @param response
   */
  this.showorhideAfterDelay = function(response) {
    var domSelection = document.querySelector(response['configuration']['domselection']);
    if (domSelection !== null) {
      if (response['configuration']['showorhide'] === 'hide') {
        domSelection.style.display = 'none';
      } else if (response['configuration']['showorhide'] === 'show') {
        domSelection.style.display = 'block';
      }
    } else {
      console.log('Element ' + response['configuration']['domselection'] + ' could not be found in HTML');
    }
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * "push" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.pushWorkflowAction = function(response) {
    if ('Notification' in window && response['configuration']['title'] && response['configuration']['message']) {
      if (Notification.permission === 'granted') {
        delayFunctionDispatcher(response['configuration']['delay'], 'pushMessage', response);
      } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(function (permission) {
          if (permission === 'granted') {
            delayFunctionDispatcher(response['configuration']['delay'], 'pushMessage', response);
          }
        });
      }
    }
  };

  /**
   * Callback for pushWorkflowAction() (after a delay function (e.g. pageLoadDelayFunction())
   *
   * @param response
   */
  this.pushMessageAfterDelay = function(response) {
    setTimeout(function() {
      var notification = new Notification(response['configuration']['title'], {
        icon: response['configuration']['icon'],
        body: response['configuration']['message'],
      });
      if (response['configuration']['uri']) {
        notification.onclick = function() {
          window.open(response['configuration']['uri']);
        };
      }
    }, parseInt(response['configuration']['delay']));
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * "title" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.titleWorkflowAction = function(response) {
    delayFunctionDispatcher(response['configuration']['delay'], 'title', response);
  };

  /**
   * Callback for titleWorkflowAction() (after a delay function (e.g. pageLoadDelayFunction())
   *
   * @param response
   */
  this.titleAfterDelay = function(response) {
    document.title = response['configuration']['title'];
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * Not a real workflowAction but more a finisher action to stop asking for email addresses on email4link clicks if
   * the visitor is already known (only with a cookie "luxDisableEmail4Link"
   *
   * @param response
   */
  this.disableEmail4LinkWorkflowAction = function(response) {
    identification.setDisableForLinkStorageEntry();
  };

  /**
   * Callback for generalWorkflowActionCallback()
   * A/B Testing page visit AJAX response. Take the tx_luxenterprise_domain_model_abpagevisit.uid value and save it
   * into a data-attribute, so it can be used for conversion management later
   *
   * @param response
   */
  this.abPageVisitWorkflowAction = function(response) {
    var abpagevisitIdentifier = response['configuration']['record'];
    if (abpagevisitIdentifier !== null) {
      var element = document.querySelector('[data-lux-abpagevisit]');
      element.setAttribute('data-lux-abpagevisit', abpagevisitIdentifier);

      abPageConversionListener(
        abpagevisitIdentifier,
        element.getAttribute('data-lux-abpageconversion'),
        element.getAttribute('data-lux-abpageconversionconfiguration')
      )
    }
  };

  /**
   * Try to send a request if A/B testing conversion is fulfilled
   *
   * @param {Number} abpagevisitIdentifier like "123"
   * @param {String} method 0=link, 1=form, 2=htmlSelection
   * @param {String} configuration like "[data-foo]"
   */
  var abPageConversionListener = function(abpagevisitIdentifier, method, configuration) {
    var selection = 'a';
    if (method === '1') {
      selection = 'form'; // todo: to implement
    } else if (method === '2') {
      selection = unescapeHtml(configuration);
    }

    var elements = document.querySelectorAll(selection);
    for (var i = 0; i < elements.length; i++) {
      if (elements[i].localName === 'form' || elements[i].form !== undefined) {
        var form = (elements[i].form !== undefined ? elements[i].form : elements[i]);
        form.addEventListener('submit', function(event) {
          delaySubmit(event, 'abTestConversion', true);

          ajaxConnection({
            'tx_lux_fe[dispatchAction]': 'abTestingConversionFulfilledRequest',
            'tx_lux_fe[identificator]': identification.isIdentificatorSet() ? identification.getIdentificator() : '',
            'tx_lux_fe[arguments][abPageVisitIdentifier]': abpagevisitIdentifier,
          }, getRequestUri(), null, null);
        });
      } else {
        elements[i].addEventListener('click', function(event) {
          delayClick(event, 'abTestConversion');

          ajaxConnection({
            'tx_lux_fe[dispatchAction]': 'abTestingConversionFulfilledRequest',
            'tx_lux_fe[identificator]': identification.isIdentificatorSet() ? identification.getIdentificator() : '',
            'tx_lux_fe[arguments][abPageVisitIdentifier]': abpagevisitIdentifier,
          }, getRequestUri(), null, null);
        });
      }
    }
  };

  /**
   * Decide what kind of delay function should call the final workflow action
   *
   * Possible delays are (functionname + "DelayFunction"):
   *  - "pageLoad"
   *  - "scrollToBottom"
   *  - "mouseLeave"
   *  - "inactiveTab"
   *
   * Possible actions (called after delay) are (functionname + "AfterDelay"):
   *  - "lightboxOpen"
   *  - "redirect"
   *  - "showAjaxContent"
   *  - "showorhide"
   *  - "pushMessage"
   *
   * @param {Array} delay Configuration of the delay
   * @param {String} action Name of the workflow action that should be executed when the delay is over
   * @param actionArguments Arguments to pass to action
   */
  var delayFunctionDispatcher = function(delay, action, actionArguments) {
    try {
      that[(delay['function'] || 'pageLoad') + 'DelayFunction'](delay['options'], action, actionArguments);
    } catch (error) {
      console.log(error);
    }
  };

  /**
   * Callback for delayFunctionDispatcher()
   * to wait the given delay time and then execute
   *
   * @param {Array} option
   * @param {String} action
   * @param actionArguments Arguments to pass to action
   */
  this.pageLoadDelayFunction = function(option, action, actionArguments) {
    setTimeout(function() {
      try {
        that[action + 'AfterDelay'](actionArguments);
      } catch (error) {
        console.log(error);
      }
    }, parseInt(option['time']));
  };

  /**
   * Callback for delayFunctionDispatcher()
   * to wait until the visitor scrolled to the 90% down of page
   *
   * @param {Array} option
   * @param {String} action
   * @param actionArguments Arguments to pass to action
   */
  this.scrollToBottomDelayFunction = function(option, action, actionArguments) {
    let eventFired = false; // ensure that the event is only fired the first time when scrolling to the bottom
    window.onscroll = function() {
      const pageHeight = document.body.offsetHeight / 100 * 90; // 90 % of the page height
      if (eventFired === false && (window.innerHeight + window.pageYOffset) >= pageHeight) {
        eventFired = true;
        setTimeout(function() {
          try {
            that[action + 'AfterDelay'](actionArguments);
          } catch (error) {
            console.log(error);
          }
        }, parseInt(option['time']));
      }
    };
  };

  /**
   * Callback for delayFunctionDispatcher()
   * to fire of the mouse pointer leaves the browser window
   *
   * @param {Array} option
   * @param {String} action
   * @param actionArguments Arguments to pass to action
   */
  this.mouseLeaveDelayFunction = function(option, action, actionArguments) {
    let eventFired = false; // ensure that the event is only fired the first time when mouse leaves browser tab
    window.addEventListener('mouseout', function(event) {
      if (eventFired === false) {
        if (event.pageY < 0 || event.pageY > window.innerHeight || event.pageX < 0 || event.pageX > window.innerWidth) {
          eventFired = true;
          setTimeout(function() {
            try {
              that[action + 'AfterDelay'](actionArguments);
            } catch (error) {
              console.log(error);
            }
          }, parseInt(option['time']));
        }
      }
    }, false);
  };

  /**
   * Callback for delayFunctionDispatcher()
   * to wait until the visitor switches to another tab (so the current tab is inactive)
   *
   * @param {Array} option
   * @param {String} action
   * @param actionArguments Arguments to pass to action
   */
  this.inactiveTabDelayFunction = function(option, action, actionArguments) {
    let eventFired = false; // ensure that the event is only fired the first time when mouse leaves browser tab
    let hidden;
    let visibilityChange;
    if (typeof document.hidden !== 'undefined') { // Opera 12.10 and Firefox 18 and later support
      hidden = 'hidden';
      visibilityChange = 'visibilitychange';
    } else if (typeof document.msHidden !== 'undefined') {
      hidden = "msHidden";
      visibilityChange = 'msvisibilitychange';
    } else if (typeof document.webkitHidden !== 'undefined') {
      hidden = 'webkitHidden';
      visibilityChange = 'webkitvisibilitychange';
    }

    if (typeof document.addEventListener === 'undefined' || hidden === undefined) {
      console.log('This function requires a modern browser such as Google Chrome or Firefox withPage Visibility API');
    } else {
      document.addEventListener(visibilityChange, function() {
        if (eventFired === false) {
          if (document[hidden]) {
            setTimeout(function() {
              try {
                eventFired = true;
                that[action + 'AfterDelay'](actionArguments);
              } catch (error) {
                console.log(error);
              }
            }, parseInt(option['time']));
          } else {
            // is active (again)
          }
        }
      }, false);
    }
  };

  /**
   * Get LuxIdentification object in luxenterprise
   *
   * @returns {LuxIdentification}
   */
  this.getIdentification = function() {
    return identification;
  };

  /**
   * @returns {void}
   */
  var initializeTracking = function() {
    if (isTrackingInitialized === false) {
      identification.setIdentificator(getIdentificationType());
      track();
      isTrackingInitialized = true;
    }
  };

  /**
   * Try to send async tracking request as soon as the fingerprint is calculated (try max. 20s)
   *
   * @returns {void}
   */
  var track = function() {
    if (identification.isIdentificatorSet()) {
      pageRequest();
      addFieldListeners();
      addFormListeners();
      addDownloadListener();
      addLinkListenerListener();
      addRedirectListener();
      addAbTestingListener();
    } else {
      trackIteration++;
      if (trackIteration < 200) {
        setTimeout(track, 100);
      } else {
        console.log('Fingerprint could not be calculated within 20s');
      }
    }
  };

  /**
   * If someone clicks don't want to be tracked any more, use a checkbox with data-lux-trackingoptout="checkbox"
   *
   * @returns {void}
   */
  var trackingOptOutListener = function() {
    var elements = document.querySelectorAll('[data-lux-trackingoptout="checkbox"]');
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      // check/uncheck checkbox with data-lux-trackingoptout="checkbox". Check if tracking is allowed.
      element.checked = identification.isOptOutStatusSet() === false;
      element.addEventListener('change', function() {
        if (identification.isOptOutStatusSet()) {
          console.log('Lux: Disable Opt Out');
          that.optIn();
        } else {
          console.log('Lux: Opt Out');
          that.optOut();
        }
      });
    }
  };

  /**
   * If autoenable is turned off, tracking can be toggled by clicking an element with
   * data-lux-trackingoptin="true" or ="false"
   *
   * @returns {void}
   */
  var trackingOptInListener = function() {
    var elements = document.querySelectorAll('[data-lux-trackingoptin]');
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      element.addEventListener('click', function(element) {
        var status = element.target.getAttribute('data-lux-trackingoptin') === 'true';
        if (status === true) {
          console.log('Lux: Opt In selected');
          that.optIn();
        } else {
          console.log('Lux: Opt Out selected');
          that.optOut();
        }
      });
    }
  };

  /**
   * @returns {void}
   */
  var pageRequest = function() {
    if (isPageTrackingEnabled()) {
      var parameters = {
        'tx_lux_fe[dispatchAction]': 'pageRequest',
        'tx_lux_fe[identificator]': identification.getIdentificator(),
        'tx_lux_fe[arguments][pageUid]': getPageUid(),
        'tx_lux_fe[arguments][languageUid]': getLanguageUid(),
        'tx_lux_fe[arguments][referrer]': getReferrer(),
        'tx_lux_fe[arguments][currentUrl]': encodeURIComponent(window.location.href),
      };
      if (getNewsUid() > 0) {
        parameters['tx_lux_fe[arguments][newsUid]'] = getNewsUid();
      }
      ajaxConnection(parameters, getRequestUri(), 'generalWorkflowActionCallback', null);
    }
  };

  /**
   * @returns {void}
   */
  var addFieldListeners = function() {
    var query = 'form:not([data-lux-form-identification]) input:not([data-lux-disable]):not([type="hidden"]):not([type="submit"]), ';
    query += 'form:not([data-lux-form-identification]) textarea:not([data-lux-disable]), ';
    query += 'form:not([data-lux-form-identification]) select:not([data-lux-disable]), ';
    query += 'form:not([data-lux-form-identification]) radio:not([data-lux-disable]), ';
    query += 'form:not([data-lux-form-identification]) check:not([data-lux-disable])';
    var elements = document.querySelectorAll(query);
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      // Skip every password field and check if this field is configured for listening in TypoScript
      if (element.type !== 'password' && isFieldConfiguredInFieldMapping(element)) {
        element.addEventListener('change', function() {
          fieldListener(this);
        });
      }
    }
  };

  /**
   * @returns {void}
   */
  var addFormListeners = function() {
    var forms = document.querySelectorAll('form[data-lux-form-identification]');
    forms.forEach(function(form) {
      form.addEventListener('submit', function(event) {
        sendFormValues(event.target);
        delaySubmit(
          event,
          'formListening',
          event.target.getAttribute('data-lux-form-identification') !== 'preventDefault'
        );
      });
    });
  };

  /**
   * @returns {void}
   */
  var addEmail4LinkListeners = function() {
    var links = document.querySelectorAll('[data-lux-email4link-title]');
    for (var i = 0; i < links.length; i++) {
      var element = links[i];
      element.setAttribute('data-lux-href', element.getAttribute('href'));
      element.setAttribute('href', '#');
      element.addEventListener('click', function(event) {
        email4LinkListener(this, event);
      });
    }
  };

  /**
   * @returns {void}
   */
  var addDownloadListener = function() {
    if (isDownloadTrackingEnabled()) {
      var links = document.querySelectorAll(getExpressionForLinkSelection());
      var href;
      for (var i = 0; i < links.length; i++) {
        if (!links[i].hasAttribute('data-lux-email4link-title')) {
          href = links[i].getAttribute('href');
          links[i].addEventListener('click', function(event) {
            ajaxConnection({
              'tx_lux_fe[dispatchAction]': 'downloadRequest',
              'tx_lux_fe[identificator]': identification.getIdentificator(),
              'tx_lux_fe[arguments][href]': this.getAttribute('href'),
              'tx_lux_fe[arguments][pageUid]': getPageUid()
            }, getRequestUri(), null, null);
            delayClick(event, 'DownlaodListener');
          });
        }
      }
    }
  };

  /**
   * @returns {void}
   */
  var addLinkListenerListener = function() {
    var links = document.querySelectorAll('[data-lux-linklistener]');
    for (var i = 0; i < links.length; i++) {
      links[i].addEventListener('click', function(event) {
        var target = (event.currentTarget) ? event.currentTarget : event.target;
        ajaxConnection({
          'tx_lux_fe[dispatchAction]': 'linkClickRequest',
          'tx_lux_fe[identificator]': identification.getIdentificator(),
          'tx_lux_fe[arguments][linklistenerIdentifier]': target.getAttribute('data-lux-linklistener'),
          'tx_lux_fe[arguments][pageUid]': getPageUid()
        }, getRequestUri(), null, null);
        delayClick(event, 'LinkListener');
      });
    }
  };

  /**
   * Can be called with opt-in (if no fingerprint, send empty value) and opt-out
   *
   * @returns {void}
   */
  var addRedirectListener = function() {
    var redirectContainer = document.querySelector('[data-lux-redirect]');
    if (redirectContainer !== null) {
      var hash = redirectContainer.getAttribute('data-lux-redirect');
      ajaxConnection({
        'tx_lux_fe[dispatchAction]': 'redirectRequest',
        'tx_lux_fe[identificator]': identification.isIdentificatorSet() ? identification.getIdentificator() : '',
        'tx_lux_fe[arguments][redirectHash]': hash
      }, getRequestUri(), 'generalWorkflowActionCallback', null);
    }
  };

  /**
   * @returns {void}
   */
  var addAbTestingListener = function() {
    var abTestingContainer = document.querySelector('[data-lux-abtestingpage]');
    if (abTestingContainer !== null) {
      setTimeout(
        function() {
          ajaxConnection({
            'tx_lux_fe[dispatchAction]': 'abTestingRequest',
            'tx_lux_fe[identificator]': identification.isIdentificatorSet() ? identification.getIdentificator() : '',
            'tx_lux_fe[arguments][abTestingPage]': abTestingContainer.getAttribute('data-lux-abtestingpage'),
          }, getRequestUri(), 'generalWorkflowActionCallback', null);
        }, 1000
      );
    }
  };

  /**
   * Delay a click to a link to give AJAX some time to do a bit magic
   *
   * @returns {void}
   */
  var delayClick = function(event, status) {
    var target = (event.currentTarget) ? event.currentTarget : event.target;
    var href = target.hasAttribute('data-lux-href') ? target.getAttribute('data-lux-href') : target.getAttribute('href');
    var hrefTarget = target.getAttribute('target');
    var delay = 400;
    if (isDebugMode()) {
      console.log(status + ' triggered. Redirect in some seconds to ' + href);
      delay = 5000;
    }
    if (href !== null) {
      event.preventDefault();
      setTimeout(
        function() {
          var newWindow = null;

          if (hrefTarget !== '' && hrefTarget !== null) {
            newWindow = window.open(href, hrefTarget);
          }

          if (newWindow === null) {
            window.location = href;
          }
        }, delay
      );
    }
  };

  /**
   * Delay a form submit to give AJAX some time for tracking requests
   *    data-lux-form-identification="true" does a form.submit() while
   *    data-lux-form-identification="submitButton" does a lastSubmitButton.click()
   *
   * @param event triggered form element
   * @param status debugging name
   * @param {boolean} submit Form must be submitted?
   * @returns {void}
   */
  var delaySubmit = function(event, status, submit) {
    var form = event.target;
    if (formDelayStop !== form) {
      formDelayStop = form;
      event.preventDefault();
      var delay = 500;
      var sendBySubmitButton = form.getAttribute('data-lux-form-identification') === 'submitButton';

      if (isDebugMode()) {
        console.log(status + ' triggered. Form submit delayed');
        delay = 5000;
      }

      if (submit === true) {
        setTimeout(
          function() {
            form.removeAttribute('data-lux-form-identification');

            // Submit by clicking submit button
            if (sendBySubmitButton === true) {
              var submitButtons = form.querySelectorAll('[type="submit"], button:not([type="button"])');
              var submitButton = submitButtons[submitButtons.length - 1]; // take last button in form if there is a previous button
              submitButton.click();
            } else {
              // Default form submit
              form.submit();
            }
          }, delay
        );
      }
    }
  };

  /**
   * @param {Node} link
   * @param event
   * @returns {void}
   */
  var email4LinkListener = function(link, event) {
    if (identification.isDisableForLinkStorageEntrySet()) {
      // track as normal asset download
      ajaxConnection({
        'tx_lux_fe[dispatchAction]': 'downloadRequest',
        'tx_lux_fe[identificator]': identification.getIdentificator(),
        'tx_lux_fe[arguments][href]': link.getAttribute('data-lux-href'),
        'tx_lux_fe[arguments][pageUid]': getPageUid()
      }, getRequestUri(), null, null);
      delayClick(event, 'Email4Link');
    } else {
      // show popup
      event.preventDefault();

      if (document.body.classList.contains('lux_waiting') === false) {
        addWaitClassToBodyTag();

        var parameters = {
          'tx_lux_email4link[title]': link.getAttribute('data-lux-email4link-title') || '',
          'tx_lux_email4link[text]': link.getAttribute('data-lux-email4link-text') || '',
          'tx_lux_email4link[href]': getFilenameFromHref(link.getAttribute('data-lux-href'))
        };

        // add any parameters to request from data-lux-email4link-arguments-key="value"
        for (var key in link.dataset) {
          if (key.indexOf('luxEmail4linkArguments') === 0) {
            parameters['tx_lux_email4link[arguments][' + key.substring('luxEmail4linkArguments'.length).toLowerCase() + ']'] = link.dataset[key];
          }
        }
        ajaxConnection(
          parameters,
          getContainer().getAttribute('data-lux-email4linktemplate'),
          'email4linkTemplateCallback',
          {link: link}
        );
      }
    }
  };

  /**
   * Callback for email4LinkListener to get html template for email4link lightbox
   *
   * @param response
   * @param callbackArguments
   * @returns {void}
   */
  this.email4linkTemplateCallback = function(response, callbackArguments) {
    that.lightboxInstance = basicLightbox.create(response.html);
    that.lightboxInstance.element().querySelector('[data-lux-email4link="form"]').addEventListener('submit', function(event) {
      email4LinkLightboxSubmitListener(this, event, callbackArguments.link);
    });
    that.lightboxInstance.show();
    removeWaitClassToBodyTag();
  }

  /**
   * Callback function if lightbox should be submitted
   *
   * @param {Node} element
   * @param event
   * @param {Node} link
   * @returns {void}
   */
  var email4LinkLightboxSubmitListener = function(element, event, link) {
    event.preventDefault();
    var href = link.getAttribute('data-lux-href');
    var target = link.getAttribute('target');
    var sendEmail = link.getAttribute('data-lux-email4link-sendemail') || 'false';

    var formArguments = {};
    var form = that.lightboxInstance.element().querySelector('[data-lux-email4link="form"]');
    for (var i = 0; i < form.elements.length; i++) {
      var field = form.elements[i];
      var name = field.getAttribute('name');
      if (name !== null && name.indexOf('email4link[') !== -1) {
        var value = field.value;
        var nameParts = name.split('[');
        name = nameParts[1].substring(0, nameParts[1].length - 1);
        formArguments[name] = value;
      }
    }

    if (isEmailAddress(formArguments['email'])) {
      addWaitClassToBodyTag();
      ajaxConnection({
        'tx_lux_fe[dispatchAction]': 'email4LinkRequest',
        'tx_lux_fe[identificator]': identification.getIdentificator(),
        'tx_lux_fe[arguments][sendEmail]': sendEmail === 'true',
        'tx_lux_fe[arguments][href]': href,
        'tx_lux_fe[arguments][pageUid]': getPageUid(),
        'tx_lux_fe[arguments][values]': encodeURIComponent(JSON.stringify(formArguments))
      }, getRequestUri(), 'email4LinkLightboxSubmitCallback', {sendEmail: (sendEmail === 'true'), href: href, target: target});
    } else {
      showElement(that.lightboxInstance.element().querySelector('[data-lux-email4link="errorEmailAddress"]'));
    }
  };

  /**
   * Callback for email4LinkLightboxSubmitListener
   *
   * @param response
   * @param callbackArguments
   * @returns {void}
   */
  this.email4LinkLightboxSubmitCallback = function(response, callbackArguments) {
    removeWaitClassToBodyTag();

    if (response.error === true) {
      showElement(that.lightboxInstance.element().querySelector('[data-lux-email4link="errorEmailAddress"]'));
    } else {
      if (callbackArguments.sendEmail === true) {
        hideElement(that.lightboxInstance.element().querySelector('[data-lux-email4link="form"]'));
        showElement(
          that.lightboxInstance.element().querySelector('[data-lux-email4link="successMessageSendEmail"]')
        );
        setTimeout(function() {
          that.lightboxInstance.close();
        }, 2000);
      } else {
        setTimeout(function() {
          var newWindow = null;

          that.lightboxInstance.close();
          if (callbackArguments.target !== '' && callbackArguments.target !== null) {
            newWindow = window.open(callbackArguments.href, callbackArguments.target);
          }

          if (newWindow === null) {
            window.location = callbackArguments.href;
          }
        }, 500);
      }
    }
  };

  /**
   * @param {Node} field
   * @returns {void}
   */
  var fieldListener = function(field) {
    var key = getKeyOfFieldConfigurationToGivenField(field, getFieldMapping());
    var value = field.value;
    ajaxConnection({
      'tx_lux_fe[dispatchAction]': 'fieldListeningRequest',
      'tx_lux_fe[identificator]': identification.getIdentificator(),
      'tx_lux_fe[arguments][key]': key,
      'tx_lux_fe[arguments][value]': value,
      'tx_lux_fe[arguments][pageUid]': getPageUid()
    }, getRequestUri(), 'generalWorkflowActionCallback', null);
  };

  /**
   * FormListener submit function
   *
   * @param {Node} form
   * @returns {void}
   */
  var sendFormValues = function(form) {
    var formArguments = {};
    for (var i = 0; i < form.elements.length; i++) {
      var field = form.elements[i];
      var key = getKeyOfFieldConfigurationToGivenField(field, getFormFieldMapping());
      if (key !== '') {
        formArguments[key] = field.value;
      }
    }

    ajaxConnection({
      'tx_lux_fe[dispatchAction]': 'formListeningRequest',
      'tx_lux_fe[identificator]': identification.getIdentificator(),
      'tx_lux_fe[arguments][values]': encodeURIComponent(JSON.stringify(formArguments)),
      'tx_lux_fe[arguments][pageUid]': getPageUid()
    }, getRequestUri(), 'generalWorkflowActionCallback', null);
  };

  /**
   * @returns {void}
   */
  var doNotTrackListener = function() {
    if (navigator.doNotTrack === '1') {
      var text = document.querySelectorAll('[data-lux-container-optout="text"]');
      for (var i = 0; i < text.length; i++) {
        hideElement(text[i]);
      }
      var textDoNotTrack = document.querySelectorAll('[data-lux-container-optout="textDoNotTrack"]');
      for (var j = 0; j < textDoNotTrack.length; j++) {
        showElement(textDoNotTrack[j]);
      }
    }
  };

  /**
   * Check if lux could work
   *
   * @returns {void}
   */
  var checkFunctions = function() {
    if (isDebugMode()) {
      console.log('Lux: Debug is activated');
      console.log('Lux: runs in mode: ' + getIdentificationType() + ' (0=fingerprint, 2=localstorage)');
      if (isLuxActivated() === false) {
        console.log('Lux: Tracking is deactivated');
      }
      if (navigator.doNotTrack === '1') {
        console.log('Do-Not-Track header set in your browser, so tracking is deactivated');
      }
      if (getContainer() === null || getContainer().getAttribute('data-lux-enable') !== '1') {
        console.log(
          'No tag with data-lux-enable="1" given, so tracking is deactivated (probably logged in into TYPO3 backend?)'
        );
      }
    }
  };

  /**
   * @param field
   * @returns {boolean}
   */
  var isFieldConfiguredInFieldMapping = function(field) {
    return getKeyOfFieldConfigurationToGivenField(field, getFieldMapping()) !== '';
  };

  /**
   * Pass a field element and check if this field is configured in TypoScript in field mapping. If found get key of
   * the configuration. Oherwise return an empty string.
   *
   * @param field
   * @param fieldMapping
   * @returns {string}
   */
  var getKeyOfFieldConfigurationToGivenField = function(field, fieldMapping) {
    var keyConfiguration = '';
    var fieldName = field.name;
    for (var key in fieldMapping) {
      // iterate through fieldtypes
      if (fieldMapping.hasOwnProperty(key)) {
        // iterate through every fieldtype definition
        for (var i = 0; i < fieldMapping[key].length; i++) {
          if (matchStringInString(fieldName, fieldMapping[key][i])) {
            keyConfiguration = key;
          }
        }
      }
    }
    return keyConfiguration;
  };

  /**
   * Return an expression for a querySelectorAll function to select all download links
   * Like 'a[href$="jpg"],a[href$="pdf"]'
   *
   * @returns {String}
   */
  var getExpressionForLinkSelection = function() {
    var extensions = getContainer().getAttribute('data-lux-downloadtracking-extensions').toLowerCase().split(',');
    return 'a[href$="' + extensions.join('"],a[href$="') + '"]';
  };

  /**
   * Check if string is identically to another string. But if there is a "*", check if the string is part of another
   * string
   *
   * @param haystack
   * @param needle
   * @returns {boolean}
   */
  var matchStringInString = function(haystack, needle) {
    if (needle.indexOf('*') !== -1) {
      needle = needle.replace('*', '');
      var found = haystack.indexOf(needle) !== -1;
    } else {
      found = haystack === needle;
    }
    return found;
  };

  /**
   * @returns {object}
   */
  var getFieldMapping = function() {
    var json = {};
    try {
      json = JSON.parse(window.luxFieldMappingConfiguration);
    } catch (err) {
      console.log('Lux: No fieldmapping configuration given.');
    }
    return json;
  };

  /**
   * @returns {object}
   */
  var getFormFieldMapping = function() {
    var json = {};
    try {
      json = JSON.parse(window.luxFormFieldMappingConfiguration);
    } catch (err) {
      console.log('Lux: No formfieldmapping configuration given.');
    }
    return json;
  };

  /**
   * @returns {boolean}
   */
  var isPageTrackingEnabled = function() {
    var enabled = false;
    var container = getContainer();
    if (container !== null) {
      if (container.hasAttribute('data-lux-pagetracking')) {
        var pageTrackingEnabled = container.getAttribute('data-lux-pagetracking');
        enabled = pageTrackingEnabled === '1';
      }
    }
    return enabled;
  };

  /**
   * @returns {boolean}
   */
  var isDownloadTrackingEnabled = function() {
    var enabled = false;
    var container = getContainer();
    if (container !== null) {
      if (container.hasAttribute('data-lux-downloadtracking')) {
        var trackingEnabled = container.getAttribute('data-lux-downloadtracking');
        enabled = trackingEnabled === '1';
      }
    }
    return enabled;
  };

  /**
   * @returns {int}
   */
  var getPageUid = function() {
    var uid = 0;
    var container = getContainer();
    if (container !== null) {
      if (container.hasAttribute('data-lux-pageuid')) {
        var uidContainer = container.getAttribute('data-lux-pageuid');
        uid = parseInt(uidContainer);
      }
    }
    return uid;
  };

  /**
   * @returns {int}
   */
  var getLanguageUid = function() {
    var uid = 0;
    var container = getContainer();
    if (container !== null) {
      if (container.hasAttribute('data-lux-languageuid')) {
        var uidContainer = container.getAttribute('data-lux-languageuid');
        uid = parseInt(uidContainer);
      }
    }
    return uid;
  };

  /**
   * @returns {int}
   */
  var getNewsUid = function() {
    var uid = 0;
    var container = getContainer();
    if (container !== null) {
      if (container.hasAttribute('data-lux-newsuid')) {
        var uidContainer = container.getAttribute('data-lux-newsuid');
        uid = parseInt(uidContainer);
      }
    }
    return uid;
  };

  /**
   * @returns {string}
   */
  var getReferrer = function() {
    return encodeURIComponent(document.referrer);
  };

  /**
   * @returns {string}
   */
  var getRequestUri = function() {
    var container = getContainer();
    if (container !== null) {
      return container.getAttribute('data-lux-requesturi');
    }
    return '';
  };

  /**
   * @params {object} parameters
   * @params {string} uri
   * @params {string} callback
   * @params {object} callbackArguments
   * @returns {void}
   */
  var ajaxConnection = function(parameters, uri, callback, callbackArguments) {
    callbackArguments = callbackArguments || {};
    if (uri !== '') {
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
        uri += key + '=' + parameters[key];
      }
    }
    return uri;
  };

  /**
   * Check if tracking is possible - when
   * - doNotTrack header ist not set
   * - optOutStatus is not set (visitor did not opt out)
   * - container with important serverside information is available in DOM
   * - data-lux-enable="1" (lux is enabled in general)
   *
   * @returns {boolean}
   */
  var isLuxActivated = function() {
    return navigator.doNotTrack !== '1'
      && identification.isOptOutStatusSet() === false
      && getContainer() !== null
      && getContainer().getAttribute('data-lux-enable') === '1'
      && isLuxAutoEnabled();
  };

  /**
   * - If lux autoenable is turned on
   * - OR if autoenable is off but optInStatus is set (visitor did an opt in)
   *
   * @returns {boolean}
   */
  var isLuxAutoEnabled = function() {
    var autoEnable = getContainer().getAttribute('data-lux-autoenable') === '1';
    return autoEnable === true || (autoEnable === false && identification.isOptInStatusSet());
  };

  /**
   * @returns {object}
   */
  var getContainer = function() {
    return document.getElementById('lux_container');
  };

  /**
   * Get identification type (0=fingerprint, 2=localstorage)
   *
   * @returns {number}
   */
  var getIdentificationType = function() {
    var type = getContainer().getAttribute('data-lux-identificationMethod') || 0;
    return parseInt(type);
  };

  /**
   * @returns {void}
   */
  var addWaitClassToBodyTag = function() {
    document.body.classList.add('lux_waiting');
  };

  /**
   * @returns {void}
   */
  var removeWaitClassToBodyTag = function() {
    document.body.classList.remove('lux_waiting');
  };

  /**
   * @param {Node} element
   * @returns {void}
   */
  var hideElement = function(element) {
    element.style.display = 'none';
  };

  /**
   * @param {Node} element
   * @returns {void}
   */
  var showElement = function(element) {
    element.style.display = 'block';
  };

  /**
   * Check if string is an email
   *
   * @param email
   * @returns {boolean}
   */
  var isEmailAddress = function(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
  };

  /**
   * Just show the filename instead of the complete path - but only for asset downloads and not for links to pages
   * or folders
   *
   * @param {String} href
   * @returns {String}
   */
  var getFilenameFromHref = function(href) {
    var filename = href.replace(/^.*[\\\/]/, '');
    var fileExtensions = [
      'pdf',
      'txt',
      'doc',
      'docx',
      'xls',
      'xlsx',
      'ppt',
      'pptx',
      'jpg',
      'png',
      'zip'
    ];
    if (inArray(getFileExtension(filename).toLowerCase(), fileExtensions)) {
      href = filename;
    }
    return href;
  };

  /**
   * @param {String} needle
   * @param {Array} haystack
   * @returns {boolean}
   */
  var inArray = function(needle, haystack) {
    var length = haystack.length;
    for (var i = 0; i < length; i++) {
      if (haystack[i] === needle) return true;
    }
    return false;
  };

  /**
   * @param {String} filename
   * @returns {String}
   */
  var getFileExtension = function(filename) {
    if (filename.indexOf('.') !== -1) {
      return filename.split('.').pop();
    }
    return '';
  };

  /**
   * htmlspecialchars_decode() variant for JavaScript
   *
   * @param {string} html
   * @returns {string}
   */
  var unescapeHtml = function(html) {
    var temp = document.createElement("div");
    temp.innerHTML = html;
    var result = temp.childNodes[0].nodeValue;
    temp.removeChild(temp.firstChild);
    return result;
  }

  /**
   * Is debug mode activated?
   * - Check if a cookie with name "ENABLELUXDEBUG" is given
   * - Search for text "ENABLELUXDEBUG" anywhere on the website
   *
   * @returns {boolean}
   */
  var isDebugMode = function () {
    return identification.isDebugCookieSet() || document.body.innerHTML.search('ENABLELUXDEBUG') !== -1;
  }
}

/**
 * Get a singleton object in lux and luxenterprise
 *
 * @type {{getInstance: (function(): LuxIdentification)}}
 */
window.LuxSingleton = (function() {
  var instance;

  function createInstance() {
    return new LuxMain();
  }

  return {
    getInstance: function() {
      if (!instance) {
        instance = createInstance();
      }
      return instance;
    }
  };
})();

var Lux = window.LuxSingleton.getInstance();
Lux.initialize();
