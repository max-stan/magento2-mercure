<?php

declare(strict_types=1);

namespace MaxStan\Mercure\ViewModel;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

readonly class Mercure implements ArgumentInterface
{
    public const string MERCURE_ENDPOINT = '.well-known/mercure';

    public function __construct(
        private UrlInterface $urlBuilder
    ) {
    }

    public function getHubUrl(): string
    {
        return rtrim($this->urlBuilder->getUrl(self::MERCURE_ENDPOINT), '/');
    }
}
