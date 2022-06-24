<?php
namespace LightWine\Tests\Services;

use PHPUnit\Framework\TestCase;

use LightWine\Modules\Templating\Services\StringTemplaterService;

final class StringTemplaterServiceTest extends TestCase
{
    private StringTemplaterService $stringTemplaterService;

    public function setup(): void {
        $this->stringTemplaterService = new StringTemplaterService();
    }

    /**
     * @test Tests the DoReplacements function of the StringTemplaterService class
     */
    public function testReplacementFunction(): void {
        $this->stringTemplaterService->ClearVariables();
        $this->stringTemplaterService->AssignVariable("foo", "bar");

        $template = $this->stringTemplaterService->DoReplacements("this is a test templater {{foo}}");

        $this->assertEquals("this is a test templater bar", $template);
    }

    /**
     * @test Tests the DoReplacements function of the StringTemplaterService class using a array of variables
     */
    public function testReplacementFunctionUsingArray(): void {
        $this->stringTemplaterService->ClearVariables();
        $this->stringTemplaterService->AssignArrayOfVariables(["foo" => "bar", "test" => "test2"]);

        $template = $this->stringTemplaterService->DoReplacements("this is a test templater {{foo}} and {{test}}");

        $this->assertEquals("this is a test templater bar and test2", $template);
    }
}