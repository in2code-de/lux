<?php

declare(strict_types=1);

namespace In2code\Lux\Update\AddSites;

use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Utility\DatabaseUtility;

class AddSitesForLinkclick extends AbstractSiteUpgrade
{
    protected const TABLE_NAME = Linkclick::TABLE_NAME;
}
