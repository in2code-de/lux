<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\Tools\Console\ConnectionNotFound;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class DhController
{

//    public function __construct(
//        private readonly ConnectionPool $connectionPool
//    )
//    {
//    }

    public function getDhAjax(ServerRequestInterface $request): ResponseInterface
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionForTable('tx_lux_domain_model_pagevisit');
        $sql = "SELECT
    SUM(CASE WHEN referrer RLIKE 'www.facebook.com|m.facebook.com|l.facebook.com|lm.facebook.com' THEN 1 ELSE 0 END) AS facebook,
    SUM(CASE WHEN referrer RLIKE 'instagram.com|www.instagram.com|m.instagram.com|l.instagram.com|lm.instagram.com|mobile.instagram.com|web.instagram.com' THEN 1 ELSE 0 END) AS instagram,
    SUM(CASE WHEN referrer RLIKE 'lnkd.in|www.linkedin.com|m.linkedin.com|l.linkedin.com|lm.linkedin.com' THEN 1 ELSE 0 END) AS linkedin,
    SUM(CASE WHEN referrer RLIKE 't.co|www.twitter.com|m.twitter.com|l.twitter.com|lm.twitter.com' THEN 1 ELSE 0 END) AS twitter,
    SUM(CASE WHEN referrer RLIKE 'xing.com' THEN 1 ELSE 0 END) AS xing,
    SUM(CASE WHEN referrer RLIKE 'www.youtube.com' THEN 1 ELSE 0 END) AS youtube,
    SUM(CASE WHEN referrer RLIKE 'vimeo.com' THEN 1 ELSE 0 END) AS vimeo
FROM tx_lux_domain_model_pagevisit
WHERE crdate>1663113600
  and crdate<1694706912;";
        $result = $connection->executeQuery($sql)->fetchNumeric();
        $html = '    <div class="panel panel-default flex">
    <div class="panel-heading">
        <h3 class="panel-title">
            Top Social Media Sources
        </h3>
    </div>
    <div class="panel-body">

                <canvas width="750"
                        height="400"
                        data-chart="doughnut"
                        data-chart-data="'. implode(',', $result) . '"
                        data-chart-labels="Facebook,Instagram,LinkedIn,Twitter,Xing,YouTube,Vimeo"></canvas>

    </div>
</div>
';
        $response = GeneralUtility::makeInstance(HtmlResponse::class, $html);
        return $response;
    }
}
