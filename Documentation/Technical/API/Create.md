![LUX](/Documentation/Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](/Documentation/Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

# Endpoint "create" to write new or update existing leads into database (writing access)

*Note:* This endpoint was introduced with LUXenterprise 38.0.0 and is not available before this version

The endpoint `create` can be used to add new visitor objects to LUX.

*Note:* If you pass an email address and there is already a visitor with same mail existing, both visitor objects are
going to be merged to keep history by default

## 1. Usage

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
          "domain": "mydomain.org",
          "userAgent": "Mozilla/5.0"
        }
      }
    }
  }
}
```

CURL example:

```
curl -k -d 'tx_luxenterprise_api[arguments]={"endpoint":"create","properties":{"visitor":{"email":"new@email.org","ipAddress":"127.0.0.1","identified":"1","scoring":"10","visits":"1",attributes":{"0":{"name":"firstname","value":"Alex"},"1":{"name":"lastname","value":"Kellner"}},"pagevisits":{"0":{"page":"12","language":"0","referrer":"https://lastdomain.org/page"}},"fingerprints":{"0":{"value":"abcdef123456789foobar","domain":"mydomain.org","userAgent":"Mozilla/5.0"}}}}}' -H 'Api-Key: abc...' --url https://www.in2code.de/luxenterprise_api.json
```


## 2. Merge by Fingerprint

If you want to update an existing lead by its fingerprint, this example will help you out. In the following case we
want to add the attribute "newsletter" with value 1 for a possible newsletter registration.

```
{
  "endpoint": "create",
  "merge": {
    "mergeByEmail": 0,
    "mergeByFingerprint": 1
  },
  "properties": {
    "visitor": {
      "attributes": {
        "0": {
          "name": "newsletter",
          "value": "1"
        }
      },
      "fingerprints": {
        "0": {
          "value": "abcdef123456789foobar"
        }
      }
    }
  }
}
```

CURL example:

```
curl -k -d 'tx_luxenterprise_api[arguments]={"endpoint":"create","merge":{"mergeByEmail":0,"mergeByFingerprint":1},"properties":{"visitor":{"attributes":{"0":{"name":"newsletter","value":"1"}},"fingerprints":{"0":{"value":"abcdef123456789foobar"}}}}}' -H 'Api-Key: abc...' --url https://www.in2code.de/luxenterprise_api.json
```

**Note:** If you want to get the fingerprint value of the current visitor via JavaScript, following example could help:

```
const lux = LuxSingleton.getInstance();
if (lux.getIdentification().isIdentificatorSet()) {
    const identificator = lux.getIdentification().getIdentificator();
    console.log(identificator);
}
```
