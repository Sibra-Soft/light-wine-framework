<?php
namespace LightWine\Modules\Sam\Services;

use LightWine\Modules\Sam\Models\SamLoginResponseModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Modules\Sam\Interfaces\ISamService;
use LightWine\Modules\Sam\Models\SamUserRightsReturnModel;
use LightWine\Core\Helpers\DeviceHelpers;

class SamService implements ISamService {
    public static $passwordBlowFish = "SeQ3H55Dp9XxndP";

    private TemplatesService $templateService;
    private ConfigurationManagerService $configurationManager;
    private MysqlConnectionService $databaseConnection;

    public function __construct() {
        $this->templateService = new TemplatesService();
        $this->configurationManager = new ConfigurationManagerService();
        $this->databaseConnection = new MysqlConnectionService();
    }

    public function GetUserRightsAssignment(): SamUserRightsReturnModel {
        $returnModel = new SamUserRightsReturnModel;

        $returnModel->Username = $_SESSION["Username"];
        $returnModel->Role = implode(",", $_SESSION["UserRole"]);

        return $returnModel;
    }

    public function CheckDeviceRegistration(){
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

    public function RegisterDevice(){
        $pincode = Helpers::GeneratePincode();

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("device_guid", DeviceHelpers::DeviceGuid($_SESSION["UserId"]));
        $this->databaseConnection->AddParameter("user_id", $_SESSION["UserId"]);
        $this->databaseConnection->AddParameter("status", 'waiting');
        $this->databaseConnection->AddParameter("ip", DeviceHelpers::IP());
        $this->databaseConnection->AddParameter("hostname", DeviceHelpers::Hostname());
        $this->databaseConnection->AddParameter("os", DeviceHelpers::OS());
        $this->databaseConnection->AddParameter("verification_pincode", $pincode);

        $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_devices", null, true);

        return $pincode;
    }

    /**
     * This function start a basic authentication for the current template
     */
    private function BasicAuthentication(){
        if(!isset($_SESSION["BasicAuthChecksum"])){
            if (!isset($_SERVER['PHP_AUTH_USER'])) {
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');

                HttpContextHelpers::ShowError(403, "You don't have permission to access this content", "Forbidden");
            }else{
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];

                if($this->Login($username, $password)){
                    $_SESSION["BasicAuthChecksum"] = hash("sha1", Helpers::NewGuid());
                }else{
                    HttpContextHelpers::ShowError(403, "You don't have permission to access this content", "Forbidden");
                }
            }
        }
    }

    public function CheckIfUserIsLoggedin(){
        return isset($_SESSION["Checksum"]);
    }

    public function Login(string $username, string $password): SamLoginResponseModel {
        $responseModel = new SamLoginResponseModel;

        // Generate the function variables
        $requestPassword = hash("sha512", $password.$this->passwordBlowFish);
        $clientToken = $_SESSION["ClientToken"];
        $loginChecksum = Helpers::NewGuid();

        // Build the query to check the login
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->addParameter("loginUsername", $username, "");
        $this->databaseConnection->addParameter("loginPassword", $requestPassword);
        $this->databaseConnection->getDataset("
            SELECT
	            `user`.id,
	            `user`.username,
	            IFNULL(`user`.`display_name`, `user`.username) AS `display_name`,
	            LOWER(`role`.description) AS `role`
            FROM `_users` AS `user`
            INNER JOIN `_user_roles` AS role ON role.id = `user`.role_id
            WHERE `username` = ?loginUsername
                AND `password` = ?loginPassword
                AND `confirm_hash` = 1
            LIMIT 1;"
        );

        if($this->databaseConnection->rowCount > 0){
            // Do some table updating
            $this->databaseConnection->executeQuery("UPDATE `_users` SET last_login = NOW() WHERE `username` = ?loginUsername");

            // Fill the response model
            $responseModel->Checksum = hash("sha1", $loginChecksum);
            $responseModel->ClientToken = $clientToken;
            $responseModel->Roles = explode(",", $this->databaseConnection->DatasetFirstRow("role"));
            $responseModel->UserDisplayName = $this->databaseConnection->DatasetFirstRow("display_name");
            $responseModel->UserFullname = $this->databaseConnection->DatasetFirstRow("display_name");
            $responseModel->UserId = $this->databaseConnection->DatasetFirstRow("id");
            $responseModel->Username = $username;
            $responseModel->LoginCorrect = true;

            // Write the session variables
            $_SESSION["Checksum"] = $responseModel->Checksum;
            $_SESSION["UserId"] = $responseModel->UserId;
            $_SESSION["UserDisplayName"] = $responseModel->UserDisplayName;
            $_SESSION["UserFullname"] = $responseModel->UserDisplayName;
            $_SESSION["UserRole"] = implode(",", $responseModel->Roles);
            $_SESSION["Username"] = $responseModel->Username;
            $_SESSION["ClientToken"] = $responseModel->ClientToken;
        }else{
            $responseModel->LoginCorrect = false;
        }

        return $responseModel;
    }

    public function Logoff() {
        unset($_SESSION["Checksum"]);
    }
}
?>