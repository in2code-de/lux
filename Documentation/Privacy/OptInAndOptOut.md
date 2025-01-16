![LUX](../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

## Tracking Opt-Out and Opt-In

### Opt-In or Opt-Out functionality

First of all you have to decide, if LUX should track by default (no extra Opt-In needed, but an Opt-Out for disabled
tracking) or if your website visitor should allow the tracking (normally done in a cookie banner popup - so
with Opt-In functionality).

Per default LUX tracks visitors without their explicit agreement and they have to Opt-Out if they don't want to be
tracked any more (see some examples below how to Opt-Out).
If you want to not automaticly track your visitors without their agreement, you can use the opt-in functionality
of LUX.

In th first step, you have to disable the `autoenable` function via **TypoScript constants**:

```
plugin.tx_lux.settings.autoenable = 0
```

Now, LUX will not track visits per default (you can double check if `data-lux-autoenable="0"` is set in your HTML
source). In the next step, you can place an HTML-element anywhere on the page with `data-lux-trackingoptin="true"`.
A manual click on this element will allow the tracking (a local storage record `luxTracking=true` is set in the browser).

```
<span data-lux-trackingoptin="true">Opt-In for LUX analyses</span>
```

This can also be disabled again with an element like:

```
<span data-lux-trackingoptin="false">Opt-Out for LUX analyses</span>
```

As an alternative, you can directly access JavaScript functions to Opt-In or Opt-Out. This is helpful if you work with
a cookie banner or a cookie lightbox like Usercentrics or a similar solution.

If you add the default JS before, what is normally automatically done via TypoScript setup in LUX
(file EXT:lux/Resources/Public/JavaScript/Lux/Lux.min.js and
if you have also installed LUXenterprise file EXT:luxenterprise/Resources/Public/JavaScript/Lux/LuxEnterprise.min.js),
you can use this JavaScript:

```
# Opt-In
var Lux = LuxSingleton.getInstance();
Lux.optIn();

# Opt-Out
var Lux = LuxSingleton.getInstance();
Lux.optOut();

# Opt-Out and reload for a stop of all tracking mechanism at once
var Lux = LuxSingleton.getInstance();
Lux.optOutAndReload();
```

#### Example usage of OptIn with Cookiebot consent manager

If you want to disable tracking by default and enable tracking via Cookiebot consent manager, you could add a JavaScript
to your page like:

```
<script type="text/javascript">
  window.addEventListener('CookiebotOnAccept', function(e) {
    if (!Cookiebot.consent.marketing) return;
    var Lux = LuxSingleton.getInstance();
    Lux.optIn();
  }, false);
</script>
```

Note: Do not forget to use `plugin.tx_lux.settings.autoenable=0` via TypoScript setup

### Opt-Out Plugin

As known from Matomo (former known as Piwik) also LUX offers a Plugin for an Opt-Out possibility for visitors. You
can use it as a regulare editor.

<img src="../Images/documentation_plugin_optout_frontend1.png" width="800" />

### DoNotTrack Header

Browsers support a (per default turned off) option to inform the website that the visitor don't wants to be tracked.
This is the *DoNotTrack* or *DNT* setting. Even if this rare used feature of the browser is only a recommendation, LUX
will respect this setting of course!

<img src="../Images/documentation_marketing_donottrack.png" width="800" />

**Note:** While Firefox turns on the DNT by default for anonymous tabs, Chrome and Internet Explorer never turn this
setting on by default.

**Note:** Maybe you want to switch from fingerprint to local storage mode. See [OptIn and OptOut](FingerprintsAndLocalStorage.md)
