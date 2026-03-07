<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Service;

use MaxStan\Mercure\Api\MercureRefreshTokenInterface;
use MaxStan\Mercure\Model\Jwt\SubscriberTokenProvider;

readonly class MercureRefreshToken implements MercureRefreshTokenInterface
{
    public function __construct(
        private SubscriberTokenProvider $tokenProvider,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function refresh(): string
    {
        return $this->tokenProvider->getJwt();
    }
}
