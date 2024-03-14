![LUX](/Documentation/Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](/Documentation/Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

# Settings for editors

While administrators have access to all data in LUX and LUXenterprise, editors have only limited access to guarantee
privacy in a multiclient installation.

## Access per Site

Data is recorded now with site identifiers. This ensures to split information by site identifiers. If an editor needs
access to LUX data in a site, take care that this user has reading access to the root page of a site.

This means, that if an editor needs access to all LUX data, ensure that this editor has reading access to all root
pages.

## Access to categories

Basically there are three different category types that are used in LUX:

* Category Scoring is calculated on page access or downloads
* Company Categories are used to segment companies (Wiredmind integration into LUX)
* Workflow Categories are used to group workflows for a better management (Part of LUXenterprise)

Take care that your editors have reading access to the page where relevant categories are stored.

## Storage PID for linklistener, shortener and utm generator records

Since version 35.0.0 you can define where to store those types of records depending on the backend user. There is now
more flexibility via User TSConfig (in backend user or backend usergroup records):

```
tx_lux {
    defaultPage {
        tx_lux_domain_model_linklistener = 1
        tx_luxenterprise_domain_model_shortener = 2
        tx_luxenterprise_domain_model_utmgenerator_uri = 3
    }
}
```
