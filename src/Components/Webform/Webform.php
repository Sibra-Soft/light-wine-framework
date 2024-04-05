<?php
namespace LightWine\Components\Webform;

use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Components\ComponentBase;
use LightWine\Components\Webform\Models\WebformComponentModel;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Communication\Models\MessageModel;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\FlashMessages\Services\FlashMessageService;
use LightWine\Modules\Webform\Services\WebformService;
use LightWine\Modules\Communication\Services\MessageQueueService;

class Webform
{
    private ComponentBase $component;
    private WebformComponentModel $settings;
    private WebformService $webformService;
    private MessageQueueService $messageQueue;
    private MysqlConnectionService $databaseConnection;
    private FlashMessageService $flashMessageService;

    public function __construct(int $id){
        $this->component = new ComponentBase();
        $this->webformService = new WebformService();
        $this->messageQueue = new MessageQueueService();
        $this->settings = $this->component->GetSettings(new WebformComponentModel, $id);
        $this->databaseConnection = new MysqlConnectionService();
        $this->flashMessageService = new FlashMessageService();
    }

    public function Init(){
        return $this->RenderComponent();
    }

    /**
     * Sets the analytics of the current webform
     * @param string $status The status you want to update ('form-view', 'form-send')
     * @param int $formId The if of the current webform
     */
    private function Analytics(string $status, int $formId){
        switch($status){
            case "form-view":
                $this->databaseConnection->ClearParameters();
                $this->databaseConnection->AddParameter("formId", $formId);
                $this->databaseConnection->ExecuteQuery("UPDATE `site_forms` SET view_count = view_count + 1 WHERE id = ?formId");
                break;

            case "form-send":
                $this->databaseConnection->ClearParameters();
                $this->databaseConnection->AddParameter("formId", $formId);
                $this->databaseConnection->ExecuteQuery("UPDATE `site_forms` SET click_count = click_count + 1 WHERE id = ?formId");
                break;
        }
    }

    /**
     * Handels the postback of the webform component
     * @param int $formId The id of the webform
     * @return bool Tells if the current state is a postback state
     */
    private function HandlePostback(int $formId): bool {
        $postBack = RequestVariables::Get("_postback");

        if($postBack){
            $formData = json_encode(RequestVariables::ToArray());

            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("form_id", $formId);
            $this->databaseConnection->AddParameter("data", $formData);

            $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_form_data");

            return true;
        }

        return false;
    }

    /**
     * Main render function of the component
     * @return string The content of the component
     */
    private function RenderComponent(): string {
        $unique_id = Helpers::NewGuid();
        $content = "";

        $formId = $this->settings->FormId;

        if($this->HandlePostback($formId)){
            $message = new MessageModel;

            $message->Receiver = "alex@sibra-soft.nl";
            $message->Subject = "Nieuw website bericht";

            $this->messageQueue->AddToMessageQueueBasedOnTemplate($message, 35, RequestVariables::ToArray()[0]);
            $this->Analytics("form-send", $formId);

            $this->flashMessageService->NewFlashMessage("FORM_MESSAGE", "Bedankt voor het versturen van het formulier, wij nemen zo snel mogelijk contact met u op.");

            HttpResponse::RedirectPermanent("/", []);
        }else{
            if($this->flashMessageService->GetFlashMessage("FORM_MESSAGE")){
                $content .= $this->flashMessageService->GetFlashMessage("FORM_MESSAGE");
            }else{
                $content .= '<form class="webform" id="'.$unique_id.'" method="post" >';
                $content .= '<input type="hidden" name="_postback" value="'.$unique_id.'" >';

                $content .= $this->webformService->GetWebformById($formId);

                $this->Analytics("form-view", $formId);

                $content .= '</form>';
            }
        }

        return $content;
    }
}