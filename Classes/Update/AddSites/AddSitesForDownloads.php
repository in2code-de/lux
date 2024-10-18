<?php

declare(strict_types=1);

namespace In2code\Lux\Update\AddSites;

use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Utility\DatabaseUtility;

class AddSitesForDownloads extends AbstractSiteUpgrade
{
    protected const TABLE_NAME = Download::TABLE_NAME;
}
