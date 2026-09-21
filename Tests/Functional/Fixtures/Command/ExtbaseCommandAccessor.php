<?php

declare(strict_types=1);
namespace In2code\Lux\Tests\Functional\Fixtures\Command;

use In2code\Lux\Command\ExtbaseCommandTrait;
use Symfony\Component\Console\Command\Command;

class ExtbaseCommandAccessor extends Command
{
    use ExtbaseCommandTrait;
}
