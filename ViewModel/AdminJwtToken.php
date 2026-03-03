<?php

declare(strict_types=1);

namespace MaxStan\Mercure\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use MaxStan\Mercure\Model\Jwt\SubscriberTokenProvider;

class AdminJwtToken implements ArgumentInterface
{
    public function __construct(
        private readonly SubscriberTokenProvider $tokenProvider
    ) {
    }

    public function getJwt(): string
    {
        return $this->tokenProvider->getJwt();
    }
}
