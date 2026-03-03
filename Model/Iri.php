<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

readonly class Iri
{
    public function __construct(
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @throws NoSuchEntityException
     */
    public function get(string $uri): string
    {
        /** @var Store $store */
        $store = $this->storeManager->getDefaultStoreView();

        return $store->getBaseUrl(UrlInterface::URL_TYPE_WEB) . $uri;
    }
}
