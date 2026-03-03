<?php

declare(strict_types=1);

namespace MaxStan\Mercure\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use MaxStan\Mercure\Model\Config;

readonly class HubUrl implements ArgumentInterface
{
    public function __construct(
        private Config $config
    ) {
    }

    public function getEncodedUrl(): string
    {
        if ($value = $this->config->getHubUrl()) {
            return base64_encode($value);
        }

        return '';
    }
}
