<?php
declare(strict_types=1);
namespace In2code\Lux\Hooks;

use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\UrlUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class LuxLinkClickLinkhandler
 */
class LuxLinkClickLinkhandler
{
    /**
     * @param array $parameters
     * @return void
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function postProcessTypoLink(array &$parameters): void
    {
        if ($this->isLinkclickLink($parameters)) {
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
     */
    protected function getTargetUri(array $parameters): string
    {
        $linkclickIdentifier = $this->getLinkclickIdentifier($parameters);
        $configuration = [
            'parameter' => $this->getLinkTargetFromLinkclickIdentifier($linkclickIdentifier)
        ];
        return ObjectUtility::getContentObject()->typoLink_URL($configuration);
    }

    /**
     * @param int $linkclickIdentifier
     * @return string
     */
    protected function getLinkTargetFromLinkclickIdentifier(int $linkclickIdentifier): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Linkclick::TABLE_NAME);
        return (string)$queryBuilder
            ->select('link')
            ->from(Linkclick::TABLE_NAME)
            ->where('uid=' . (int)$linkclickIdentifier)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param array $parameters
     * @return int
     */
    protected function getLinkclickIdentifier(array $parameters): int
    {
        return (int)UrlUtility::getAttributeValueFromString(
            $parameters['finalTagParts']['aTagParams'],
            'data-lux-linkclick'
        );
    }

    /**
     * @param $parameters
     * @return bool
     */
    protected function isLinkclickLink(&$parameters): bool
    {
        return stristr($parameters['finalTagParts']['aTagParams'], 'data-lux-linkclick') !== false;
    }
}
