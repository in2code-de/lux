![LUX](../../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

# FAQ


## How can I identify visitors?

Basicly there are 5 different ways at the moment:
- Use fieldMapping to map single fields of any forms on your website and animate visitors to fill out those fields
- Use formMapping to map complete forms on your website and animate visitors to fill out those forms
- Use email4link feature in CKEditor and offer whitepapers or other links and get the visitors email address
- If a user logs in into your TYPO3 frontend
- If a newsletter receiver opens a link from the newsletter (needs free extension luxletter)

see [Identification](../Identification/Index.md) for more information.


## How to track downloads with an extension like fal_securedownload or secure_downloads

LUX automatically tracks downloadable files (if file extensions are properly configured in TypoScript setup) even if
those files are related anywhere else then on the own website.
Nevertheless, we need to have real filenames to download (like `filename.pdf`).
So we would recommend to use the extension `fal_protect`. This extension allows to set permissions on files within
file storages (like fileadmin) but doesn't replace the URL.


## Debugging in JavaScript or why is Lux not working?

There is a debug output in browser console. This is helpful to find problems with lux on your website. E.g. it shows
if tracking is disabled and why. In addition fingerprint values are shown.

Two possibilities to turn on debug mode:

* Add a new cookie in your browser console (e.g. by clicking + at tab web storage in Firefox) with name `ENABLELUXDEBUG` and any value (e.g. 1)
* Simply add the string `ENABLELUXDEBUG` anywhere on your website


## How to enable tracking even if I'm logged in into backend?

With a single line of TypoScript constants:
`plugin.tx_lux.settings.disableTrackingForBackendUsers = 0`


## I change lib.lux.settings but nothing happens?

If you change/overwrite lib.lux, it is maybe not recognized in lux itself. This is caused by building a lib object in
lux and copying to plugin.tx_lux_fe and other parts before your sitepackage is included. If you want to change also
plugin.tx_lux_fe and other parts, you also have to copy your settings like described below.

Example TypoScript setup in your sitepackage:

```
lib.lux.settings {
    identification {
        # Define your own fieldmapping configuration
        _enable = 1
        fieldMapping {
            email {
                0 = *[email]
                1 = *[e-mail]
            }
        }

        # Define your own email4link configuration
        email4link.mail {
            _enable = 1
            fromName = Service
            fromEmail = service@yourcompany.com
            bccEmail = bcc@yourcompany.com
        }
    }

    # Define your own workflow configuration (part of luxenterprise)
    workflow.actions.6.configuration.1 {
        name = Slack Channel "_leads"
        webhookUrl = https://hooks.slack.com/services/X0287F3BJ/A9JJE587Y/rKIXTPbVEVnIPPNUQxFb98RO
        emoji =
    }
}

# Frontend functionality
plugin.tx_lux_fe.settings < lib.lux.settings

# Backend module
module.tx_lux.settings < lib.lux.settings

# Take care of your fieldMapping and formFieldMapping configuration
luxConfigurationFieldIdentification.10.settings < lib.lux.settings
luxConfigurationFormIdentification.10.settings < lib.lux.settings
```


## CKEditor seems to be broken

Per default LUX ships a configuration in YAML for CKEditor configuration to enable email4link feature. This should help
even beginners to make this feature work. Nevertheless this configuration can break your individual configuration for
the RTE. You can simply disable the configuration from LUX via Extension Manager configuration.


## Lux does not work - any hints?

* Did you clean all caches after installation (in Install Tool)?
* Please use a second browser (where you are not logged in into backend) for your tests (or enable tracking for BE-users via TypoScript)
* Please check if your browser does not use the *doNotTrack* settings (FireFox anonymous tab automaticly turns this function on)
* Please check if there is no local storage value *false* for entry *luxTracking* in your browser


## How to add own workflow triggers?

This is very simple - see [Workflows](../Campaigns/Index.md) for more information.


## How to add own workflow actions?

This is very simple - see [Workflows](../Campaigns/Index.md) for more information.


## How can I remove the annoying google bot from lux?

There is a cleanup command controller that can be used for this kind of task.

Example CLI call:
```
./vendor/bin/typo3cms luxcleanup:removevisitorbyproperty fingerprints.userAgent Googlebot 0
```

See [CommandController](../CommandController/Index.md) for more information.


## How to use opt-in instead of opt-out for cookies on my website?

If you want to use opt-in instead of opt-out functionality, there is a possibility for this -
see [Privacy](../../Privacy/Index.md) for more information.


## How to increase performance?

All data is stored on your server. The upside is quite clear in time of GDPR/DSGVO: You don't have to pass data to
third party companies. The downside could be, that a lot of data is stored within your TYPO3 database.
There a some possibilities to increase performance.

### 1. Update LUX and related extensions to the latest version

We are constantly working on performance improvements in LUX and LUXenterprise to deal with lot's of data in MySQL. See
changelog for version details

### 2. Turn on caches for dashboards and page overview view

We added a caching layer that, can speedup dashboard times with factor 100. Just turn it on in extension configuration.
Of course there is a comand that helps you to warmup caches (e.g. 1 time per night).

### 3. Clean outdated data from time to time

Remove all visitor data that is older then three years:

`./vendor/bin/typo3 lux:cleanupVisitorsByAge 94608000`

Remove only unknown visitors and their data that is older then one year:

`./vendor/bin/typo3 lux:cleanupUnknownVisitorsByAge 31536000`


### 4. memory_limit and max_execution_time in Apache settings

Depending on the settings of your server it could happen that some backend modules are crashing when open it.
While it is best practice to keep Apache settings for `memory_limit` and `max_execution_time` quite small to prevent
unneeded load in frontend, it would be nice to have some higher settings for all backend requests.
A possible solution for such a scenario would be to use **PHP FPM** and different domains for backend and frontend.
So `https://backend.yourdomain.org` could have a (e.g.) `memory_limit` of `512M` and a `max_execution_time` of `120`.


### 5. Extract lux data into a different database

**Untested** In TYPO3 you have the possibility to separate tables into different databases like:

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


### 6. Help from in2code

We offer help for users with in2code/luxenterprise. Just call us.


## AJAX requests don't work as expectded

### TypeNum declaration in site configuration

If you define any type-parameters in your site configuration, you have to define also all types for lux:

| Type       | Explanation                                  | Extension             |
|------------|----------------------------------------------|-----------------------|
| 1518815717 | Pagevisit request                            | in2code/lux           |
| 1517985223 | Fieldmapping configuration in JavaScript     | in2code/lux           |
| 1560095529 | Formmapping configuration in JavaScript      | in2code/lux           |
| 1680114177 | Email4link template file                     | in2code/lux           |
| 1591948099 | Url shortener                                | in2code/luxenterprise |
| 1520192598 | Render content in a lightbox                 | in2code/luxenterprise |
| 1560175278 | Render content via AJAX                      | in2code/luxenterprise |
| 1520796480 | Render content via contextual content plugin | in2code/luxenterprise |
| 1634676765 | Needed for A/B testing                       | in2code/luxenterprise |
| 1639660481 | Linkhandler typolink configuration           | in2code/luxenterprise |
| 1650897821 | API of luxenterprise                         | in2code/luxenterprise |

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
      pagevisit.json: 1518815717
      fieldmapping.js: 1517985223
      formmapping.js: 1560095529
      email4link.json: 1680114177
      redirect.php: 1591948099
      contentLightbox.html: 1520192598
      contentAjax.html: 1560175278
      contentContextualContent.html: 1520796480
      abtesting.html: 1634676765
      resolveTypolink.json: 1639660481
      leadapi.json: 1650897821
...
```

## How to add fields to email4link popup in frontend?

Let's say you want to add some fields where you also ask for the name or company or for a newsletter subscription.
This can be done very quick.

First of all, you can copy the Email4Link html template from LUX (in Private/Templates/Lux/Email4Link.html) and
adjust the new path via TypoScript:

```
# New path is EXT:mysitepackage/Resources/Private/Templates/Extensions/Lux/Frontend/Email4link.html
plugin.tx_lux_email4link.view.templateRootPaths.1 = EXT:mysitepackage/Resources/Private/Templates/Extensions/Lux/
```

Example change with a new field where you ask for a name, email, company and if the visitor wants to receive a
newsletter:

```
<div class="lux_lightbox_container">
    <h3>{download.title}</h3>
    <p>{download.text}</p>

    <form data-lux-email4link="form">
        <div class="form-group">
            <label for="lux_email4link_name" style="display: none;">Name</label>
            <input
                    type="text"
                    id="lux_email4link_name"
                    class="form-control"
                    required="required"
                    placeholder="Max Muster"
                    name="email4link[name]">
        </div>


        <div class="form-group">
            <label for="lux_email4link_email" style="display: none;">Email</label>
            <input
                    type="email"
                    id="lux_email4link_email"
                    class="form-control"
                    required="required"
                    placeholder="{f:translate(key:'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:frontend.email4link.lightbox.email')}"
                    name="email4link[email]">
        </div>


        <div class="form-group">
            <label for="lux_email4link_company" style="display: none;">Company</label>
            <input
                    type="text"
                    id="lux_email4link_company"
                    class="form-control"
                    placeholder="Company name"
                    name="email4link[company]">
        </div>


        <div class="form-check">
            <label>
                <input
                    type="checkbox"
                    class="form-check-input"
                    name="email4link[newsletter]"
                    value="1" />
                I want to get hottest news from you
            </label>
        </div>


        <f:if condition="{settings.addPrivacyLink}">
            <div class="form-check">
                <label>
                    <input
                            type="checkbox"
                            class="form-check-input"
                            required="required"
                            name="email4link[privacyChecked]"
                            value="1" />
                    <f:translate key="LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:frontend.email4link.lightbox.privacy" />
                    <f:link.typolink parameter="{settings.pidPrivacyPage}" target="_blank" />
                </label>
            </div>
        </f:if>


        <input
                type="submit"
                class="btn btn-primary"
                value="{f:translate(key:'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:frontend.email4link.lightbox.submit')}" />

    </form>

    <p class="lux_smalltext">{download.href}</p>

    <div data-lux-email4link="successMessageSendEmail" style="display:none;" class="alert alert-success">
        {f:translate(key:'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:frontend.email4link.lightbox.success')}
    </div>

    <div data-lux-email4link="errorEmailAddress" style="display:none;" class="alert alert-danger">
        {f:translate(key:'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:frontend.email4link.lightbox.emailerror')}
    </div>
</div>
```

Don't forget to allow the new fields in your TypoScript configuration:

```
lib.lux.settings {
    identification {
        email4link {
            form {
                fields {
                    enabled = name, email, company, newsletter, privacyChecked
                }
            }
        }
    }
}
plugin.tx_lux_fe.settings < lib.lux.settings
```

## How to track accordion opens or other content changes without page reload?

Page visits will be automatically be tracked with LUX on normal TYPO3 pages. In some rare scenarios
you may want to push an event after an interaction to LUX.
This can be helpful if you want to track accordion opens, multistep
forms or other content changes without page reload and without a different URL. You can simply push such a visit via
JavaScript:

```
const lux = LuxSingleton.getInstance();
lux.push('accordion 1 just opened', 'eventTrackRequest');
```

## How to track searchterms with my AJAX onpage search?

If you don't have the searchterm as GET parameter (e.g. `?q=searchterm`) available in the URL,
you can simply push a searchterm via JavaScript manually to LUX.
This can be e.g. helpful if your search results are only loaded via AJAX.
See this example how to push a searchterm via JS:

```
const lux = LuxSingleton.getInstance();
lux.push('any searchterm', 'searchRequest');
```

## How to switch off the extension?

If you want to turn off the frontend functionality (e.g. in case of DoS attacks...), you could use TypoScript constants:

```
# All frontend functionalities can be toggled for testing or against flooding
plugin.tx_lux.settings.enableFrontendController = 0
```
