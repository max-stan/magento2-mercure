<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

use Magento\Framework\Exception\LocalizedException;

interface MercureTopicPublisherInterface
{
    /**
     * @param string $topic
     * @param mixed $data
     *
     * @return string
     * @throws LocalizedException
     */
    public function execute(string $topic, mixed $data): string;
}
