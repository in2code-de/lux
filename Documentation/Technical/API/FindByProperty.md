![LUX](/Documentation/Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](/Documentation/Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

# Endpoint "findByProperty" for getting a single visitor (reading access)

The endpoint `findByProperty` can be used to search for a single lead.

## Default arguments

These arguments are used by default if not overwritten in your request:

```
'endpoint' => 'findByProperty',
'propertyName' => 'uid',
'depth' => 3,
'defaultProperties' => [
    'uid',
    'scoring',
    'email',
    'email',
    'identified',
    'visits',
    'blacklisted',
    'attributes',
    'pagevisits',
    'newsvisits',
    'linkclicks',
    'categoryscorings',
    'downloads',
]
```

## Example usage

In the example below, a search is triggered `where tx_lux_domain_model_visitor.uid = 123` with these
arguments:

```
{
  "endpoint": "findByProperty",
  "propertyValue": "123"
}
```

CURL example:

```
curl -d 'tx_luxenterprise_api[arguments]={"endpoint":"findByProperty","propertyValue":"123"}' -H 'Api-Key: abc...' --url https://www.in2code.de/luxenterprise_api.json
```

Example answer:

```
{
  "arguments": {
    "endpoint": "findByProperty",
    "propertyName": "email",
    "depth": 3,
    "defaultProperties": [
      "uid",
      "scoring",
      "email",
      "email",
      "identified",
      "visits",
      "blacklisted",
      "attributes",
      "pagevisits",
      "newsvisits",
      "linkclicks",
      "categoryscorings",
      "downloads"
    ],
    "propertyValue": "sandra.pohl@in2code.de"
  },
  "data": {
    "scoring": 102,
    "email": "sandra.pohl@in2code.de",
    "identified": true,
    "visits": 5,
    "pagevisits": [
      {
        "page": null,
        "language": 0,
        "crdate": "2019-07-25T12:46:52+02:00",
        "referrer": "",
        "domain": "",
        "uid": 49433,
        "pid": 0
      },
    ],
    "attributes": [
      {
        "name": "email",
        "value": "sandra.pohl@in2code.de",
        "uid": 543,
        "pid": 0
      }
    ],
    "downloads": [
      {
        "crdate": "2019-07-25T12:47:01+02:00",
        "href": "/fileadmin/content/downloads/whitepaper/DisasterRecovery.pdf",
        "page": null,
        "file": null,
        "domain": "",
        "uid": 549,
        "pid": 0
      },
      {
        "crdate": "2019-07-25T18:30:08+02:00",
        "href": "/fileadmin/content/downloads/whitepaper/IhrePerfekteInfrastruktur.pdf",
        "page": null,
        "file": null,
        "domain": "",
        "uid": 554,
        "pid": 0
      },
    ],
    "blacklisted": false,
    "uid": 13890
  }
}
```

You can also change the property field. E.g. if you want to search for an email:

```
{
  "endpoint": "findByProperty",
  "propertyName": "email",
  "propertyValue": "sandra.pohl@in2code.de"
}
```
