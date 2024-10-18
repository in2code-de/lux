<?php

declare(strict_types=1);

namespace In2code\Lux\Update\AddSites;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Utility\DatabaseUtility;

class AddSitesForPagevisits extends AbstractSiteUpgrade
{
    protected const TABLE_NAME = Pagevisit::TABLE_NAME;
}
