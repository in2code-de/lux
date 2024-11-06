<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Provider;

/**
 * Class CustomerMail
 * this class can decide if an email belongs to a b2c or b2b contact
 */
class CustomerMail
{
    protected array $b2cEmailDomains = [
        '163.com',
        '126.com',
        '139.com',
        '188.com',
        '189.cn',
        '1und1.de',
        'aol.de',
        'aol.com',
        'aon.at',
        'arcor.de',
        'barmer.de',
        'bluemail.ch',
        'bluewin.ch',
        'chello.at',
        'dak.de',
        'dxy.cn',
        'eclipso.at',
        'eclipso.com',
        'eclipso.de',
        'email.de',
        'ewe.net',
        'foxmail.com',
        'free.fr',
        'freenet.de',
        'gmail.com',
        'gmx.at',
        'gmx.ch',
        'gmx.com',
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
        'mail.ch',
        'mail.com',
        'mail.de',
        'mail.ru',
        'me.com',
        'mozmail.com',
        'msn.com',
        'o2online.de',
        'online.de',
        'orange.fr',
        'osnanet.de',
        'ostfalia.de',
        'outlook.com',
        'outlook.de',
        'posteo.de',
        'qq.com',
        'sunrise.ch',
        't-online.de',
        'telekom.de',
        'tk.de',
        'versanet.de',
        'vodafone.de',
        'vodafonemail.de',
        'wanadoo.fr',
        'web.de',
        'yeah.net',
        'yahoo.com',
        'yahoo.de',
        'yandex.ru',
    ];

    public function __construct(protected readonly AllowedMail $allowedMail)
    {
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
