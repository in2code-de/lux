<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Image;

interface ImageServiceInterface
{
    public function getCacheIdentifier(): string;
    public function getCacheKey(): string;
}
