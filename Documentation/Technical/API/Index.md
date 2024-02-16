![LUX](/Documentation/Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](/Documentation/Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

# API and Interface from LUX (only in luxenterprise)

Since LUXenterprise 19.0.0 we introduced a proper interface with reading access as you may know from other REST APIs.

## Configuration

First of all you have to check the extension manager configuration of LUXenterprise, to turn on the API and to add
an Api-Key and to define which IP-addresses are allowed to read from the API (optional)

| Title             | Default value | Description                                                                                                                        |
|-------------------|---------------|------------------------------------------------------------------------------------------------------------------------------------|
| api               | 0             | Enable or disable the API of LUX                                                                                                   |
| apiKey            | -             | You have to enter a random value that will be used then as API-KEY for authentication. Note: Minimum 128 characters are needed!    |
| apiKeyIpAllowList | -             | Define one or more IPs or ranges (optional) for allowing to read the API (e.g. 192.0.0.1,192.168.0.0/24,fc00::,2001:db8::567:89ab) |

**Note:** Take care to add the typenum `1650897821` to your siteconfiguration (see FAQ for more details). In our example `luxenterprise_api.json` will be recognized from TYPO3 routing (see CURL examples below).

## Endpoints

The API works as most interfaces by selecting an endpoint and passing arguments as JSON. The result is also always a
JSON output.

### 1. Endpoint "findAllByAnyProperties" for getting a list of visitors (reading access)

The endpoint `findAllByAnyProperties` can be used to search for all leads by given search terms.
You can pass multiple arguments (also in related tables), limit and orderings.

#### Default arguments

These arguments are used by default if not overwritten in your request:

```
'endpoint' => 'findAllByAnyProperties',
'properties' => [
    [
        'name' => 'uid',
        'value' => 0,
        'operator' => 'greaterThan'
    ]
],
'limit' => 100,
'depth' => 3,
'orderings' => [
    'uid' => 'DESC'
],
'defaultProperties' => [
    'uid',
    'scoring',
    'email',
    'email',
    'identified',
    'visits',
    'blacklisted',
    'attributes',
]
```

#### Example usage

In the example below, a search is triggered `where tx_lux_domain_model_visitor.email like %in2code.de` with these
arguments:

```
{
  "endpoint": "findAllByAnyProperties",
  "properties": {
    "0": {
      "name": "email",
      "value": "%in2code.de",
      "operator": "like"
    }
  },
  "limit": 2,
  "depth": 2
}
```

CURL example:

```
curl -d 'tx_luxenterprise_api[arguments]={"endpoint":"findAllByAnyProperties","properties":{"0":{"name":"email","value":"%in2code.de","operator":"like"}},"limit":2,"depth":2}' -H 'Api-Key: abc...' --url https://www.in2code.de/luxenterprise_api.json
```

Example result:

```
{
  "arguments": {
    "endpoint": "findAllByAnyProperties",
    "properties": [
      {
        "name": "email",
        "value": "%in2code.de",
        "operator": "like"
      }
    ],
    "limit": 2,
    "depth": 2,
    "orderings": {
      "uid": "DESC"
    },
    "defaultProperties": [
      "uid",
      "scoring",
      "email",
      "email",
      "identified",
      "visits",
      "blacklisted",
      "attributes"
    ]
  },
  "data": [
    {
      "scoring": 647,
      "email": "alex@in2code.de",
      "identified": true,
      "visits": 13,
      "attributes": [
        [],
        []
      ],
      "blacklisted": false,
      "uid": 18855
    },
    {
      "scoring": 393,
      "email": "alexander.kellner@in2code.de",
      "identified": true,
      "visits": 10,
      "attributes": [
        [],
        []
      ],
      "blacklisted": false,
      "uid": 18802
    }
  ]
}
```

You can also search in related tables: `where tx_lux_domain_model_attribute.name = "email" and tx_lux_domain_model_attribute.value = "%in2code.de"`
with these arguments:

```
{
  "endpoint": "findAllByAnyProperties",
  "properties": {
    "0": {
      "name": "attributes.name",
      "value": "email",
      "operator": "equals"
    },
    "1": {
      "name": "attributes.value",
      "value": "%in2code.de",
      "operator": "like"
    }
  },
  "limit": 200,
  "depth": 2,
  "orderings": {
    "uid": "ASC"
  }
}
```

**Note:** The attribute `email` es stored directly in visitor table but also in attribute table. A more useful query would be to search for property `newsletter` or `lastname`, etc...


Another example to search for property `newsletter=1` within active users of the latest 7 days (in this documentation
let's assume the unix timestamp `1650386396` is 7 days ago):

```
{
  "endpoint": "findAllByAnyProperties",
  "properties": {
    "0": {
      "name": "attributes.name",
      "value": "newsletter",
      "operator": "equals"
    },
    "1": {
      "name": "attributes.value",
      "value": "1",
      "operator": "equals"
    },
    "2": {
      "name": "pagevisits.crdate",
      "value": "1650386396",
      "operator": "greaterThan"
    }
  },
  "limit": 200,
  "depth": 2,
  "orderings": {
    "pagevisits.crdate": "DESC"
  }
}
```


### 2. Endpoint "findByProperty" for getting a single visitor (reading access)

The endpoint `findByProperty` can be used to search for a single lead.

#### Default arguments

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

#### Example usage

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



### 3. Endpoint "create" to write new leads into database (writing access)

*Note:* This endpoint was introduced with LUXenterprise 38.0.0 and is not available before this version

The endpoint `create` can be used to add new visitor objects to LUX.

*Note:* If you pass an email address and there is already a visitor with same mail existing, both visitor objects are
going to be merged to keep history


#### Example usage

A new lead with two attributes is created from API with one pagevisit and with a fingerprint record can be added via
API with these arguments:

```
{
  "endpoint": "create",
  "properties": {
    "visitor": {
      "email": "new@email.org",
      "ipAddress": "127.0.0.1",
      "identified": "1",
      "scoring": "10",
      "visits": "1",
      "attributes": {
        "0": {
          "name": "firstname",
          "value": "Alex"
        },
        "1": {
          "name": "lastname",
          "value": "Kellner"
        }
      },
      "pagevisits": {
        "0": {
          "page": "12",
          "language": "0",
          "referrer": "https://lastdomain.org/page"
        }
      },
      "fingerprints": {
        "0": {
          "value": "abcdef123456789foobar",
          "domain": "https://mydomain.org",
          "userAgent": "Mozilla/5.0"
        }
      }
    }
  }
}
```

CURL example:

```
curl -k -d 'tx_luxenterprise_api[arguments]={"endpoint":"create","properties":{"visitor":{"email":"new@email.org","ipAddress":"127.0.0.1","identified":"1","scoring":"10","visits":"1",attributes":{"0":{"name":"firstname","value":"Alex"},"1":{"name":"lastname","value":"Kellner"}},"pagevisits":{"0":{"page":"12","language":"0","referrer":"https://lastdomain.org/page"}},"fingerprints":{"0":{"value":"abcdef123456789foobar","domain":"https://mydomain.org","userAgent":"Mozilla/5.0"}}}}}' -H 'Api-Key: abc...' --url https://www.in2code.de/luxenterprise_api.json
```
