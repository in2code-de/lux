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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Dashboard\Dashboard;
use TYPO3\CMS\Dashboard\Widgets\BarChartWidget;
use TYPO3\CMS\Dashboard\Widgets\DoughnutChartWidget;
use TYPO3\CMS\Dashboard\Widgets\ListWidget;

return function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder) {
    $services = $configurator->services();

    if ($containerBuilder->hasDefinition(Dashboard::class)) {
        $configuration = $services->set('dashboard.widgets.luxPageVisitsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxPageVisits',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisits.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisits.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxPageVisitsDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxDownloadsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxDownloads',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloads.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloads.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxDownloadsDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxNewsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxNews',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxnews.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxnews.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxNewsDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxSearchtermsWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxSearchterms',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxsearchterms.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxsearchterms.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxSearchtermsDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxIdentifiedWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'LuxIdentified',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxidentified.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxidentified.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxIdentifiedDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxIdentifiedPerMonthWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'LuxIdentifiedPerMonth',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxidentifiedpermonth.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxidentifiedpermonth.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxIdentifiedPerMonthDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxRecurringWidget')
            ->class(DoughnutChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxRecurring',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxrecurring.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxrecurring.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxRecurringDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxPageVisitsWeekWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxPageVisitsWeek',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisitsweek.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxpagevisitsweek.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxPageVisitsWeekDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxDownloadsWeekWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxDownloadsWeek',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloadsweek.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.luxdownloadsweek.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxDownloadsWeekDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxReferrerWidget')
            ->class(BarChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxReferrer',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.referrer.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.referrer.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'medium',
            ])
            ->arg('$dataProvider', new Reference(LuxReferrerDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxBrowserWidget')
            ->class(DoughnutChartWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxBrowser',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.browser.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.browser.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxBrowserDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }

        $configuration = $services->set('dashboard.widgets.LuxHottestLeadsWidget')
            ->class(ListWidget::class)
            ->tag('dashboard.widget', [
                'identifier' => 'luxHottestLeads',
                'groupNames' => 'luxgroup',
                'title' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.hottestleads.title',
                'description' => 'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.hottestleads.description',
                'iconIdentifier' => 'extension-lux-turquoise',
                'height' => 'medium',
                'width' => 'small',
            ])
            ->arg('$dataProvider', new Reference(LuxHottestLeadsDataProvider::class));
        if ($containerBuilder->hasDefinition(BackendViewFactory::class)) {
            // TYPO3 12
            $configuration->arg('$backendViewFactory', new Reference(BackendViewFactory::class));
        } else {
            // Todo: Can be removed when TYPO3 11 support will be dropped
            $configuration->arg('$view', new Reference('dashboard.views.widget'));
        }
    }
};
