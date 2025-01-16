![LUX](../../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

### Backend Module Leads/Leads

Change the view to List module by clicking on "Leads".

The backend module Leads show you all identified (and anonymous) leads.
<img src="../../Images/documentation_leads_list.png" width="800" />

See all your leads in a listview on the left side. While - on the right side - you can see useful information
(number of page visits and hottest leads). The left side itself is split into a filter area and the table-list-view
below.




#### Filter

| Field            | Description                                                                                                                 |
|------------------|-----------------------------------------------------------------------------------------------------------------------------|
| Searchterm       | Use this fulltext search field for filtering (name, email, company, fingerprint, etc...)                                    |
| Scoring          | Search for leads with a minimum scoring                                                                                     |
| Category-Scoring | Show only leads that have a category-scoring of a given category (Table columns change - Categoryscoring will be available) |
| Time-From        | Search for leads that are only known since a defined date and time                                                          |
| Time-To          | Search for leads that are only active until a defined date and time                                                         |
| Identified only  | List only identified leads in the table below                                                                               |

**Filter Buttons**
Filter now: Let's filter the table with our properties
Reset filter: Let's reset the table to original list view
Download button: The download button let you download the lead table in format CSV




#### Table

The table will show you your website leads.

| Column              | Description                                                                                                              |
|---------------------|--------------------------------------------------------------------------------------------------------------------------|
| Lead identification | Use this fulltext search field for filtering (name, email, company, fingerprint, etc...)                                 |
| Lead scoring        | Show only leads that visited a page (Table rows change - pagevisits available now)                                       |
| Email               | Search for leads with a minimum scoring                                                                                  |
| Company             | Show only leads that have a category-scoring of a given category (Table rows change - Categoryscoring will be available) |
| Last visit          | Search for leads that are only known since a defined date and time                                                       |
| Number of visits    | Search for leads that are only active until a defined date and time                                                      |
| Pagevisits          | Only viewable if you filter for a page (will replace column *Number of visits*)                                          |
| Categoryscoring     | Only viewable if you filter for a scoring of a category                                                                  |

**Orderings:** Per default the orderings is identified leads first and after that sort by scoring. The orderings can
change if you are using a special filter.




#### Detail Preview

If you click in a table row, a small lead preview will be loaded via AJAX. Both diagrams on the right side will be
replaced with a preview box and a scoring box.

<img src="../../Images/documentation_leads_list_detail.png" width="800" />

* If you are using company detection via Wiredminds (see [Companies](Companies.md)), you can change the related company
manually.
* If you add a text into the textarea *Internal description*, this notice is saved automatically if the focus gets
lost on the field (on blur).

Clicking on *Show lead details* will open the detail page.




#### Detail View

The detail view will give you a couple of information of the chosen lead. The view is splitted into 7 boxes:
* Lead information overview
* Activity log
* Conversion funnel / Page visits
* Lead scoring
* Categoryscoring
* Pagevisits
* Properties
* Profiles

<img src="../../Images/screenshot_detail.png" width="800" />

##### Lead information

See all relevant lead information.

It starts with the *Lead identification*. Depending on the information we have, this
will show the email or the first- and lastname or simply "Anonymous".
Beside this, you will see the general scoring and the hottest category scoring (if there is one). A click on the google
icon will open a new tab with google and let you search for the lead.

After the first line, there are information that came from field mapping, meta information (first and last
visit) and information enriched by the visitors IP-address. The description field is also shown and can be used in the
same way as described in *Detail Preview*.

##### Activity log

See the most interesting activities of this lead related to lux. You will see information like *Lead gets identified*,
*Lead gets identified by email4link" or "Lead downloads an asset". Also every workflow that takes action on this lead
is listed here.

##### Conversion funnel / Page visits

See from which referrer your lead start to visit your website and follow page by page.

##### Lead scoring

See the lead scoring of the lead in the last 6 weeks. That gives you the possibility to decide, how your nurturing
workflows are running.

**Note:** The scoring calculation can be done in the Extension Manager settings of the extension. If you are using
the value *lastVisitDaysAgo*, you should use a CommandController to calculate the lead scoring one time a day.

**Tipp:** Use your mousecursor for getting a date and time in a tooltip to every activity.

##### Categoryscoring

See a diagram with all available categoryscorings for this lead.

**Note:** The calculation can be influenced by the Extension Manager settings of the extension.
**Note:** See [Categoryscorings](../Categoryscorings/Index.md) how to use category scoring in lux.

##### Pagevisits

See the number of page visits of the last weeks.

##### Properties

If you collect more data than just email, firstname, lastname and company (see identification part of the documenation)
You need also to see this information. And this view shows you all collected attributes from the visitor.

##### Profiles

Because your lead could use more than just one device, all fingerprints + legacy cookies and devices are listed here
with some additional information.

##### Interaction Buttons

On the bottom of the detail view, you will find three buttons.

* Go back: Browser will show the previous page
* Blacklist: This lead will be blacklisted. This means, the lead is not visible in any view anymore. This is helpful, if you identify a searchengine crawler (maybe with a high scoring). In addition, all properties and related tables are cleaned!
* Remove completely: This will remove all information about this lead from your system. Remove means that the records are really remove - not only a deleted=1!
