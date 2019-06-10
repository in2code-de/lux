<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### FAQ


#### How can I identify visitors?

Basicly there are three different ways at the moment:
- Use fieldMapping to map single fields of any forms on your website and animate visitors to fill out those fields
- Use formMapping to map complete forms on your website and animate visitors to fill out those forms
- Use email4link feature in CKEditor and offer whitepapers or other links and get the visitors email address

see [Identification](../Identification/Index.md) for more information.


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
