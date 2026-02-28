<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Fixtures;

use MaxStan\Mercure\Api\MercureTopicProviderInterface;

/**
 * Test fixture: provides a user-scoped private topic.
 */
readonly class TestPrivateTopicProvider implements MercureTopicProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getPrivateTopics(int $userId): array
    {
        return ["https://example.com/private/user/$userId/messages"];
    }

    /**
     * @inheritDoc
     */
    public function getPublicTopics(): array
    {
        return [];
    }
}
