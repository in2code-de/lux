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
        $icon = $this->iconFactory->getIcon('extension-lux');
        return $icon->render();
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
        $content .= '<a href="https://www.in2code.de/produkte/lux-typo3-marketing-automation/?utm_campaign=LUX+Community+Version&utm_id=llcv&utm_source=typo3&utm_medium=browser&utm_content=go+enterprise" class="lux_poweredby" style="color:black !important; font-weight:bold; right:85px; position:absolute;" target="_blank" rel="noopener">';
        $content .= $icon->render();
        $content .= 'Go enterprise</a>';
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
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return $uriBuilder->uriFor('information', [], 'General', 'Lux', 'lux_LuxAnalysis');
    }

    public function __toString()
    {
        return $this->render();
    }

    public function isValid()
    {
        return true;
    }
}
