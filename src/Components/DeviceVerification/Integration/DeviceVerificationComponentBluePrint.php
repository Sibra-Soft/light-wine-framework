<?php
class DeviceVerificationComponentBluePrint
{
    public array $Mode = [
        "Caption" => "Component Mode",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "Select the component mode you want to use",
        "Field" => "dropdown",
        "FieldValues" => ComponentModes::class
    ];
}