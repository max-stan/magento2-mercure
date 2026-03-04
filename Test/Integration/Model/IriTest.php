<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model;

use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Model\Iri;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Mercure IRI builder.
 */
#[DbIsolation(true)]
class IriTest extends TestCase
{
    private Iri $iri;

    protected function setUp(): void
    {
        $this->iri = Bootstrap::getObjectManager()->get(Iri::class);
    }

    /**
     * Verify IRI is built from store base URL and appended path.
     */
    public function testGetReturnsBaseUrlWithUri(): void
    {
        $result = $this->iri->get('livechat/conversations/42');

        $this->assertStringEndsWith('livechat/conversations/42', $result);
        $this->assertStringStartsWith('http', $result);
    }

    /**
     * Verify IRI with empty path returns store base URL.
     */
    public function testGetWithEmptyUriReturnsBaseUrl(): void
    {
        $result = $this->iri->get('');

        $this->assertStringStartsWith('http', $result);
        $this->assertStringEndsWith('/', $result);
    }

    /**
     * Verify IRI result contains the default store base URL.
     */
    public function testGetResultContainsStoreBaseUrl(): void
    {
        $baseResult = $this->iri->get('');
        $fullResult = $this->iri->get('some/path');

        $this->assertStringStartsWith($baseResult, $fullResult);
    }
}
