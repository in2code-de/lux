![LUX](/Documentation/Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](/Documentation/Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

## Technical documenation

This documentation is the interesting part for administrators and developers and should explain how lux can be
installed, configured and how does it work. In addition we want to give you some hints how to extend lux with your own
workflow triggers or workflow actions.

### Introduction

Lux is build to use asynchronical techniques and without the need of any USER_INT function to also use lux together with
staticfilecache extension for **high performance** on your TYPO3 websites.

There is **no need to use a JS framework like jQuery** - Lux just uses vanilla JS - also for the lightbox implementation.

But TYPO3 should run in **composer mode** to allow the loading of additional packages (see Installation for more
details).

Tracking and doing workflow stuff in frontend needs **JavaScript** to be enabled in the visitors browser. Some functions
are disabled if the visitor is also logged in into backend at the same time (to avoid misleading data).
It's possible for visitors to opt out (with the opt-out plugin) or to use the *do not track* settings in their browser.
Lux will respect this settings in every case.

Every visitor has an individual fingerprint, based on his hard- and software. This anonymous hash is used to
recognize the visitor in future visits. This hash will be used for tracking and identification.

There are some functional settings - saved to localstorage in browser which are:
* `luxDisableEmail4Link` to disable email4link popups if a visitor is already identified
* `luxTracking` for a tracking opt out or opt in

See more information in the chapters:

### [Installation](Installation/Index.md)
### [Editor configuration](Editors/Index.md)
### [Analysis](Analysis/Index.md)
### [Identification](Identification/Index.md)
### [Scoring and Categoryscoring](Categoryscorings/Index.md)
### [Leads](Leads/Index.md)
### [Marketing campaigns](Campaigns/Index.md)
### [Plugins and Pageoverview](Plugins/Index.md)
### [Finisher](Finisher/Index.md)
### [Commands & Scheduler Tasks](Commands/Index.md)
### [API](API/Index.md)
### [Events (PSR-14)](Events/Index.md)
### [FAQ](FAQ/Index.md)
### [Changelog and breaking changes](Changelog/Index.md)
