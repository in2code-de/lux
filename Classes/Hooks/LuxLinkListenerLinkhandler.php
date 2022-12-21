<?php

declare(strict_types=1);
namespace In2code\Lux\Hooks;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\UrlUtility;

class LuxLinkListenerLinkhandler
{
    /**
     * @param array $parameters
     * @return void
     * @throws Exception
     * @throws ExceptionDbal
     * @noinspection PhpUnused
     */
    public function postProcessTypoLink(array &$parameters): void
    {
        if ($this->isLinkListenerLink($parameters)) {
            $url = $this->getTargetUri($parameters);
            $parameters['finalTag'] = preg_replace(
                '~(<a.+href=")[^"]+("[^>]+>)~U',
                '${1}' . $url . '${2}',
                $parameters['finalTag']
            );
        }
    }

    /**
     * @param array $parameters
     * @return string
     * @throws Exception
     * @throws ExceptionDbal
     */
    protected function getTargetUri(array $parameters): string
    {
        $linkListenerUid = $this->getLinkListenerIdentifier($parameters);
        $configuration = [
            'parameter' => $this->getLinkTargetFromLinkListenerIdentifier($linkListenerUid),
        ];
        return ObjectUtility::getContentObject()->typoLink_URL($configuration);
    }

    /**
     * @param int $linkListenerUid
     * @return string
     * @throws Exception
     * @throws ExceptionDbal
     */
    protected function getLinkTargetFromLinkListenerIdentifier(int $linkListenerUid): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linklistener::TABLE_NAME);
        return (string)$queryBuilder
            ->select('link')
            ->from(Linklistener::TABLE_NAME)
            ->where('uid=' . (int)$linkListenerUid)
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @param array $parameters
     * @return int
     */
    protected function getLinkListenerIdentifier(array $parameters): int
    {
        return (int)UrlUtility::getAttributeValueFromString(
            $parameters['finalTagParts']['aTagParams'],
            'data-lux-linklistener'
        );
    }

    /**
     * @param $parameters
     * @return bool
     */
    protected function isLinkListenerLink(&$parameters): bool
    {
        return stristr($parameters['finalTagParts']['aTagParams'], 'data-lux-linklistener') !== false;
    }
}
