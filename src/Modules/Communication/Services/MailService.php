<?php
namespace LightWine\Modules\Communication\Services;

use PHPMailer\PHPMailer\PHPMailer;

use LightWine\Modules\Communication\Models\MailMessageModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Communication\Interfaces\IMailService;
use LightWine\Modules\Database\Services\MysqlConnectionService;

Class MailService implements IMailService {
    protected PHPMailer $mailer;

    private ConfigurationManagerService $settings;
    private MysqlConnectionService $databaseConnection;

    Public function __construct(){
        $this->mailer = new PHPMailer(true);
        $this->settings = new ConfigurationManagerService();
        $this->databaseConnection = new MysqlConnectionService();

        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth = true;
        $this->mailer->Host = $this->settings->GetAppSetting("smtp")["host"];
        $this->mailer->Port = $this->settings->GetAppSetting("smtp")["port"];
        $this->mailer->Username = $this->settings->GetAppSetting("smtp")["username"];
        $this->mailer->Password = $this->settings->GetAppSetting("smtp")["password"];
    }

    /**
     * Sends a mail using the details of the MailMessageModel
     * @param MailMessageModel $mail Model containing all the details of the mail message
     */
    public function SendMail(MailMessageModel $mail){
        $this->mailer->SetFrom($mail->FromAddress, $mail->FromName);

        $this->mailer->addAddress($mail->EmailAddress);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $mail->Subject;
        $this->mailer->Body = $mail->Body;

        if($this->mailer->send() && $this->settings->GetAppSetting("LogAllMail") == "true"){
            $this->LogSendMail($mail);
        }
    }

    /**
     * This function logs the mail to the database if successfully send
     * @param MailMessageModel $mail Modle containing all the details of the mail
     */
    private function LogSendMail(MailMessageModel $mail){
        $this->databaseConnection->ClearParameters();

        $this->databaseConnection->AddParameter("send_from_address", $mail->FromAddress);
        $this->databaseConnection->AddParameter("send_from_name", $mail->FromName);
        $this->databaseConnection->AddParameter("subject", $mail->Subject);

        $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_mail");
    }
}
?>