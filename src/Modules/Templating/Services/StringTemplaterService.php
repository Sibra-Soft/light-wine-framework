<?php
namespace LightWine\Modules\Templating\Services;

use LightWine\Modules\Templating\Interfaces\IStringTemplaterService;

class StringTemplaterService implements IStringTemplaterService
{
    private array $Store = [];

    /**
     * Clears all the variables of the current defined class
     */
    public function ClearVariables(){
        $this->Store = [];
    }

    /**
     * Adds a array of variables to the variable store
     * @param array $variablesArray
     */
    public function AssignArrayOfVariables(array $variablesArray){
        foreach($variablesArray as $key => $value){$this->Store[$key] = $value; }
    }

    /**
     * Add one variable to the variable store
     * @param string $key
     * @param string $value
     */
    public function AssignVariable(string $key, string $value){
        $this->Store[$key] = $value;
    }

    /**
     * Do the replacements on the specified content
     * @param string $template
     * @return string
     */
    public function DoReplacements(string $template): string {
        preg_match_all('/(?<=\{{).+?(?=\}})/', $template, $matches);

        foreach($matches[0] as $variable){
            if(array_key_exists($variable,$this->Store)){
                $template = str_replace("{{".$variable."}}", $this->Store[$variable], $template);
            }
        }

        return $template;
    }
}