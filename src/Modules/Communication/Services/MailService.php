<?php
namespace LightWine\Modules\Communication\Services;

use PHPMailer\PHPMailer\PHPMailer;

use LightWine\Modules\Communication\Models\MailMessageModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Communication\Interfaces\IMailService;

Class MailService implements IMailService {
    protected PHPMailer $mailer;

    private ConfigurationManagerService $settings;

    Public function __construct(){
        $this->mailer = new PHPMailer(true);
        $this->settings = new ConfigurationManagerService();

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
        $this->mailer->send();
    }
}
?>