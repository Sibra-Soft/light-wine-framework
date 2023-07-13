<?php
namespace LightWine\Components\Webform\Integration;

use LightWine\Components\Webform\Enums\ComponentModes;

class WebformComponentBluePrint
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

    public array $FormId = [
        "Caption" => "Form id",
        "Tab" => "General",
        "Mode" => "*",
        "Group" => "General",
        "Description" => "The id of the form you want to render",
        "Field" => "input"
    ];

    public array $MailTemplate = [
        "Caption" => "Mail template",
        "Tab" => "General",
        "Mode" => "3",
        "Group" => "General",
        "Description" => "The mail template to use when sending the webform",
        "Field" => "dropdown",
        "FieldValues" => "templates~mail"
    ];
}