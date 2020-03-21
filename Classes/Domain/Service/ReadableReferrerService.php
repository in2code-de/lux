<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

/**
 * Class ReadableReferrerService
 */
class ReadableReferrerService
{

    /**
     * @var string
     */
    protected $referrer = '';

    /**
     * Array with mapping for domains (original => Readable)
     *
     * @var array
     */
    protected $sources = [
        't.co' => 'Twitter',
        'www.google.com' => 'Google International',
        'www.google.de' => 'Google Germany'
    ];

    /**
     * ReadableReferrerService constructor.
     *
     * @param string $referrer
     */
    public function __construct(string $referrer = '')
    {
        $this->referrer = $referrer;
    }

    /**
     * @return string
     */
    public function getReadableReferrer(): string
    {
        $domain = $this->getDomain();
        if (array_key_exists($domain, $this->sources)) {
            $domain = $this->sources[$domain];
        }
        return $domain;
    }

    /**
     * @return string
     */
    public function getOriginalReferrer(): string
    {
        return $this->referrer;
    }

    /**
     * @return string
     */
    protected function getDomain(): string
    {
        $domain = '';
        $parts = parse_url($this->referrer);
        if (array_key_exists('host', $parts)) {
            $domain = $parts['host'];
        }
        return $domain;
    }
}
