<?php
namespace LightWine\Modules\FlasMessages\Interfaces;

interface IFlashMessageService
{
    /**
     * Generates a new flashmessage ready to be shown
     * @param string $name The name of the message
     * @param string $message The message you want to display
     * @param string $type The type of the message (info, warning, error, etc.)
     */
    public function NewFlashMessage(string $name, string $message, string $type = "info");

    /**
     * Gets a specified message
     * @param string $name The name of the message to show
     */
    public function GetFlashMessage(string $name);
}
