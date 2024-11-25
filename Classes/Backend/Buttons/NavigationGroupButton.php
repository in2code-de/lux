<?php

declare(strict_types=1);

namespace In2code\Lux\Backend\Buttons;

use TYPO3\CMS\Backend\Template\Components\Buttons\AbstractButton;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class NavigationGroupButton extends AbstractButton
{
    protected array $configuration = [];

    protected string $currentAction;
    protected string $currentController;

    protected Request $request;
    protected UriBuilder $uriBuilder;
    protected IconFactory $iconFactory;

    public function __construct(
        Request $request,
        string $currentAction,
        string $currentController,
        array $configuration
    ) {
        $this->request = $request;
        $this->currentAction = $currentAction;
        $this->currentController = $currentController;
        $this->configuration = $configuration;
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->uriBuilder->setRequest($this->request);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    public function render()
    {
        $content = $this->prepend();
        $content .= '<div class="lux btn-group" role="group">';
        foreach ($this->configuration as $buttonConfiguration) {
            $url = $this->uriBuilder->uriFor(
                $buttonConfiguration['action'],
                null,
                $buttonConfiguration['controller'] ?? null
            );
            $class = 'btn-default';
            if ($this->currentAction === $buttonConfiguration['action']) {
                if (($buttonConfiguration['controller'] ?? null) === null
                    || $this->currentController === $buttonConfiguration['controller']) {
                    $class = 'btn-primary';
                }
            }
            $content .= '<a href="' . $url . '" class="btn ' . $class . '">' . $buttonConfiguration['label'] . '</a>';
        }
        $content .= '</div>';
        $content = $this->append($content);
        return $content;
    }

    protected function prepend(): string
    {
        return '
            <span class="t3js-icon icon icon-size-medium icon-state-default icon-extension-lux" data-identifier="extension-lux" aria-hidden="true">
                <span class="icon-markup">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 70.9 70.9" style="enable-background:new 0 0 70.9 70.9;" xml:space="preserve">
                        <path d="M27.4,2.7c0,0-2.4,6.7,0.9,9.3s4.8,7.1,4.3,9.1c0,0,12.7-0.5,16.4,7.7c0,0-0.2,1.3,7.7,4.7c0,0,2.7,0.9-2.4,6.9 c0,0,0.8,1.7-2.8,2.9c0,0,0.8,4.4-3.9,3.6c-4.7-0.8-11.1-3.8-6.9,3.3c0,0,7.9,7.8,6.4,14.6c0,0-4.9-12.4-20.5-14.1 c0,0,14,3.2,19.1,17.4c0,0-2.8-5.7-15.2-9.1c0,0,3.6,1.3,4.9,5.4c0,0-1-1.9-12.4-5.2s-9.6-14.8-9-17.1c0.9-3.5,3.5-12.4,8.9-17.2 c0,0-3.1-2.7,2.1-13.5C24.9,11.5,24.1,6.7,27.4,2.7z" fill="currentColor"></path>
                    </svg>
                </span>
            </span>
        ';
    }

    protected function append(string $content): string
    {
        if (ExtensionManagementUtility::isLoaded('luxenterprise')) {
            return $this->appendEnterprise($content);
        }
        return $this->appendCommunity($content);
    }

    protected function appendCommunity(string $content): string
    {
        $icon = $this->iconFactory->getIcon('extension-lux-star', Icon::SIZE_SMALL);
        $content .= '<div style="padding-top: 5px;">';
        $content .= 'LUX Community Edition';
        $content .= '<a href="' . $this->getInfoUri() . '" style="margin-left: 5px;">';
        $content .= $this->iconFactory->getIcon('actions-info-circle-alt', Icon::SIZE_SMALL);
        $content .= '</a></div>';
        $content .= '<a href="https://www.in2code.de/produkte/lux-typo3-marketing-automation/?utm_campaign=LUX+Community+Version&utm_id=llcv&utm_source=typo3&utm_medium=browser&utm_content=go+enterprise" class="lux_poweredby" style="color:currentColor !important; font-weight:bold; right:85px; position:absolute;" target="_blank" rel="noopener">';
        $content .= $icon->render();
        $content .= ' Go enterprise</a>';
        return $content;
    }

    protected function appendEnterprise(string $content): string
    {
        $content .= '<div style="padding: 5px 0 0 5px;">';
        $content .= 'LUX Enterprise Edition';
        $content .= '<a href="' . $this->getInfoUri() . '" style="margin-left: 5px;">';
        $content .= $this->iconFactory->getIcon('actions-info-circle-alt', Icon::SIZE_SMALL);
        $content .= '</a></div>';
        return $content;
    }

    protected function getInfoUri(): string
    {
        return $this->uriBuilder->uriFor('information', null, 'General');
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function isValid()
    {
        return true;
    }
}
