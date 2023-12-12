<?php
namespace LightWine\Modules\FlashMessages\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\FlashMessages\Interfaces\IFlashMessageService;

class FlashMessageService implements IFlashMessageService
{
    /**
     * Renders a flashmessage using the specified template
     */
    private function RenderFlashMessage(string $entry, string $message): string {
        $type = StringHelpers::SplitString($entry, "_", 3);

        return '<div class="alert alert-'.$type.'">'.$message.'</div>';
    }

    /** {@inheritdoc} */
    public function NewFlashMessage(string $name, string $message, string $type = "info"){
        $_SESSION["flash_message_".$name."_".$type] = $message;
    }

    /** {@inheritdoc} */
    public function GetFlashMessage(string $name){
        foreach($_SESSION as $key => $value){
            if(StringHelpers::StartsWith($key, "flash_message_".$name)){
                $message = $this->RenderFlashMessage($key, $value);
            }
        }

        return $message;
    }
}