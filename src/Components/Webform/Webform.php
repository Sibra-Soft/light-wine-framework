<?php
namespace LightWine\Components\Webform;

use LightWine\Components\ComponentBase;
use LightWine\Components\Webform\Models\WebformComponentModel;
use LightWine\Modules\Webform\Services\WebformService;
use LightWine\Modules\Objects\Services\ObjectService;
use LightWine\Modules\Communication\Services\MessageQueueService;

class Webform
{
    private ComponentBase $component;
    private WebformComponentModel $settings;
    private WebformService $webformService;
    private ObjectService $objectService;
    private MessageQueueService $messageQueue;

    public function __construct(int $id){
        $this->component = new ComponentBase();
        $this->webformService = new WebformService();
        $this->objectService = new ObjectService();
        $this->messageQueue = new MessageQueueService();
        $this->settings = $this->component->GetSettings(new WebformComponentModel, $id);

    }

    public function Init(){
        return $this->RenderComponent();
    }

    private function HandlePostback(){

    }

    private function RenderComponent(){
        $content = "";

        $this->HandlePostback();

        // Check if a formId must be used or if the content is specified in the component
        if($this->settings->UseFormId > 0){
            $formId = $this->settings->UseFormId;
            $content = $this->webformService->GetWebformById($formId);
        }else{
            $content = $this->settings->FormHtml;
        }

        return $content;
    }
}