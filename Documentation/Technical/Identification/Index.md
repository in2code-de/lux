<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### Identification

This part of the documentation describes different possibilities to **identify your leads**.
Identification means that you are able to know the person behind the lead.
And this needs an unique identification-property from the lead.
E.g. firstname would be - of course - not a good property because this is not unique.
Marketing Automation tools are going to use the **email-address** as unique property of the user.
And... emails are great because, you can:

* identify the lead
* see the firstname as part of the email address (mostly given in business cases)
* see the lastname as part of the email address (mostly given in business cases)
* see the company as part of the email address (mostly given in business cases)
* having an url (company) where you can simply find more interesting stuff of your lead
* send marketing relevant information in newsletters

So, if email-addresses are great and we want to collect the email from our unknown leads - how can we do this? The
answer is simple: We have to deal a bit with our visitors. Let them download an interesting asset (e.g. a whitepaper)
but ask for their email address first. Another way would be to offer a callback-form where you offer to call the
visitor - also ask for the email address here. Is there a form, where the visitor can get access to a software?
You guessed it, ask for the email address.

Going back to the technical part - forms will help us on lead identification.

The possibilities to identify a lead in lux:
* Identify via field mapping configuration (any single form field in TYPO3)
* Identify via form mapping configuration (any form in TYPO3)
* Identify via Email4link CKEditor plugin
* Identify via Frontend login
* Identify via Luxletter extension


#### Field mapping

The easiest way for an identification is to offer some forms on your website where you listen to the values that your
visitors add there. Lux will help you to listen on any webform (no matter if it is powermail, femanager, form,
formhandler, felogin or a static html content). The technique behind is simple. You can map field names to lead
properties via TypoScript.

The default mapping will e.g. map a field with name `tx_powermail_pi1[field][e-mail]` to the lead property *email*
(see TypoScript below).

```
lib.lux.settings {

    # All identification settings
    identification {
        # En- or Disable field and form identification
        _enable = {$plugin.tx_lux.settings.fieldandformidentification}

        # Auto identify with EVERY input field of your website.
        # Disabled for forms with [data-lux-form-identification] and also disabled for fields with [data-lux-disable]
        # Using a * as wildcard will search for a string in a string while using a key without * will search for the exact match.
        fieldMapping {
            # "email" is the key-field for visitor identification and merging.
            email {
                0 = *[email]
                1 = *[e-mail]
                2 = *[e_mail]
            }
            firstname {
                0 = *[firstname]
                1 = *[first-name]
                2 = *[first_name]
                3 = *[firstName]
                4 = *[vorname]
            }
            lastname {
                0 = *[lastname]
                1 = *[last-name]
                2 = *[last_name]
                3 = *[lastName]
                4 = *[surname]
                5 = *[sur-name]
                6 = *[sur_name]
                7 = *[name]
                8 = *[nachname]
            }
            company {
                0 = *[company]
                1 = *[firma]
            }
            phone {
                0 = *[phone]
                1 = *[telefon]
                2 = *[tel]
            }
            username {
                0 = username
                1 = user
                2 = benutzername
                3 = benutze
            }
        }
    }
}

```

**Note:** Take care that your lib.lux configuration is recognized by lux (see FAQ section how to copy it to plugin.tx_lux_fe)

You can test the field mapping by adding a simple form to your frontend. Open a second browser and fill out the form.
As soon as you enter an email and leave the field (on blur), the values are sent asynchronical to Lux. You don't have to
submit the form. Just go into the backend and check if the lead gets identified.

**Note:** Password fields are ignored for this function

Example powermail form in frontend:
<img src="../../../Documentation/Images/documentation_identification_fieldmapping_frontend.png" width="800" />

Example lead identification in dashboard:
<img src="../../../Documentation/Images/documentation_identification_fieldmapping_dashboard.png" width="800" />


#### FormFieldMapping

Another way for identifications is to listen to complete form submits of any forms on your website.
If you want to send all field informations of a form to lux, just add a `data-lux-form-identification="true"` to the
form-tag itself.

