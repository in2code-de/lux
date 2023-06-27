![LUX](/Documentation/Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](/Documentation/Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

### Finisher

This part of the manual describes how you can add own finishers to lux.

A finisher is a technique to start some individual magic after lux has done anything like tracking a pagerequest or
identifying a lead. It's a bit like a workflow (part of EXT:luxenterprise) but it cannot be configured by an editor.
Finishers are configured by TypoScript and PHP.

An usual example for a finisher is if you want to send some lead-data to a third-party-software like a CRM
(salesforce, etc...).


#### TypoScript Configuration

You can extend lux with own finishers with just some lines of TypoScript:

```
lib.lux.settings {
    # Add finishers classes for your own actions (e.g. pass values to a third party tool like a crm)
    finisher {

        # Example: Send an email to me if a visitor gets identified
        100 {
            class = Vendor\Luxextension\Domain\Finisher\SendNotificationEmailFinisher
            configuration {
                receiver = my@email.org
                subject = New visitor just identified on domain.org
            }
        }
    }
}
```

**Note:** Take care that your lib.lux configuration is recognized by lux (see FAQ section how to copy it to plugin.tx_lux_fe)


A small PHP file will do the trick for you:

```
<?php
declare(strict_types=1);
namespace Vendor\Luxextension\Domain\Finisher;

use In2code\Lux\Domain\Finisher\AbstractFinisher;
use In2code\Lux\Domain\Finisher\FinisherInterface;

/**
 * Class SendNotificationEmailFinisher
 */
class SendNotificationEmailFinisher extends AbstractFinisher implements FinisherInterface
{
    /**
     * Decide if start() should be called or not by returning a boolean value
     *
     * @return bool
     */
    public function shouldFinisherRun(): bool
    {
        return $this->getVisitor()->isIdentified();
    }

    /**
     * Function start() is called if shouldFinisherRun() returns true.
     *
     * @return array Could handle instructions for the frontend (opening popups, etc...). Empty for backend logic only.
     */
    public function start(): array
    {
        mail(
            $this->getConfigurationByKey('receiver'),
            $this->getConfigurationByKey('subject'),
            'New visitor identified: ' . $this->getVisitor()->getEmail()
        );
        return [];
    }
}

```

Your PHP finisher class must implement FinisherInterface and should extend the AbstractFinisher. Last class offers you
useful methods like $this->getVisitor(), $this->getConfigurationByKey(), $this->getConfigurationByPath()
or $this->getEvent() can help you to get useful information.

Your class must have a start() function where your logic is implemented. The method shouldFinisherRun() is optional
and if you don't add this to your finisher, it will return true by default.

Because there are some actions that are called in lux, you can decide which of this action should start your finisher
by setting the class property $startWithControllerActions (look at AbstractFinisher.php for some examples).

Now, feel free to extend Lux for your needs.
