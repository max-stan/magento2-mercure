<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Fixtures;

use MaxStan\Mercure\Api\MercureTopicProviderInterface;

/**
 * Test fixture: provides a public notification topic.
 */
readonly class TestPublicTopicProvider implements MercureTopicProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getPrivateTopics(int $userId, int $userType): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getPublicTopics(): array
    {
        return ['https://example.com/public/notifications'];
    }
}
