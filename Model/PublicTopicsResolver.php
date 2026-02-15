<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model;

use MaxStan\Mercure\Api\PublicTopicsResolverInterface;
use MaxStan\Mercure\Api\TopicsResolver;

readonly class PublicTopicsResolver implements TopicsResolver, PublicTopicsResolverInterface
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
