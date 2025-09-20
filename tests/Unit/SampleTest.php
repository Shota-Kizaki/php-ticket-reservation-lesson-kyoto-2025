<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
final class SampleTest extends TestCase
{
    public function testGreetsWithName(): void
    {
        $this->assertSame(123, 123);
    }
}