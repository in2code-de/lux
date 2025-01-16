![LUX](../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

## Introduction

Whenever a lead visits your website, we want to track which pages have been seen or which document were downloaded.
This makes it necessary to recognize the lead when leaving page a and entering page b or when the lead comes again on
another day.
To fulfill this requirement, there a basically two different possible technics available with LUX.

### Fingerprint (recommended for B2B)

Per default LUX runs in fingerprint mode and try to automatically recognize a visitor by its hardware combined with
his IP-address. Those values are hashed to one string (sha256) and cannot be reverted or splitted into its originally
parts.

Values are:

* userAgent
* webdriver
* language
* colorDepth
* deviceMemory
* hardwareConcurrency
* screenResolution and availableScreenResolution
* timezone and timezoneOffset
* sessionStorage and localStorage
* indexedDB and openDatabase
* addBehavior
* cpuClass
* platform
* plugins
* canvas
* webgl and webglVendorRenderer
* hasLiedLanguages and hasLiedResolution and hasLiedOs and hasLiedBrowser
* touchSupport
* fonts
* audio
* IP-address

**Upside:** Fingerprint is calculated automatically and does not need to be stored anywhere on the device
(cookie or local storage). A tracking between different domains and page branches is possible
within the same TYPO3 instance.

**Downside:** To calculate a fingerprint every page request needs 200-400ms on top (asynchronous).
Beside that multiple visitors with same hard- and software
are recognized as only one visitor, if they are using the same IP-address.
This is especially true for iPhones of the same version and generation.

### LocalStorage (recommended for B2C)

**Upside:** If you have a lot of mobile visitors on your website (e.g. if you own a b2c shop for a younger target
group), you may want to differ between your visitors. So you could go for LocalStorage. This is comparable to a cookie.
A random string will be saved on the visitor's device, which can be done very quickly.
You can also differ between multiple mobile visitors - even with same hardware and IP-address.

**Downside:** You have to ask your visitor if you are allowed to store a random string the local storage of the device,
to identify your visitor. To meet GDPR rules, we would suggest you to set up a cookie banner.
In addition, a visitor of domain A is not automatically merged if he also visits domain B on the same TYPO3 instance
(every domain has its own local storage area. Of course if the user is identified on both domains, the profile will be
merged to only one).

This can be turned on via TypoScript constants:

```
plugin.tx_lux.settings.identificationMethod = 2
```

**Note:** Maybe you want to disable the "autoenable" functionality. See [OptIn and OptOut](OptInAndOptOut.md)
