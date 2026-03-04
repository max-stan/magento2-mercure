<?php

declare(strict_types=1);

namespace MaxStan\Mercure\Test\Integration\Model\Config\Source;

use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\Mercure\Model\Config\Source\Algorithm;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for JWT algorithm source model.
 */
class AlgorithmTest extends TestCase
{
    private Algorithm $algorithm;

    protected function setUp(): void
    {
        $this->algorithm = Bootstrap::getObjectManager()->get(Algorithm::class);
    }

    /**
     * Verify toOptionArray returns exactly three algorithm options.
     */
    public function testToOptionArrayReturnsThreeAlgorithms(): void
    {
        $this->assertCount(3, $this->algorithm->toOptionArray());
    }

    /**
     * Verify all expected HMAC algorithm values are present.
     */
    public function testToOptionArrayContainsExpectedValues(): void
    {
        $values = array_column($this->algorithm->toOptionArray(), 'value');

        $this->assertContains('hmac.sha256', $values);
        $this->assertContains('hmac.sha384', $values);
        $this->assertContains('hmac.sha512', $values);
    }

    /**
     * Verify each option has the required value and label keys.
     */
    public function testToOptionArrayHasValueAndLabelKeys(): void
    {
        foreach ($this->algorithm->toOptionArray() as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
