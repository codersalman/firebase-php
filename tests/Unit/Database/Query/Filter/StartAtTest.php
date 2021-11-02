<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Kreait\Firebase\Database\Query\Filter\StartAt;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class StartAtTest extends UnitTestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testModifyUri(float|bool|int|string $given, string $expected): void
    {
        $filter = new StartAt($given);

        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://domain.tld')));
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public function valueProvider(): array
    {
        return [
            'int' => [1, 'startAt=1'],
            'string' => ['value', 'startAt=%22value%22'],
        ];
    }
}
