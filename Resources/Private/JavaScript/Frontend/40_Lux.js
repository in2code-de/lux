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
   * Status if tracking is already initialized. That allows to call initialize() or optIn() more then once without
   * problems.
   *
   * @type {boolean}
   */
  var initialized = false;

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
   * @returns {void}
   */
  this.initialize = function() {
    if (initialized === false) {
      identification = new LuxIdentification();
      checkFunctions();

      trackingOptOutListener();
      trackingOptInListener();
      if (isLuxActivated()) {
        initializeTracking();
      } else {
        addRedirectListener();
      }
      addEmail4LinkListeners();
      doNotTrackListener();
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
   * Store if someone opts out (don't track any more)
   *
   * @returns {void}
   */
  this.optOut = function() {
    identification.setTrackingOptOutStatus();
  };

  /**
   * Remove opt out status (someone can be tracked again)
   *
   * @returns {void}
   */
  this.optOutDisabled = function() {
    identification.removeTrackingOptOutStatus();
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
   * OptIn remove (probably relevant if autoenable is disabled)
   *
   * @returns {void}
   */
  this.optInDisabled = function() {
    identification.removeTrackingOptInStatus();
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
   * Callback for workflow action "LightboxContent" (part of the Enterprise Edition)
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
    var html =
      '<div><iframe src="' + uri + parseInt(contentElementUid) + '" width="800" height="600">' +
      '</iframe></div>';
    that.lightboxInstance = basicLightbox.create(html);
    setTimeout(function() {
      that.lightboxInstance.show();
    }, parseInt(response['configuration']['delay']));
  };

  /**
   * Callback for workflow action "Redirect" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.redirectWorkflowAction = function(response) {
    if (response['configuration']['uri']) {
      window.location = response['configuration']['uri'];
    }
  };

  /**
   * Callback for workflow action "AjaxContent" (part of the Enterprise Edition)
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
          domSelection.innerHTML = this.responseText;
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
   * Callback for workflow action "Showorhide" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.showorhideWorkflowAction = function(response) {
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
   * Callback for workflow action "push" (part of the Enterprise Edition)
   *
   * @param response
   */
  this.pushWorkflowAction = function(response) {
    if ('Notification' in window && response['configuration']['title'] && response['configuration']['message']) {
      if (Notification.permission === "granted") {
        pushMessage(response);
      } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(function (permission) {
          if (permission === "granted") {
            pushMessage(response);
          }
        });
      }
    }
  };

  /**
   * @param response
   */
  var pushMessage = function(response) {
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
  }

  /**
   * Not a real workflowAction but more a finisher action to stop asking for email addresses on email4link clicks if
   * the visitor is already known (only with a cookie "luxDisableEmail4Link"
   *
   * @param response
   */
  this.disableEmail4LinkWorkflowAction = function(response) {
    identification.setDisableForLinkStorageEntry();
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
    if (initialized === false) {
      identification.setIdentificator(getIdentificationType());
      track();
      initialized = true;
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
          that.optOutDisabled();
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
          that.optInDisabled();
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
    for (var i = 0; i < forms.length; i++) {
      forms[i].addEventListener('submit', function(event) {
        if (event.target.getAttribute('data-lux-form-identification') === 'preventDefault') {
          event.preventDefault();
        }
        sendFormValues(event.target);
      });
    }
  };

  /**
   * @returns {void}
   */
  var addEmail4LinkListeners = function() {
    var links = document.querySelectorAll('[data-lux-email4link-title]');
    for (var i = 0; i < links.length; i++) {
      var element = links[i];
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
              'tx_lux_fe[arguments][href]': this.getAttribute('href')
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
        var linklistenerIdentifier = event.target.getAttribute('data-lux-linklistener');
        if (linklistenerIdentifier !== '') {
          ajaxConnection({
            'tx_lux_fe[dispatchAction]': 'linkClickRequest',
            'tx_lux_fe[identificator]': identification.getIdentificator(),
            'tx_lux_fe[arguments][linklistenerIdentifier]': linklistenerIdentifier,
            'tx_lux_fe[arguments][pageUid]': getPageUid()
          }, getRequestUri(), null, null);
          delayClick(event, 'LinkListener');
        }
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
   * Delay a click to a link to give AJAX some time
   *
   * @returns {void}
   */
  var delayClick = function(event, status) {
    var href = event.target.getAttribute('href');
    var delay = 400;
    if (isDebugMode()) {
      console.log(status + ' triggered. Redirect in some seconds to ' + href);
      delay = 5000;
    }
    if (href !== null) {
      event.preventDefault();
      setTimeout(
        function() {
          window.location = href;
        }, delay
      );
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
        'tx_lux_fe[arguments][href]': link.getAttribute('href')
      }, getRequestUri(), null, null);
      delayClick(event, 'Email4Link');
    } else {
      // show popup
      event.preventDefault();

      var title = link.getAttribute('data-lux-email4link-title') || '';
      var text = link.getAttribute('data-lux-email4link-text') || '';
      var href = link.getAttribute('href');
      var containers = document.querySelectorAll('[data-lux-container="email4link"]');
      if (containers.length > 0) {
        var container = containers[0].cloneNode(true);
        var html = container.innerHTML;
        html = html.replace('###TITLE###', title);
        html = html.replace('###TEXT###', text);
        html = html.replace('###HREF###', getFilenameFromHref(href));
        that.lightboxInstance = basicLightbox.create(html);
        that.lightboxInstance.element().querySelector('[data-lux-email4link="form"]').addEventListener('submit', function(event) {
          email4LinkLightboxSubmitListener(this, event, link);
        });
        that.lightboxInstance.show();
      }
    }
  };

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
    var href = link.getAttribute('href');
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
        'tx_lux_fe[arguments][values]': JSON.stringify(formArguments)
      }, getRequestUri(), 'email4LinkLightboxSubmitCallback', {sendEmail: (sendEmail === 'true'), href: href});
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
          that.lightboxInstance.close();
          window.location = callbackArguments.href;
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
      'tx_lux_fe[arguments][value]': value
    }, getRequestUri(), 'generalWorkflowActionCallback', null);
  };

  /**
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
      'tx_lux_fe[arguments][values]': JSON.stringify(formArguments)
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
    document.body.className += ' ' + 'lux_waiting';
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
   * Search for text "ENABLELUXDEBUG" anywhere on the website to show some debug information
   *
   * @returns {boolean}
   */
  var isDebugMode = function () {
    return document.body.innerHTML.search('ENABLELUXDEBUG') !== -1;
  }
}

/**
 * Get a singleton object in lux and luxenterprise
 *
 * @type {{getInstance: (function(): LuxIdentification)}}
 */
var LuxSingleton = (function() {
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

var Lux = LuxSingleton.getInstance();
Lux.initialize();
