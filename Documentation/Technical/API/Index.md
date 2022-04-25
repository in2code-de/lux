<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### API and Interface from LUX (only in luxenterprise)

Since luxenterprise 19.0.0 we introduced a small interface with reading access.

#### Configuration

First of all you have to check the extension manager configuration of luxenterprise, to turn on the API and to add
an Api-Key and to define which IP-addresses are allowed to read from the API (optional)

| Title                   | Default value             | Description                                                                                                                        |
| ----------------------- | ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| api                     | 0                         | Enable or disable the API of LUX                                                                                                   |
| apiKey                  | -                         | You have to enter a random value that will be used then as API-KEY for authentication. Note: Minimum 128 characters are needed!    |
| apiKeyIpAllowList       | -                         | Define one or more IPs or ranges (optional) for allowing to read the API (e.g. 192.0.0.1,192.168.0.0/24,fc00::,2001:db8::567:89ab) |

**Note:** Take care to add the typenum `1650897821` to your siteconfiguration (see FAQ for more details). In our example `leadapi.json` will be recognized from TYPO3 routing (see CURL examples below).

#### Usage

##### Endpoints

At the beginning there is only one reading endpoint where you can search for any leads with a given propertyname and
propertyvalue.

CURL example:
```
curl -d 'tx_luxenterprise_api[arguments]={"endpoint":"findAllByProperty","filter":{"propertyName":"email","propertyValue":"in2code.de"},"limit":100}' -H 'Api-Key: abc...' --url https://www.in2code.de/leadapi.json
```

Example answer:
```
{"arguments":{"endpoint":"findAllByProperty","filter":{"exactMatch":false,"propertyName":"email","propertyValue":"in2code.de"},"limit":1,"orderings":{"uid":"DESC"},"defaultProperties":["categoryscorings","scoring","email","email","identified","visits","blacklisted","attributes"]},"data":[{"scoring":647,"categoryscorings":[[],[],[]],"email":"alex@in2code.de","identified":true,"visits":13,"attributes":[[],[]],"blacklisted":false}]}
```
