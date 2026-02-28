<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for JWT signing algorithm options.
 */
class Algorithm implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'hmac.sha256', 'label' => __('HMAC SHA-256')],
            ['value' => 'hmac.sha384', 'label' => __('HMAC SHA-384')],
            ['value' => 'hmac.sha512', 'label' => __('HMAC SHA-512')],
        ];
    }
}
