<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

## Tracking Opt-Out and Opt-In

### Opt-Out Plugin

As known from Matomo (former known as Piwik) also Lux offers a Plugin fo an Opt-Out possibility for visitors.

<img src="../Images/documentation_plugin_optout_frontend1.png" width="800" />

### Opt-In Functionality

If you want to not automaticly track your visitors without their acceptence, you can use the opt-in functionality
of Lux.

First of all, you have to disable the autoenable function via TypoScript constants:

```
plugin.tx_lux.settings.autoenable = 0
```

Now, lux will not track visits per default. As next step, you should place a HTML-element
anywhere on the page with data-lux-trackingoptin="true". A click on this element will allow the tracking.

```
<span data-lux-trackingoptin="true">Opt-In for Lux analyses</span>
```

This can also be disabled again with an element like:

```
<span data-lux-trackingoptin="false">Opt-Out for Lux analyses</span>
```

How to work with this possibilities? You can add a cookie banner or a cookie lightbox over your website where you can
ask your visitor, if it's ok to set a cookie for e.g. usability reasons. Place the data-attribute on the ok-button and
close the banner.

You could also call the JavaScript function directly for Opt-In or Opt-Out (if JS is already added from
EXT:lux/Resources/Public/JavaScript/Lux/Lux.min.js before):

```
var Lux = LuxSingleton.getInstance();
Lux.initialize();
Lux.optIn();

# or
Lux.optOut();
```

### DoNotTrack Header

Browsers support a (per default turned off) option to inform the website that the visitor don't wants to be tracked.
This is the *DoNotTrack* or *DNT* setting. Even if this rare used feature of the browser is only a recommendation, Lux
will respect this setting of course!

<img src="../Images/documentation_marketing_donottrack.png" width="800" />

**Note:** While Firefox turns on the DNT by default for anonymous tabs, Chrome and Internet Explorer never turn this
setting on by default.
