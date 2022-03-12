<?php
namespace LightWine\Core\Interfaces;

interface IComponentBase
{
    /**
     * Gets the settings of the specified component, and adds them to the model
     * @param mixed $controlInstance The instance of the component, you want to get the settings from
     * @param int $componentId The id of the component in the database
     */
    public function GetSettings($controlInstance, int $componentId): object;
}