There some different things when comparing FormFieldMappinng with FieldMapping (see above):
* The data will be send to lux when the visitor submits the form and not before
* The complete form will be send to lux (but only the fields that are defined in mapping configuration)
* You have to change the markup of the forms with a data-attribute to the form tag

The default field-mapping is similar to the FieldMapping: E.g. map a field with name `tx_form_formframework[e-mail]`
to the lead property *email* (see TypoScript below).


```
lib.lux.settings {

    # All identification settings
    identification {
        # En- or Disable field and form identification
        _enable = {$plugin.tx_lux.settings.fieldandformidentification}

        # Identify by complete form submits with '<form data-lux-form-identification="true">'
        # If you want to stop the submit process (pagereload or redirect), you can use data-lux-form-identification="preventDefault"
        # Using a * as wildcard will search for a string in a string while using a key without * will search for the exact match.
        formFieldMapping {
            email {
                0 = email
                1 = *[email]
                2 = *[e-mail]
                2 = *[e_mail]
            }
            firstname {
                0 = firstname
                1 = *[firstname]
                2 = *[first-name]
                3 = *[first_name]
                4 = *[firstName]
                5 = *[vorname]
            }
            lastname {
                0 = lastname
                1 = *[lastname]
                2 = *[last-name]
                3 = *[last_name]
                4 = *[lastName]
                5 = *[surname]
                6 = *[sur-name]
                7 = *[sur_name]
                8 = *[name]
                9 = *[nachname]
            }
            company {
                0 = company
                1 = *[company]
                2 = *[firma]
            }
            phone {
                0 = phone
                1 = *[phone]
                2 = *[telefon]
                3 = *[tel]
            }
            product {
                0 = product
                1 = *[product]
                2 = *[produkt]
            }
            message {
                0 = message
                1 = *[message]
                2 = *[nachricht]
            }
        }
    }
}

```

**Note:** Take care that your lib.lux configuration is recognized by lux (see FAQ section how to copy it to plugin.tx_lux_fe)

**Note:** If you want to stop the submit process, because lux had a workflow for the submit action (show text on form
submit, etc..), you can use `data-lux-form-identification="preventDefault"`
instead of `data-lux-form-identification="true"`

Example form (added via content element html):

```
<form data-lux-form-identification="preventDefault" action="#">
	<div class="form-group">
		<label for="email">Ihre E-Mail-Adresse</label>
		<input type="email" class="form-control" id="email" name="test[email]">
	</div>
	<div class="form-group">
		<label for="firstname">Vorname</label>
		<input type="text" class="form-control" id="firstname" name="firstname">
	</div>
	<div class="form-group">
		<label for="lastname">Nachname</label>
		<input type="text" class="form-control" id="lastname" name="lastname">
	</div>
	<div class="form-group">
		<label for="company">Firma</label>
		<input type="text" class="form-control" id="company" name="company">
	</div>
	<div class="form-group">
		<label for="phone">Phone</label>
		<input type="text" class="form-control" id="phone" name="phone">
	</div>
	<div class="form-group">
		<label for="product">Zu welchem Produkt können wir Ihnen helfen?</label>
		<select class="form-control" id="product" name="product">
			<option>Lux</option>
			<option>Content Publisher</option>
			<option>Schulpaket</option>
			<option>CERMAT</option>
			<option>Personendatenbank</option>
		</select>
	</div>
	<div class="form-group">
		<label for="message">Ihre Nachricht an uns</label>
		<textarea class="form-control" id="message" rows="3" name="message"></textarea>
	</div>
	<div class="form-check">
		<label class="form-check-label">
			<input type="checkbox" class="form-check-input" value="1" name="agb" required>
			<a href="/privacy/">Datenschutzerklärung</a> gelesen und akzeptiert
		</label>
	</div>
	<button type="submit" class="btn btn-primary">Nachricht versenden</button>
</form>
```

Form in frontend:
<img src="../../../Documentation/Images/documentation_identification_formmapping_frontend.png" width="800" />

Example lead identification in dashboard:
<img src="../../../Documentation/Images/documentation_identification_formmapping_dashboard.png" width="800" />


