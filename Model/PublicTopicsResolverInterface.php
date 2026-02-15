<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;

use MaxStan\Mercure\Api\PublicTopicsResolverInterface;
use MaxStan\Mercure\Api\TopicsResolverInterface;

readonly class PublicTopicsResolverInterface implements TopicsResolverInterface, PublicTopicsResolverInterface
{
    public function __construct(
        private array $iris = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getTopics(?int $customerId): array
    {
        return $this->iris;
    }
}
