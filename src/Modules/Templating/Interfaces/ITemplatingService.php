<?php
namespace LightWine\Modules\Templating\Interfaces;

use \LightWine\Modules\Templates\Models\TemplateModel;

interface ITemplatingService
{
    public function AddReplaceVariable(string $name, $value);
    public function AddTemplatingVariablesToStore();
    public function AddBindingValuesToStore(int $templateId);
    public function ReplaceContent(string $content): string;
    public function ReplaceAndRenderControls(string $content): string;
    public function ReplaceExtensions(string $content) : string;
    public function ReplaceVariablesFromStore($content);
    public function RunCompilers(string $content);
    public function RenderTemplateAndDoAllReplacements($templateOrId): TemplateModel;
}
