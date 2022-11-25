<?php
namespace LightWine\Modules\Sam\Services;

use LightWine\Modules\Sam\Models\SamLoginResponseModel;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\Sam\Interfaces\ISamService;
use LightWine\Modules\Sam\Models\SamUserRightsReturnModel;
use LightWine\Core\Helpers\DeviceHelpers;
use LightWine\Core\HttpResponse;

class SamService implements ISamService {
    public static $passwordBlowFish = "SeQ3H55Dp9XxndP";

    private TemplatesService $templateService;
    private MysqlConnectionService $databaseConnection;

    public function __construct() {
        $this->templateService = new TemplatesService();
        $this->databaseConnection = new MysqlConnectionService();
    }

    /**
     * Gets the role of the current user
     * @return SamUserRightsReturnModel Model containing all the information of the role of the current user
     */
    public function GetUserRightsAssignment(): SamUserRightsReturnModel {
        $returnModel = new SamUserRightsReturnModel;

        $returnModel->Username = $_SESSION["Username"];
        $returnModel->Role = implode(",", $_SESSION["UserRole"]);

        return $returnModel;
    }

    /**
     * Checks if a specified device is registered
     * @return bool Returns true if registered, false if not registered
     */
    public function CheckDeviceRegistration(): bool {
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
     * Register a new device
     * @return int The pincode that must be entered to complete the registration
     */
    public function RegisterDevice(): int {
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
    public function BasicAuthentication(){
        if(!isset($_SESSION["BasicAuthChecksum"])){
            if (!isset($_SERVER['PHP_AUTH_USER'])) {
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');

                HttpResponse::ShowError(403, "You don't have permission to access this content", "Forbidden");
            }else{
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];

                if($this->Login($username, $password)){
                    $_SESSION["BasicAuthChecksum"] = hash("sha1", Helpers::NewGuid());
                }else{
                    HttpResponse::ShowError(403, "You don't have permission to access this content", "Forbidden");
                }
            }
        }
    }

    /**
     * Checks if the user is loggedin returns true or false
     * @return bool Retruns true if the user is loggedin, false if the user is not loggedin
     */
    public function CheckIfUserIsLoggedin(): bool {
        return isset($_SESSION["Checksum"]);
    }

    /**
     * Login the specified user, using a username and password
     * @param string $username The username of the user you want to login
     * @param string $password The password of the user you want to login
     * @return SamLoginResponseModel Model containing all the details of the specified user and login attempt
     */
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
	            LOWER(`role`.description) AS `role`,
                IFNULL(`user`.settings, '{}') AS settings
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
            $responseModel->Settings = json_decode($this->databaseConnection->DatasetFirstRow("settings"), true);

            // Write the session variables
            $_SESSION["Checksum"] = $responseModel->Checksum;
            $_SESSION["UserId"] = $responseModel->UserId;
            $_SESSION["UserDisplayName"] = $responseModel->UserDisplayName;
            $_SESSION["UserFullname"] = $responseModel->UserDisplayName;
            $_SESSION["UserRole"] = implode(",", $responseModel->Roles);
            $_SESSION["Username"] = $responseModel->Username;
            $_SESSION["ClientToken"] = $responseModel->ClientToken;
            $_SESSION["UserSettings"] = $responseModel->Settings;
        }else{
            $responseModel->LoginCorrect = false;
        }

        return $responseModel;
    }

    /**
     * Logoff the current user
     */
    public function Logoff() {
        unset($_SESSION["Checksum"]);
    }
}
?>