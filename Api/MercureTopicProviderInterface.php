<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Api;

interface MercureTopicProviderInterface
{
    public function getPrivateTopics(int $userId): array;

    public function getPublicTopics(): array;
}
