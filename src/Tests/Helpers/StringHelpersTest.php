<?php
namespace LightWine\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use LightWine\Core\Helpers\StringHelpers;

final class StringHelpersTest extends TestCase
{
    /**
     * @test Checks the StripAfterString function of the StringHelpers class
     */
    public function testStripAfterString(): void {
        $this->assertStringStartsWith("this is a ", StringHelpers::StripAfterString("this is a test string", "test"));
    }

    /**
     * @test Checks the IsValidDate function of the StringHelpers class
     */
    public function testIsValidDate(): void {
        $this->assertTrue(StringHelpers::IsValidDate("2020-02-08"), "This date is valid");
        $this->assertFalse(StringHelpers::IsValidDate("2017-09-31"), "This date is invalid");
    }

    /**
     * @test Checks the Pad function of the StringHelpers class
     */
    public function testPad(): void {
        $this->assertEquals("002", StringHelpers::Pad(2, 3));
    }

    /**
     * @test Checks the SplitString function of the StringHelpers class
     */
    public function testSplitString(): void {
        $this->assertEquals("test2", StringHelpers::SplitString("test,test2", ",", 1));
    }

    /**
     * @test Checks the StringBetween function of the StringHelpers class
     */
    public function testStringBetween(): void {
        $this->assertEquals("test", StringHelpers::StringBetween("this is a (test) string", "(", ")"));
    }

    /**
     * @test Checks the Mid function of the StringHelpers class
     */
    public function testMid(): void {
        $this->assertEquals("this is", StringHelpers::Mid("this is a test string", 0, 7));
    }

    /**
     * @test Checks the JoinString function of the StringHelpers class
     */
    public function testJoinString(): void {
        $this->assertEquals("foo,bar", StringHelpers::JoinString(["foo", "bar"], ","));
    }

    /**
     * @test Checks the StartWith function of the StringHelpers class
     */
    public function testStartWith(): void {
        $this->assertTrue(StringHelpers::StartsWith("this is a test", "this is"));
    }

    /**
     * @test Checks the EndsWith function of the StringHelpers class
     */
    public function testEndsWith(): void {
        $this->assertTrue(StringHelpers::EndsWith("this is a test", "a test"));
    }

    /**
     * @test Checks the Contains function of the StringHelpers class
     */
    public function testContains(): void {
        $this->assertTrue(StringHelpers::Contains("this is a test", "is a"));
    }

    /**
     * @test Checks the IsNullOrWhiteSpace function of the StringHelpers class
     */
    public function testIsNullOrWhiteSpace(): void {
        $this->assertTrue(StringHelpers::IsNullOrWhiteSpace(""));
        $this->assertFalse(StringHelpers::IsNullOrWhiteSpace("test"));
    }

    /**
     * @test Checks the TruncateEllipsis function of the StringHelpers class
     */
    public function testTruncateEllipsis(): void {
        $this->assertEquals("this i...", StringHelpers::TruncateEllipsis("this is a test string", 6));
    }
}