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
     * @param array $variablesArray A array containg the variables you want to add
     */
    public function AssignArrayOfVariables(array $variablesArray){
        foreach($variablesArray as $key => $value){$this->Store[$key] = $value; }
    }

    /**
     * Add one variable to the variable store
     * @param string $key The name of the variable you want to add
     * @param string $value The value of the variable you want to add
     */
    public function AssignVariable(string $key, string $value){
        $this->Store[$key] = $value;
    }

    /**
     * Do the replacements on the specified content
     * @param string $template The template you want to use
     * @return string The specified template with replaced variables
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