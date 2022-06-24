<?php
namespace LightWine\Tests\Services;

use PHPUnit\Framework\TestCase;

use LightWine\Modules\Templating\Services\TemplatingEngineService;

final class TemplatingEngineServiceTest extends TestCase
{
    private TemplatingEngineService $templatingEngineService;

    public function setup(): void {
        $this->templatingEngineService = new TemplatingEngineService();
    }

    /**
     * @test Tests the If and Else statements of the TemplatingEngineService class
     */
    public function testIfStatements(): void {
        // If statement
        $template = $this->templatingEngineService->RunEngineCompilers('@if($test == 3) foo @else bar @endif', ["test" => 3]);
        $this->assertEquals("foo", trim($template));

        // Else statement
        $template = $this->templatingEngineService->RunEngineCompilers('@if($test == 3) foo @else bar @endif', ["test" => 5]);
        $this->assertEquals("bar", trim($template));
    }

    /**
     * @test Tests the ForEach statement of the TemplatingEngineService class
     */
    public function testForEachStatement(): void {
        $template = $this->templatingEngineService->RunEngineCompilers('@foreach($lines as $line) {{$line}} @endforeach', ["lines" => ["foo", "bar"]]);
        $this->assertEquals("foo  bar", trim($template));
    }

    /**
     * @test Tests the VariableReplacement function of the TemplatingEngineService class
     */
    public function testVariableReplacement(): void {
        $template = $this->templatingEngineService->RunEngineCompilers('{{$foo}}', ["foo" => "bar"]);
        $this->assertEquals("bar", trim($template));
    }
}