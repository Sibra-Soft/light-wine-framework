<?php
namespace LightWine\Modules\Templating\Interfaces;

interface IStringTemplaterService
{
    public function ClearVariables();
    public function AssignArrayOfVariables(array $variablesArray);
    public function AssignVariable(string $key, string $value);
    public function DoReplacements(string $template) : string;
}
