![LUX](../../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

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

Available endpoints are:

### [FindByProperty to get a single lead](FindByProperty.md)
### [FindAllByAnyProperties to get a list of leads](FindAllByAnyProperties.md)
### [Create to create or update a lead](Create.md)
