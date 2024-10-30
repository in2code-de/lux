<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Provider;

/**
 * Class CustomerMail
 * this class can decide if an email belongs to a b2c or b2b contact
 */
class CustomerMail
{
    protected AllowedMail $allowedMail;

    protected array $b2cEmailDomains = [
        '1und1.de',
        'aok.de',
        'aon.at',
        'arcor.de',
        'barmer.de',
        'bluemail.ch',
        'bluewin.ch',
        'chello.at',
        'dak.de',
        'email.de',
        'ewe.net',
        'freenet.de',
        'gmail.com',
        'gmx.at',
        'gmx.ch',
        'gmx.de',
        'gmx.net',
        'googlemail.com',
        'hotmail.com',
        'hotmail.de',
        'htp-tel.de',
        'icloud.com',
        'ikk-classic.de',
        'kabelbw.de',
        'live.com',
        'mac.com',
        'mail.de',
        'me.com',
        'mozmail.com',
        'o2online.de',
        'online.de',
        'osnanet.de',
        'ostfalia.de',
        'outlook.com',
        'outlook.de',
        'posteo.de',
        't-online.de',
        'telekom.de',
        'tk.de',
        'versanet.de',
        'vodafone.de',
        'vodafonemail.de',
        'web.de',
        'yahoo.com',
        'yahoo.de',
    ];

    public function __construct(AllowedMail $allowedMail)
    {
        $this->allowedMail = $allowedMail;
    }

    public function isB2cEmail(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return in_array($domain, $this->b2cEmailDomains) || $this->allowedMail->isEmailAllowed($email) === false;
    }

    public function isB2bEmail(string $email): bool
    {
        return $this->isB2cEmail($email) === false;
    }
}
