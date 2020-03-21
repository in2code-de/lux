<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### Tracking and Analysis

This part of the documentation describes all configuration parts related to analysis and tracking of visitors and will
be automaticly loaded if you choose the main lux static template in the root template.

#### TypoScript Configuration

The file [01_TrackingConfiguration.typoscript](../../../Configuration/TypoScript/Lux/01_TrackingConfiguration.typoscript)
contains all relevant settings for the visitor tracking.

Lux basicly tracks every vistors page request with the current time. In addition it's possible to also track asset
downloads.

See the inline comments for a description in TypoScript:

```
lib.lux.settings {

    # All tracking settings
    tracking {
        pagevisits {
            # Toggle pagevisit tracking (on/off).
            # Switching this feature on will flood table tx_lux_domain_model_pagevisit but allow you to see a full pagefunnel of all of your visitors. Your decision :)
            _enable = {$plugin.tx_lux.settings.tracking.page}
        }
        assetDownloads {
            # Toogle asset download tracking (on/off)
            _enable = {$plugin.tx_lux.settings.tracking.assetDownloads}

            # Allow only files with this extensions
            allowedFileExtensions = {$plugin.tx_lux.settings.tracking.assetDownloads.allowedExtensions}
        }
    }
}
```

Constants to this TypoScript part:
```
plugin.tx_lux.settings {
    # cat=lux//0010; type=boolean; label= Activate page tracking: (De)Activate tracking of the users pagefunnel.
    tracking.page = 1

    # cat=lux//0020; type=boolean; label= Activate download tracking: (De)Activate tracking if the user downloads an asset.
    tracking.assetDownloads = 1

    # cat=lux//0020; type=text; label= Activate download tracking: (De)Activate tracking if the user downloads an asset.
    tracking.assetDownloads.allowedExtensions = pdf,txt,doc,docx,xls,xlsx,ppt,pptx,jpg,png,zip
}
```

Example page tracking request in browser console:
<img src="../../../Documentation/Images/documentation_installation_browserrequest.png" width="800" />



### Backend Module Analysis

#### Dashboard

Now, if lux is up and running, you should see information in the Analysis Backend Module in the dashboard view:
<img src="../../../Documentation/Images/screenshot_dashboard.png" width="800" />

The dashboard view should give you a quick overview about the latest activities and some useful information:
* How many recurring/unique leads
* The latest page visits
* The latest activities
* Identified leads vs. unknown leads
* Identifaction rate of the latest months
* Hottest leads (orderings by scoring)
* A world map for a basic visitor analysis
* A basic statistics about the records that are stored in lux

Clicking on a name/email/"anonymous" will open a detail page with some more information of the lead.

**Note:** You can filter the dashboard view to leads from this or from previous month or from this year with the filter
select at the top

With a bit of TypoScript configuration it's possible to decide which activity status should be shown in activity log
in dashboard view:
```
lib.lux.settings {
    backendview {
        analysis {
            activity {
                # Greater then 0 means to also have "lux identified a new lead", greater 1 means to not have this kind of messages
                statusGreaterThen = 1
            }
        }
    }
}
```

#### Content

If you choose the content view (see top left to switch from dashboard to content), you will see the 100 most interesting
pages and assets for your leads.

<img src="../../../Documentation/Images/screenshot_analysis_content.png" width="800" />

Clicking on an asset or a page will open a detail page to this item, where you can exactly see which lead was interested
in this item.


### TYPO3 Dashboard Module

In addition to the build in dashboard, since TYPO3 10 it is possible to install another dashboard for system wide
diagrams in TYPO3. 
You could do this simply with `composer require typo3/cms-dashboard`. Once it is available, you can add some widgets
from Lux. 

This is a screenshot fom default values:

<img src="../../../Documentation/Images/screenshot_typo3dashboard.png" width="800" />
