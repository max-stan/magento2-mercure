<?php

declare(strict_types=1);

namespace MaxStan\Mercure\ViewModel;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MaxStan\Mercure\Model\Config;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;

readonly class Mercure implements ArgumentInterface
{
    private const string ADMIN_REFRESH_ENDPOINT = 'mercure/token/refresh';
    private const string FRONTEND_REFRESH_ENDPOINT = 'rest/V1/mercure/token/refresh';

    public function __construct(
        private Config $config,
        private State $appState,
        private UrlInterface $urlBuilder,
        private HttpContext $httpContext
    ) {
    }

    public function getEncodedParams(): string
    {
        return http_build_query([
            'hub' => base64_encode($this->config->getHubUrl())
        ]);
    }

    public function isLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    public function getTokenRefreshEndpoint(): string
    {
        try {
            $area = $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            return '';
        }

        $path = $area === Area::AREA_ADMINHTML
            ? self::ADMIN_REFRESH_ENDPOINT
            : self::FRONTEND_REFRESH_ENDPOINT;

        return $this->urlBuilder->getUrl($path);
    }
}
