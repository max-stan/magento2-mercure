<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

interface MercureTopicResolverInterface
{
    public function getAllowedPrivateTopics(int $userId): array;

    public function getAllowedPublicTopics(): array;

    public function getAllAllowedTopics(?int $userId): array;
}
