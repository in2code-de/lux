<?php

declare(strict_types=1);

use In2code\Lux\Widgets\DataProvider\LuxBrowserDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxDownloadsDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxDownloadsWeekDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxHottestLeadsDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxIdentifiedDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxIdentifiedPerMonthDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxNewsDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxPageVisitsDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxPageVisitsWeekDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxRecurringDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxReferrerDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxSearchtermsDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxUtmCampaignDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxUtmMediaDataProvider;
use In2code\Lux\Widgets\DataProvider\LuxUtmSourceDataProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Dashboard\Dashboard;
use TYPO3\CMS\Dashboard\Widgets\BarChartWidget;
use TYPO3\CMS\Dashboard\Widgets\DoughnutChartWidget;
use TYPO3\CMS\Dashboard\Widgets\ListWidget;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    $llPrefix = 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:';
    $services = $configurator->services();

    if ($containerBuilder->hasDefinition(Dashboard::class)) {
        $services->set('dashboard.widgets.luxPageVisitsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxPageVisits',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxpagevisits.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxpagevisits.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxPageVisitsDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxDownloadsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxDownloads',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxdownloads.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxdownloads.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxDownloadsDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxNewsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxNews',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxnews.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxnews.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxNewsDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxSearchtermsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxSearchterms',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxsearchterms.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxsearchterms.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxSearchtermsDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxIdentifiedWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'LuxIdentified',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxidentified.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxidentified.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxIdentifiedDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxIdentifiedPerMonthWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'LuxIdentifiedPerMonth',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxidentifiedpermonth.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxidentifiedpermonth.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxIdentifiedPerMonthDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxRecurringWidget')
            ->class(DoughnutChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxRecurring',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxrecurring.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxrecurring.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxRecurringDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxPageVisitsWeekWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxPageVisitsWeek',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxpagevisitsweek.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxpagevisitsweek.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxPageVisitsWeekDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxDownloadsWeekWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxDownloadsWeek',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxdownloadsweek.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxdownloadsweek.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxDownloadsWeekDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxReferrerWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxReferrer',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.referrer.title',
                'description' => $llPrefix . 'module.dashboard.widget.referrer.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxReferrerDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxBrowserWidget')
            ->class(DoughnutChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxBrowser',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.browser.title',
                'description' => $llPrefix . 'module.dashboard.widget.browser.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxBrowserDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxHottestLeadsWidget')
            ->class(ListWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxHottestLeads',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.hottestleads.title',
                'description' => $llPrefix . 'module.dashboard.widget.hottestleads.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxHottestLeadsDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.luxUtmCampaignWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxUtmCampaign',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxutmcampaign.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxutmcampaign.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxUtmCampaignDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxUtmSourceWidget')
            ->class(DoughnutChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxUtmSource',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxutmsource.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxutmsource.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxUtmSourceDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));

        $services->set('dashboard.widgets.LuxUtmMediaWidget')
            ->class(DoughnutChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxUtmMedia',
                'groupNames' => 'luxgroup',
                'title' => $llPrefix . 'module.dashboard.widget.luxutmmedia.title',
                'description' => $llPrefix . 'module.dashboard.widget.luxutmmedia.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxUtmMediaDataProvider::class))
            ->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
    }
};
