<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

## IP addresses

LUX recognizes visitor IP addreses and can use it for a transformation to a company by using a IP service API.
In addition IP-addresses can be stored complete or anynomized.
This small documentation shows you all configuration options related to IP-addresses in LUX.

### Extension manager configuration

* Disable IP logging: You can turn off the basic storage of any IP address in your database here
* Anonymize IP: Parts of the IP address are replaced with "***" before saving this value to your database

<img src="../Images/documentation_installation_extensionmanager4.png" width="800" />

### IP address services

Via TypoScript you can configure if and which service interfaces are connected to convert a visitor IP address to a
real company name.

Disabling this can be done via:

```
lib.lux.settings.ipinformation._enable = 1
```

Configure which service should be used:

```
lib.lux.settings {
  # Layer to use own ip information service (convert IP address to visitor name)
  # More then one service can be registered. The first that deliver a result, will be used.
  ipinformation {
    # Main switch for using external IP services
    _enable = 1

    # Ip-API.com
    # detailed information (company information mostly better then other services)
    # with company details, geo coordinates, zip and region
    # but connection is limited to 45 requests/minute + to http only for free usage (see ip-api.com for premium access)
    1 {
      class = In2code\Lux\Domain\Factory\Ipinformation\IpApi
      configuration {
        url = http://ip-api.com/json/{ip}
      }
    }

    # iplist.cc
    # as a complete free provider service with https connection but information depth is worse then others
    # no company details, no geo coordinates, no zip and region
    2 {
      class = In2code\Lux\Domain\Factory\Ipinformation\Iplist
      configuration {
        url = https://iplist.cc/api/{ip}
      }
    }
  }
}
```

Disabling Ip-API.com but keep iplist.cc could be done via:

```
lib.lux.settings.ipinformation.1 >
```

**Note:** Take care that your lib.lux configuration is recognized by LUX (see FAQ section how to copy it to plugin.tx_lux_fe)