#### Email4link

There is another build in feature, where you can simply ask for email addresses if a lead wants to download something.
A plugin for the CKEditor helps you to change every link into a lux email4link-link.

Example configuration yaml file for your CK Editor with lux email4link:

```
imports:
    - { resource: "EXT:rte_ckeditor/Configuration/RTE/Processing.yaml" }
    - { resource: "EXT:rte_ckeditor/Configuration/RTE/Editor/Base.yaml" }
    - { resource: "EXT:rte_ckeditor/Configuration/RTE/Editor/Plugins.yaml" }

editor:
  externalPlugins:
    luxEmail4Link:
      resource: 'EXT:lux/Resources/Public/JavaScript/Static/CkEditorPlugins/luxEmail4Link/plugin.js'

  config:

    extraAllowedContent: 'a[data-*];'
    contentsCss: ['EXT:lux/Resources/Public/Css/Modules.min.css']

    toolbarGroups:
      - { name: 'styles', groups: [ 'styles' ] }
      - { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] }
      - { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] }
      - { name: 'links', groups: [ 'links', 'Email4Link' ] }
...
```

See the related default TypoScript configuration:
```
lib.lux.settings {

    # All identification settings
    identification {

        # Email4Link configuration if visitor wants to receive a mail
        email4link {
            form {
                fields {
                    # Enable individual fields in email4link functionality (if you want to add your own fields in HTML template, you also have to enable it here)
                    enabled = email, newsletter, privacyChecked
                }
            }
            mail {
                # Enable/Disable
                _enable = 1

                # Template file for mail with asset attachment
                mailTemplate = EXT:lux/Resources/Private/Templates/Mail/Email4LinkMail.html

                # Mail subject
                subject = Your requested asset

                # From name for mail to visitor
                fromName = Website

                # From email for mail to visitor
                fromEmail = marketing@website.org

                # Send a blind copy to any receivers (can be a commaseparated email list)
                bccEmail =

                # Allow only files with this extensions
                allowedFileExtensions = pdf,txt,doc,docx,xls,xlsx,ppt,pptx,jpg,png,zip

                # Allow only sending of files within this storages (commaseparated uid list)
                allowedStorages = 1
            }
        }
    }
}
```

**Note:** Take care that your lib.lux configuration is recognized by lux (see FAQ section how to copy it to plugin.tx_lux_fe)

After that, you can change every link in the CK editor:
<img src="../../../Documentation/Images/screenshot_email4link_ckeditor_plugin.png" width="800" />

| Field                | Description                                                                                            |
|----------------------|--------------------------------------------------------------------------------------------------------|
| Title                | Show a title in the lightbox that opens on click in frontend                                           |
| Description          | Show a description in the lightbox that opens on click in frontend (optional)                          |
| Send asset via email | Try to send asset as attachment via email (only possible for allowed file extensions - see TypoScript) |

All clicks in frontend will be catched now by JavaScript and opens a lightbox:
<img src="../../../Documentation/Images/screenshot_email4link.png" width="800" />

**Note:** If you want to also use the email4link feature in your HTML-Templates, you can simply add some data-attributes
to your links.

```
<a
    class="btn btn-primary"
    href="/fileadmin/whitepaper.pdf"
    data-lux-email4link-sendemail="true"
    data-lux-email4link-text="Please give us your email-address, we're going to send you the asset."
    data-lux-email4link-title="Receive document via email.">Download our whitepaper</a>
```

**Note:** Per default, Lux will not ask twice for the email. But you can configure this via TypoScript constants
`plugin.tx_lux.settings.disableEmail4DownloadForIdentifiedVisitors=1`


#### Frontend login of a frontend user

If a frontend user logs in into TYPO3, lux automatically creates a relation to this user. If the field
fe_users.email is filled, lux take over this field property and identification is resolved.


#### Luxletter

The extension [Extension luxletter](https://github.com/in2code-de/luxletter) is a email marketing extension that
allows you to send newsletters to your users.
If a user clicks on a link in this newsletter, lux identifies who has clicked the link.
See documentation of luxletter for more details to this extension.
