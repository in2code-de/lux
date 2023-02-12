<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\File;
use In2code\Lux\Domain\Repository\FileRepository;
use In2code\Lux\Utility\StringUtility;
use Throwable;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileService
{
    public function getFileFromHref(string $href): ?File
    {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        try {
            $file = $resourceFactory->getFileObjectFromCombinedIdentifier(
                $this->convertHrefToStorageAndFilePath($href)
            );
            if ($file !== null) {
                /** @var File $file */
                $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
                $file = $fileRepository->findByUid($file->getUid());
            }
        } catch (Throwable $exception) {
            unset($exception);
            $file = null;
        }
        return $file;
    }

    /**
     * Try to convert fileadmin/abc.pdf to 1:/abc.pdf
     *
     * @param string $href
     * @return string
     */
    protected function convertHrefToStorageAndFilePath(string $href): string
    {
        $storage = $this->getStorageUidFromPath($href);
        $fileNameAndPath = $this->substitudeBasePathFromHref($href);
        return $storage . ':/' . $fileNameAndPath;
    }

    protected function substitudeBasePathFromHref(string $href): string
    {
        return substr($href, strlen($this->getBasePathFromHref($href)));
    }

    protected function getStorageUidFromPath(string $href): int
    {
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storages = $storageRepository->findAll();
        $storageUid = 0;
        foreach ($storages as $storage) {
            if ($storage->isOnline()) {
                $configuration = $storage->getConfiguration();
                $basePath = $configuration['basePath'];
                if (StringUtility::startsWith($this->cleanHref($href), $basePath)) {
                    $storageUid = $storage->getUid();
                    break;
                }
            }
        }
        return $storageUid;
    }

    protected function getBasePathFromHref(string $href): string
    {
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storages = $storageRepository->findAll();
        $basePath = '';
        foreach ($storages as $storage) {
            if ($storage->isOnline()) {
                $configuration = $storage->getConfiguration();
                $basePath = $configuration['basePath'];
                if (StringUtility::startsWith($this->cleanHref($href), $basePath)) {
                    $basePath = $configuration['basePath'];
                    break;
                }
            }
        }
        return $basePath;
    }

    /**
     * Remove leading slash or domain from href for comparing with basePath
     *
     * @param string $path
     * @return string
     */
    protected function cleanHref(string $path): string
    {
        $path = ltrim($path, StringUtility::getCurrentUri());
        $path = ltrim($path, '/');
        return $path;
    }
}
