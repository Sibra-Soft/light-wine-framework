<?php
namespace LightWine\Components\DeviceVerification;

use LightWine\Components\ComponentBase;
use LightWine\Components\DeviceVerification\Enums\ComponentModes;
use LightWine\Components\DeviceVerification\Models\RegistrationReturnModel;
use LightWine\Core\Helpers\DeviceHelpers;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Communication\Models\MessageModel;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Sam\Services\SamService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\StringTemplaterService;
use LightWine\Components\DeviceVerification\Models\DeviceVerificationComponentModel;
use LightWine\Modules\Communication\Services\MessageQueueService;

class DeviceVerification {
    private ComponentBase $control;
    private MysqlConnectionService $databaseConnection;
    private SamService $samService;
    private StringTemplaterService $stringTemplaterService;
    private DeviceVerificationComponentModel $settings;
    private MessageQueueService $messageQueue;
    private TemplatesService $templatesService;

    public function __construct(int $id){
        $this->control = new ComponentBase();
        $this->settings = $this->control->GetSettings(new DeviceVerificationComponentModel, $id);
        $this->databaseConnection = new MysqlConnectionService();
        $this->samService = new SamService();
        $this->stringTemplaterService = new StringTemplaterService();
        $this->messageQueue = new MessageQueueService();
        $this->templatesService = new TemplatesService();
    }

    /**
     * Sends a device verification mail, and shows the correct template
     */
    private function SendDeviceVerification(){
        $template = "";

        if($this->CheckIfDeviceIsRegisteredAndVerified()){
            if($this->settings->RedirectIfVerified){
                HttpResponse::RedirectPermanent($this->settings->RedirectUrlIfVerified, []);
            }

            return "";
        }else{
            // Check if a mail template is specified
            if($this->settings->MailTemplate > 0){
                $template = $this->templatesService->GetTemplateById($this->settings->MailTemplate)->Content;
            }else{
                $template = $this->settings->VerifyTemplate;
            }

            $deviceRegistration = $this->RegisterCurrentDevice();

            if($this->settings->UsePincodeToVerify){
                $messageModel = new MessageModel();

                $this->stringTemplaterService->AssignVariable("pincode", $deviceRegistration->Pincode);
                $template = $this->stringTemplaterService->DoReplacements($template);

                $messageModel->ReplaceHeaderAndFooter = true;
                $messageModel->HeaderTemplateId = 96;
                $messageModel->FooterTemplateId = 97;
                $messageModel->DateScheduled = Helpers::Now();
                $messageModel->Receiver = "alex@sibra-soft.nl";
                $messageModel->Subject = "Apparaat verificatie";
                $messageModel->Body = $template;

                $this->messageQueue->AddToMessageQueue($messageModel);
            }

            HttpResponse::RedirectPermanent($this->settings->RedirectUrlIfNotVerified, []);
        }
    }

    /**
     * Confirms the device verification
     * @return string The confirm template
     */
    private function ConfirmDeviceVerification(): string {
        if($this->VerifyCurrentDevice()){
            return $this->settings->ConfirmTemplate;
        }else{
            return $this->settings->ErrorTemplate;
        }
    }

    /**
     * Verify the current device
     * @return bool Returns `True` if verified, `False` if not
     */
    private function VerifyCurrentDevice(): bool {
        $this->databaseConnection->ClearParameters();

        if($this->settings->UsePincodeToVerify){
            $pincode = RequestVariables::Get("pincode");

            $this->databaseConnection->AddParameter("pincode", $pincode);
            $this->databaseConnection->GetDataset("UPDATE `_devices` SET status = 'verified' WHERE verification_pincode = ?pincode LIMIT 1;");

            if($this->databaseConnection->rowsAffected > 0){
                return true;
            }else{
                return false;
            }
        }else{
            $guid = RequestVariables::Get("device_guid");

            $this->databaseConnection->AddParameter("guid", $guid);
            $this->databaseConnection->GetDataset("UPDATE `_devices` SET status = 'verified' WHERE device_guid = ?guid LIMIT 1;");

            if($this->databaseConnection->rowsAffected > 0){
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * Check if the current device is registered and verified
     * @return bool Returns `True` if registered and verified, `False` if not
     */
    private function CheckIfDeviceIsRegisteredAndVerified(): bool {
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("deviceId", DeviceHelpers::DeviceGuid($_SESSION["UserId"]));
        $this->databaseConnection->AddParameter("userId", $_SESSION["UserId"]);
        $this->databaseConnection->GetDataset("SELECT * FROM `_devices` WHERE device_guid = ?deviceId AND user_id = ?userId LIMIT 1;");

        if($this->databaseConnection->rowCount > 0){
            if($this->databaseConnection->DatasetFirstRow("status") == "verified"){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Registres the current device and returns the device guid and generated pincode
     * @return RegistrationReturnModel Model containing the pincode and guid
     */
    private function RegisterCurrentDevice(): RegistrationReturnModel {
        $returnModel = new RegistrationReturnModel;

        $pincode = Helpers::GeneratePincode();
        $deviceGuid = DeviceHelpers::DeviceGuid($_SESSION["UserId"]);

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("device_guid", $deviceGuid);
        $this->databaseConnection->AddParameter("user_id", $_SESSION["UserId"]);
        $this->databaseConnection->AddParameter("status", 'waiting');
        $this->databaseConnection->AddParameter("ip", DeviceHelpers::IP());
        $this->databaseConnection->AddParameter("hostname", DeviceHelpers::Hostname());
        $this->databaseConnection->AddParameter("os", DeviceHelpers::OS());
        $this->databaseConnection->AddParameter("verification_pincode", $pincode);

        $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_devices", null, true);

        $returnModel->Pincode = $pincode;
        $returnModel->DeviceGuid = $deviceGuid;

        return $returnModel;
    }

    private function RenderControl(): string {
        if(!isset($_SESSION["UserId"])){
            HttpResponse::RedirectPermanent("/", []);
        }

        switch($this->settings->Mode){
            case ComponentModes::Verify: return $this->SendDeviceVerification();
            case ComponentModes::Confirm: return $this->ConfirmDeviceVerification();

            default: return "Unknown component mode";
        }
    }

    public function Init(){
        return $this->RenderControl();
    }
}
?>