<?php
namespace LightWine\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use LightWine\Core\Helpers\Helpers;

final class HelpersTest extends TestCase
{
    /**
     * @test Checks the MapPath function of the Helpers class
     */
    public function testMapPath(): void {
        $this->assertEquals(dirname(__FILE__, 3)."/test/foo/bar", Helpers::MapPath("~/test/foo/bar"));
        $this->assertEquals($_SERVER["DOCUMENT_ROOT"]."/test/foo/bar", Helpers::MapPath("../test/foo/bar"));
    }

    /**
     * @test Checks the GeneratePincode function of the Helpers class
     */
    public function testGeneratePincode(): void {
        $this->assertTrue(strlen(Helpers::GeneratePincode()) == 4);
    }

    /**
     * @test Checks the FirstOrDefault function of the Helpers class
     */
    public function testFirstOrDefault(): void {
        $this->assertEquals("foo", Helpers::FirstOrDefault(["foo", "bar"]));
    }
}