<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Controller\Adminhtml\Token;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use MaxStan\Mercure\Api\MercureRefreshTokenInterface;

class Refresh extends Action
{
    public function __construct(
        private readonly MercureRefreshTokenInterface $mercureHttpManagement,
        private readonly JsonFactory $jsonFactory,
        private readonly JsonSerializer $json,
        Context                                       $context
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        return $this->jsonFactory->create()
            ->setJsonData($this->json->serialize($this->mercureHttpManagement->refresh()));
    }
}
