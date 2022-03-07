<?php
namespace LightWine\Components\Account;

use LightWine\Components\ComponentBase;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Sam\Models\SamLoginResponseModel;
use LightWine\Modules\Communication\Services\MailService;
use LightWine\Modules\Sam\Services\SamService;
use LightWine\Modules\Api\Enums\RequestMethodesEnum;
use LightWine\Components\Account\Models\AccountComponentModel;

class Account {
    private $returnOutput = "";

    private ComponentBase $control;
    private MysqlConnectionService $databaseConnection;
    private MailService $mailService;
    private SamService $samService;
    private AccountComponentModel $component;

    public function __construct($id){
        $this->control = new ComponentBase();
        $this->databaseConnection = new MysqlConnectionService();
        $this->mailService = new MailService();
        $this->samService = new SamService();
        $this->component = $this->control->GetSettings(new AccountComponentModel, $id);
    }

    public function Init(){
        return $this->RenderControl();
    }

    private function HandleLoginWithLinkedIn(){

    }

    private function HandleLoginWithGoogle(){

    }

    private function HandleResetPasswordMode(){

    }

    private function HandleDeleteAccount(){
        
    }

    /**
     * Function to check if a specified account already exists
     * @param string $username The username of the account to check
     * @return bool `true` if the account exists, `false` if the account does not exist
     */
    private function CheckIfAccountAlreadyExists(string $username){
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("username", $username);
        $this->databaseConnection->GetDataset("SELECT * FROM `_users` WHERE username = ?username LIMIT 1;");

        if($this->databaseConnection->rowCount > 0){
            return true;
        }else{
            return false;
        }
    }

    private function HandleCreateMode(){
        if($_SERVER['REQUEST_METHOD'] == RequestMethodesEnum::POST){
            $requestUsername = HttpContextHelpers::RequestVariable("account-username");
            $requestPassword = HttpContextHelpers::RequestVariable("account-password");
            $requestPasswordConfirm = HttpContextHelpers::RequestVariable("account-password-repeat");
            $requestFullname = HttpContextHelpers::RequestVariable("account-fullname");

            if($requestPassword !== $requestPasswordConfirm){

                $errorTemplate = $this->component->ErrorTemplate;
                $errorTemplate = str_replace("{{message}}", "Gebruikersnaam of wachtwoord onjuist", $errorTemplate);
                $errorTemplate = str_replace("{{message-internal}}", "PASSWORD_NO_MATCH", $errorTemplate);

                $this->returnOutput = str_replace("{{error-template}}", $errorTemplate, $this->returnOutput);

            }elseif($this->CheckIfAccountAlreadyExists($requestUsername)){

                $errorTemplate = $this->component->ErrorTemplate;
                $errorTemplate = str_replace("{{message}}", "U heeft al een account", $errorTemplate);
                $errorTemplate = str_replace("{{message-internal}}", "ACCOUNT_EXISTS", $errorTemplate);

                $this->returnOutput = str_replace("{{error-template}}", $errorTemplate, $this->returnOutput);

            }else{

                $password = hash("sha512", $requestPassword.$this->component->BlowfishSecret);
                $hash = sha1(Helpers::NewGuid());

                if(StringHelpers::IsNullOrWhiteSpace($requestFullname)){
                    $requestFullname = $requestUsername;
                }

                // Create the new account
                $this->databaseConnection->ClearParameters();
                $this->databaseConnection->AddParameter("username", $requestUsername);
                $this->databaseConnection->AddParameter("password", $password);
                $this->databaseConnection->AddParameter("display_name", $requestFullname);
                $this->databaseConnection->AddParameter("role_id", 1);
                $this->databaseConnection->AddParameter("confirm_hash", $hash);
                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_users");

                // Send a activation mail to the user
                $mailTemplate = $this->component->MailTemplate;
                $this->mailService->AssignVariable("token", $hash);
                $this->mailService->SendMailFromTemplate($requestUsername, $mailTemplate, "Welkom bij Moviedos");

                $successTemplate = $this->component->SuccessTemplate;
                $successTemplate = str_replace("{{message-internal}}", "OK", $successTemplate);

                $this->returnOutput = $successTemplate;
            }
        }else{
            $this->returnOutput = str_replace("{{error-template}}", "", $this->returnOutput);
        }
    }

    private function HandleLogoutMode(){
        session_destroy();
        $this->returnOutput = "logout:ok";
    }

    private function HandleConfirmMode(){
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("hash", HttpContextHelpers::RequestVariable("token"));
        $this->databaseConnection->GetDataset("SELECT `id` FROM `_users` WHERE confirm_hash = ?hash LIMIT 1;");

        if($this->databaseConnection->rowCount > 0){
            $this->databaseConnection->ExecuteQuery("UPDATE `_users` SET confirm_hash = 1 WHERE confirm_hash = ?hash LIMIT 1;");

            $this->returnOutput = $this->component->SuccessTemplate;
        }else{
            $this->returnOutput = "Het opgegeven account kan niet worden gevonden, wellicht is het gekoppelde account al eerdere geactiveerd";
        }
    }

