![LUX](../../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

# Endpoint "findAllByAnyProperties" for getting a list of visitors (reading access)

The endpoint `findAllByAnyProperties` can be used to search for all leads by given search terms.
You can pass multiple arguments (also in related tables), limit and orderings.

## Default arguments

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

## Example usage

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
