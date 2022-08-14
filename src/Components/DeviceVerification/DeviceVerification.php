<?php
namespace LightWine\Components\DeviceVerification;

use LightWine\Components\ComponentBase;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Modules\Sam\Services\SamService;
use LightWine\Core\Helpers\DeviceHelpers;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Components\DeviceVerification\Models\DeviceVerificationModel;
use LightWine\Modules\Communication\Services\MessageQueueService;

class DeviceVerification {
    private ComponentBase $control;
    private MysqlConnectionService $databaseConnection;
    private SamService $samService;
    private TemplatingService $templatingService;
    private DeviceVerificationModel $settings;
    private MessageQueueService $messageQueue;

    public function __construct(int $id){
        $this->settings = $this->control->GetSettings(new DeviceVerificationModel, $id);
        $this->databaseConnection = new MysqlConnectionService();
        $this->samService = new SamService();
        $this->templatingService = new TemplatingService();
        $this->messageQueue = new MessageQueueService();
    }

    private function ConfirmDeviceVerification(){
        $deviceCode = RequestVariables::Get("device");

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("deviceCode", $deviceCode);

        $this->databaseConnection->ExecuteQuery("UPDATE `_devices` SET `status` = 'verified' WHERE device_guid = ?deviceCode LIMIT 1;");
    }

    private function SendDeviceVerification(){
        // Make sure to only show the message once
        if(!$this->samService->CheckIfUserIsLoggedin()) header("location: /");

        $userId = $_SESSION["UserId"];

        // Check if the device already is verified
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("deviceGuid", DeviceHelpers::DeviceGuid($userId));
        $this->databaseConnection->GetDataset("SELECT * FROM `_devices` WHERE device_guid = ?deviceGuid AND status = 'verified'");

        if($this->databaseConnection->rowCount <= 0){
            $this->databaseConnection->ClearParameters();

            $this->databaseConnection->AddParameter("user_id", $userId);
            $this->databaseConnection->AddParameter("device_guid", DeviceHelpers::DeviceGuid($userId));
            $this->databaseConnection->AddParameter("status", "waiting");
            $this->databaseConnection->AddParameter("ip", DeviceHelpers::IP());
            $this->databaseConnection->AddParameter("os", DeviceHelpers::OS());
            $this->databaseConnection->AddParameter("hostname", DeviceHelpers::Hostname());
            $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_devices");

            // Do main templater replacements
            $templaterService->AssignVariable("os", $device->OS());
            $templaterService->AssignVariable("ip_address", $device->IP());
            $templaterService->AssignVariable("browser", $device->Browser());
            $templaterService->AssignVariable("country_name", $device->Country());

            $this->mainTemplate = $templaterService->DoReplacements($this->mainTemplate);

            // Send the mail for verifing the device
            $mailer = new \Mail();
            $mailer->AssignVariable("name", $_SESSION["UserFullname"]);
            $mailer->AssignVariable("device_ip", $device->IP());
            $mailer->AssignVariable("device_os", $device->OS());
            $mailer->AssignVariable("device_guid", $device->DeviceGuid($userId));
            $mailer->AssignVariable("device_hostname", $device->Hostname());
            $mailer->SendMailFromTemplate($_SESSION["Username"], "device-verify", "Apparaat Verificatie");

            $this->samService->Logoff();
        }else{
            header("location: /dashboard/");
        }
    }

    private function RenderControl(){
        switch($this->settings->Mode){
            case "SendVerification": $this->SendDeviceVerification(); break;
            case "ConfirmVerification": $this->ConfirmDeviceVerification(); break;
        }

        return $this->settings->MainTemplate;
    }

    public function Init(){
        return $this->RenderControl();
    }
}
?>