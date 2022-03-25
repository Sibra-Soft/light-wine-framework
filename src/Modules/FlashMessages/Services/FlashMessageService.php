<?php
namespace LightWine\Modules\FlasMessages\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\FlasMessages\Interfaces\IFlashMessageService;

class FlashMessageService implements IFlashMessageService
{
    /**
     * Renders a flashmessage using the specified template
     */
    private function RenderFlashMessage(string $entry, string $message): string {
        $type = StringHelpers::SplitString($entry, "_", 3);
        return sprintf('<div class="alert alert-%1">%s</div>', $message, $type);
    }

    /**
     * Generates a new flashmessage ready to be shown
     * @param string $name The name of the message
     * @param string $message The message you want to display
     * @param string $type The type of the message (info, warning, error, etc.)
     */
    public function NewFlashMessage(string $name, string $message, string $type = "info"){
        $_SESSION["flash_message_".$name."_".$type] = $message;
    }

    /**
     * Gets a specified message
     * @param string $name The name of the message to show
     */
    public function GetFlashMessage(string $name){
        foreach($_SESSION as $key => $value){
            if(StringHelpers::StartsWith($key, "flash_message_".$name)){
                $message = $this->RenderFlashMessage($name, $value);
            }
        }

        unset($_SESSION["flash_message_"]);

        return $message;
    }
}