    private function HandleLoginMode(){
        $this->returnOutput = $this->component->MainTemplate;
        $errorTemplate = $this->component->ErrorTemplate;

        // Check if the current request is a Post request
        if($_SERVER['REQUEST_METHOD'] == RequestMethodesEnum::POST){
            // Check if we want to use a login provider
            switch(HttpContextHelpers::RequestVariable("signin")){
                case "linkedin": $this->HandleLoginWithLinkedIn(); break;
                case "google": $this->HandleLoginWithGoogle(); break;
            }

            $username = HttpContextHelpers::RequestVariable("login-username");
            $password = HttpContextHelpers::RequestVariable("login-password");

            // Check if the user want's to be keeped loggedin
            if(StringHelpers::IsNullOrWhiteSpace(HttpContextHelpers::RequestVariable("keep-me-loggedin"))){
                setcookie("keep-me-loggedin", 0, 0);
            }else{
                setcookie("keep-me-loggedin", 1, 0);
            }

            // Check if the specified login is correct
            $this->samService->passwordBlowFish = $this->component->BlowfishSecret;
            $loginResponse = $this->samService->Login($username, $password);

            if(!$loginResponse->LoginCorrect){
                $errorTemplate = str_replace("{{message}}", "Gebruikersnaam of wachtwoord onjuist", $errorTemplate);
                $errorTemplate = str_replace("{{message-internal}}", "LOGIN_INCORRECT", $errorTemplate);

                $this->returnOutput = str_replace("{{error-template}}", $errorTemplate, $this->returnOutput);
            }else{
                if($this->samService->CheckDeviceRegistration()){
                    if($this->component->RedirectUrl and $this->component->RedirectAfterLogin){
                        header('location: '.$this->component->RedirectUrl);
                    }else{
                        $this->returnOutput  = $this->component->SuccessTemplate;
                    }
                }else{
                    $registerPincode = $this->samService->RegisterDevice();
                    $this->HandleSendVerificationCode($loginResponse, $registerPincode);

                    unset($_SESSION["Checksum"]); // Logout the current user

                    $errorTemplate = str_replace("{{message-internal}}", "DEVICE_NOT_VERIFIED", $errorTemplate);
                    $this->returnOutput = str_replace("{{error-template}}", $errorTemplate, $this->returnOutput);
                }
            }
        }else{
            if($this->samService->CheckIfUserIsLoggedin() and !(StringHelpers::IsNullOrWhiteSpace($this->component->RedirectUrl)) and $this->component->RedirectIfLoggedIn){
                header('location: '.$this->component->RedirectUrl);
            }
        }

        $this->returnOutput = str_replace("{username}", $username, $this->returnOutput);
        $this->returnOutput = str_replace("{{error-template}}", "", $this->returnOutput);
    }

    private function HandleForgotPasswordMode(){
        $mainTemplate = $this->component->MainTemplate;

        if($_SERVER['REQUEST_METHOD'] == RequestMethodesEnum::POST){
            $username = HttpContextHelpers::RequestVariable("login-username");

            // Get the userId from the specified username
            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("username", $username);
            $this->databaseConnection->GetDataset("SELECT `id`, `display_name` FROM `_users` WHERE `username` = ?username LIMIT 1;");

            $hash = sha1(Helpers::NewGuid());
            $userFullname = $this->databaseConnection->DatasetFirstRow("fullname");

            // Update the hash column
            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("confirm_hash", $hash);
            $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_users");

            // Send the mail
            $this->mailService->AssignVariable("username", $username);
            $this->mailService->AssignVariable("hash", $hash);
            $this->mailService->AssignVariable("name", $userFullname);
            $this->mailService->SendMailFromTemplate($username, $this->component->MainTemplate, "Wachtwoord vergeten");
        }

        return $mainTemplate;
    }

    private function HandleSendVerificationCode(SamLoginResponseModel $loginResponse, int $pincode){
        $this->mailService->AssignVariable("pincode", $pincode);
        $this->mailService->SendMailFromTemplate($loginResponse->Username, "device-verification-mail", "Moviedos apparaat verificatie");
    }

    /**
     * This function renders the current control
     * @return string The HTML of the control that will be displayed on the page
     */
    private function RenderControl(){
        $this->returnOutput = $this->component->MainTemplate;

        switch((int)$this->component->Mode){
            case 0: // Create account
                $this->HandleCreateMode();
                break;

            case 1: // Delete account
                $this->HandleDeleteAccount();
                break;

            case 2: // Confirm account
                $this->HandleConfirmMode();
                break;

            case 3: // Login
                $this->HandleLoginMode();
                break;

            case 4: // Forgot password
                $this->HandleForgotPasswordMode();
                break;

            case 5: // Reset password
                $this->HandleResetPasswordMode();
                break;

            case 6: // Logout
                $this->HandleLogoutMode();
                break;
        }

        return $this->returnOutput;
    }
}