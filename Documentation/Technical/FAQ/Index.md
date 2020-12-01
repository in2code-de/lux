<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### FAQ


#### How can I identify visitors?

Basicly there are 5 different ways at the moment:
- Use fieldMapping to map single fields of any forms on your website and animate visitors to fill out those fields
- Use formMapping to map complete forms on your website and animate visitors to fill out those forms
- Use email4link feature in CKEditor and offer whitepapers or other links and get the visitors email address
- If a user logs in into your TYPO3 frontend
- If a newsletter receiver opens a link from the newsletter (needs free extension luxletter)

see [Identification](../Identification/Index.md) for more information.


#### Debugging in JavaScript or why is Lux not working?

If you add the string `ENABLELUXDEBUG` anywhere on your website, the JS-debugging mode is turned on. So you will see
if Lux is activated or deactivated and why in the browser console. In addition fingerprint values are shown.


#### How to enable tracking even if I'm logged in into backend?

With a single line of TypoScript constants:
`plugin.tx_lux.settings.disableTrackingForBackendUsers = 0`


#### I change lib.lux.settings but nothing happens?

If you change/overwrite lib.lux, take care that you tell the plugin and the module that this changed.

Example TypoScript setup:

```
# Add Slack webhookUrl
lib.lux.settings.workflow.actions.4.configuration.webhookUrl = https://hooks.slack.com/services/mywebhookcode
lib.lux.settings.workflow.actions.4.configuration.emoji =
plugin.tx_lux_fe.settings < lib.lux.settings
module.tx_lux.settings < lib.lux.settings
```


#### Lux does not work - any hints?

* Did you clean all caches after installation (in Install Tool)?
* Please use a second browser (where you are not logged in into backend) for your tests (or enable tracking for BE-users via TypoScript)
* Please check if your browser does not use the *doNotTrack* settings (FireFox anonymous tab automaticly turns this function on)
* Please check if there is no cookie value *true* for cookie *luxTrackingOptOut* in your browser


#### How to add own workflow triggers?

This is very simple - see [Workflows](../Workflows/Index.md) for more information.


#### How to add own workflow actions?

This is very simple - see [Workflows](../Workflows/Index.md) for more information.


#### How can I remove the annoying google bot from lux?

There is a cleanup command controller that can be used for this kind of task.

Example CLI call:
```
./vendor/bin/typo3cms luxcleanup:removevisitorbyproperty fingerprints.userAgent Googlebot 0
```

See [CommandController](../CommandController/Index.md) for more information.


#### How to use opt-in instead of opt-out for cookies on my website?

If you want to use opt-in instead of opt-out functionality, there is a possibility for this -
see [Privacy](../../Privacy/Index.md) for more information.


#### How to increase performance?

All data is stored on your server. The upside is quite clear in time of GDPR/DSGVO: You don't have to pass data to
third party companies. The downside could be, that a lot of data is stored within your TYPO3 database.
There a some possibilities to increase performance.

##### 1. Extract lux data into a different database

In TYPO3 you have the possibility to separate tables into different databases like:
```
<?php
$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'] = [
    'Default' => [
        'charset' => 'utf8',
        'driver' => 'mysqli',
        'dbname' => 'typo3',
        'host' => '127.0.0.1',
        'user' => 'typo3',
        'password' => 'anypassword'
    ],
    'Lux' => [
        'charset' => 'utf8',
        'driver' => 'mysqli',
        'dbname' => 'typo3lux',
        'host' => '127.0.0.1',
        'user' => 'typo3lux',
        'password' => 'anypassword'
    ],
];
$GLOBALS['TYPO3_CONF_VARS']['DB']['TableMapping'] = [
      'tx_lux_domain_model_visitor' => 'Lux',
      'tx_lux_domain_model_fingerprint' => 'Lux',
      'tx_lux_domain_model_attribute' => 'Lux',
      'tx_lux_domain_model_pagevisit' => 'Lux',
      'tx_lux_domain_model_ipinformation' => 'Lux',
      'tx_lux_domain_model_download' => 'Lux',
      'tx_lux_domain_model_categoryscoring' => 'Lux',
      'tx_lux_domain_model_log' => 'Lux'
];
```

##### 2. Clean outdated data from time to time

Remove all visitor data that is older then three years:

`./vendor/bin/typo3 lux:cleanupVisitorsByAge 94608000`

Remove only unknown visitors and their data that is older then one year:

`./vendor/bin/typo3 lux:cleanupUnknownVisitorsByAge 31536000`


##### 3. Help from in2code

We offer help for users with in2code/luxenterprise. Just call us.


#### AJAX requests don't work as expectded

##### TypeNum declaration in site configuration

If you define any type-parameters in your site configuration, you have to define also all types for lux:

| Type | Explanation | Extension |
|------|-------------|-----------|
| 1518815717 | Pagevisit request | in2code/lux |
| 1517985223 | Fieldmapping configuration in JavaScript | in2code/lux |
| 1560095529 | Formmapping configuration in JavaScript | in2code/lux |
| 1591948099 | Url shortener | in2code/luxenterprise |
| 1520192598 | Render content in a lightbox | in2code/luxenterprise |
| 1560175278 | Render content via AJAX | in2code/luxenterprise |
| 1520796480 | Render content via contextual content plugin | in2code/luxenterprise |

Example configuration:

```
...
rootPageId: 1
routes:
  -
    route: robots.txt
    type: staticText
    content: "Disallow: /typo3/\r\n"
routeEnhancers:
  PageTypeSuffix:
    type: PageType
    default: /
    index: ''
    suffix: /
    map:
      pagevisit.html: 1518815717
      fieldmapping.js: 1517985223
      formmapping.js: 1560095529
      redirect.php: 1591948099
      contentLightbox.html: 1520192598
      contentAjax.html: 1560175278
      contentContextualContent.html: 1520796480
...
```
