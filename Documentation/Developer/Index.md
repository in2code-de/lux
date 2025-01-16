![LUX](../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

## How to develop with LUX

### LUX

#### Local test environment

LUX ships its own testing environment based on docker. This can simply be started in the same folder as LUX with
`make install-project` for the first time and then
`make stop` to stop the container and
`make start` to start it again

After that Backend is reachable under https://local.lux.de/typo3/

Login is possible with `akellner` as username and password.

*Note* Doing a `make` on CLI lists a bunch of useful commands (like clear caches, etc...)
*Note* Docker and Dinghy should be installed on your unix system first

#### Run tests

There are different tests that can be started locally. First of all, you have to login into the PHP-container with
`make login-php`

After that, you can run different tests:

| Test                    | Command                  |
|-------------------------|--------------------------|
| Start code sniffer test | `composer test:php:cs`   |
| Start PHP linter        | `composer test:php:lint` |
| Start TypoScript linter | `composer test:ts:lint`  |
| Start unit tests        | `composer test:unit`     |

### LUXenterprise

If you clone LUXenterprise, you have basically the same possibilities as in LUX, but LUXenterprise will also install LUX
of course.

#### Local test environment

LUX ships its own testing environment based on docker. This can simply be started in the same folder as LUX with
`make install-project` for the first time and then
`make stop` to stop the container and
`make start` to start it again

After that Backend is reachable under https://local.luxenterprise.de/typo3/

Login is possible with `akellner` as username and password.

*Note* Doing a `make` on CLI lists a bunch of useful commands (like clear caches, etc...)
*Note* Docker and Dinghy should be installed on your unix system first

#### Run tests

There are different tests that can be started locally. First of all, you have to login into the PHP-container with
`make login-php`

After that, you can run different tests:

| Test                    | Command                  |
|-------------------------|--------------------------|
| Start code sniffer test | `composer test:php:cs`   |
| Start PHP linter        | `composer test:php:lint` |
| Start TypoScript linter | `composer test:ts:lint`  |
| Start unit tests        | `composer test:unit`     |
