<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

## Technical documenation

This documentation is the interesting part for administrators and developers and should clearify how lux can be
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

Every visitor will get an unique cookie with name `luxId`. This id will be used for tracking and identification.
A second cookie can be added for an opt out with the name `luxTrackingOptOut`.

See more information in the chapters:

### [Installation](Installation/Index.md)
### [Analysis](Analysis/Index.md)
### [Identification](Identification/Index.md)
### [Scoring and Categoryscoring](Categoryscorings/Index.md)
### [Leads](Leads/Index.md)
### [Workflows](Workflows/Index.md)
### [Plugins and Pageoverview](Plugins/Index.md)
### [Finisher](Finisher/Index.md)
### [CommandController](CommandController/Index.md)
### [FAQ](FAQ/Index.md)
