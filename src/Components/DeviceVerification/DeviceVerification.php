<?php
namespace LightWine\Components\DeviceVerification;
use LightWine\Components\ComponentBase;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;

class DeviceVerification {
    private int $controlId = 0;
    private string $mainTemplate = "";

    private ComponentBase $control;
    private MysqlConnectionService $databaseConnection;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
    }

    private function ConfirmDeviceVerification(){
        $deviceCode = HttpContextHelpers::RequestVariable("device");

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("deviceCode", $deviceCode);

        $this->databaseConnection->ExecuteQuery("UPDATE `_devices` SET `status` = 'verified' WHERE device_guid = ?deviceCode LIMIT 1;");
    }

    private function SendDeviceVerification(){
        $dbConnection = new \DalConnection();
        $device = new \services\DeviceService();
        $sam = new \SecurityAccountManager();
        $templaterService = new \TacoTinyTemplater();

        // Make sure to only show the message once
        if(!$sam->CheckIfUserIsLoggedin()){
            header("location: /");
        }

        $userId = $_SESSION["UserId"];

        // Check if the device already is verified
        $dbConnection->ClearParameters();
        $dbConnection->AddParameter("deviceGuid", $device->DeviceGuid($userId));
        $dbConnection->GetDataset("SELECT * FROM `_devices` WHERE device_guid = ?deviceGuid AND status = 'verified'");

        if($dbConnection->rowCount <= 0){
            $dbConnection->ClearParameters();

            $dbConnection->AddParameter("user_id", $userId);
            $dbConnection->AddParameter("device_guid", $device->DeviceGuid($userId));
            $dbConnection->AddParameter("status", "waiting");
            $dbConnection->AddParameter("ip", $device->IP());
            $dbConnection->AddParameter("os", $device->OS());
            $dbConnection->AddParameter("hostname", $device->Hostname());

            \DalHelpers::$dbConnection = $dbConnection;
            \DalHelpers::UpdateOrInsertRecordBasedOnParameters("_devices");

            // Do main templater replacements
            $templaterService->ClearVariables();
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

            $sam->Logoff();
        }else{
            header("location: /dashboard/");
        }
    }

    private function RenderControl(){
        $this->mainTemplate = $control->GetControlTemplate("MainTemplate");

        // Select the function based on the component mode
        switch($control->GetSettings("Mode")){
            case "SendVerification": $this->SendDeviceVerification(); break;
            case "ConfirmVerification": $this->ConfirmDeviceVerification(); break;
        }

        return $this->mainTemplate;
    }

    public function Init(){
        return $this->RenderControl();
    }
}
?>