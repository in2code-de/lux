<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Visitor;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetGravatarImageViewHelper
 */
class GetGravatarImageViewHelper extends AbstractViewHelper
{

    /**
     * @var string
     */
    protected $defaultFile = 'typo3conf/ext/lux/Resources/Public/Images/AvatarDefault.svg?a=b';

    /**
     * Size in px
     *
     * @var int
     */
    protected $size = 150;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('visitor', Visitor::class, 'visitor', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $url = $this->getDefaultUrl();
        /** @var Visitor $visitor */
        $visitor = $this->arguments['visitor'];
        if ($visitor->isIdentified()) {
            $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($visitor->getEmail())))
                . '?d=' . urlencode($url) . '&s=' . $this->size;
            $header = GeneralUtility::getUrl($gravatarUrl, 2);
            if (!empty($header)) {
                $url = $gravatarUrl;
            }
        }
        return $url;
    }

    /**
     * @return string
     */
    protected function getDefaultUrl(): string
    {
        return StringUtility::getCurrentUri() . $this->defaultFile;
    }
}
