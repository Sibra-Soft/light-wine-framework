<?php
namespace LightWine\Modules\Communication\Services;

use PHPMailer\PHPMailer\PHPMailer;

use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\Templates\Services\TemplatesService;

use \DateTime;

Class MailService extends CommunicationService {
    protected array $variableStore = array();
    protected PHPMailer $mailer;

    public string $fromName = "";
    public string $fromAddress = "";
    public bool $useEventToSendMail = true;

    private ConfigurationManagerService $config;
    private MysqlConnectionService $databaseService;
    private TemplatesService $templatesService;

    Public function __construct(){
        $this->config = new ConfigurationManagerService();
        $this->databaseService = new MysqlConnectionService();
        $this->templatesService = new TemplatesService();
        $this->mailer = new PHPMailer(true);

        $this->fromAddress = $this->config->GetAppSetting("smtp")["from_address"];
        $this->fromName = $this->config->GetAppSetting("smtp")["from_name"];

        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth = true;
        $this->mailer->Host = $this->config->GetAppSetting("smtp")["host"];
        $this->mailer->Port = $this->config->GetAppSetting("smtp")["port"];
        $this->mailer->Username = $this->config->GetAppSetting("smtp")["username"];
        $this->mailer->Password = $this->config->GetAppSetting("smtp")["password"];
    }

    /**
     * Assign a variable to the templating engine
     * @param string $name The name of the variable used in the template
     * @param string $value The value of the variable used in the template
     */
    public function AssignVariable(string $name, string $value){
        $this->variableStore[$name] = $value;
    }

    /**
     * Function for replacing all the variables with the specified value
     * @param string $templateBody
     * @return string
     */
    private function ReplaceMailTemplateVariables(string $templateBody){
        $returnTemplate = $templateBody;

        foreach($this->variableStore as $name => $value){
            $returnTemplate = str_replace("{{".$name."}}", $value, $returnTemplate);
        }

        return $returnTemplate;
    }

    /** This function adds a attachement to the current mail */
    public function AddAttachment($file, $name){
        $this->mailer->AddAttachment($file, $name,  'base64', 'application/pdf');
    }

    /**
     * Function to send mail using the specified body content
     * @param string $toAddress The address the mail must be send to
     * @param string $body The body of the mail
     * @param string $subject The subject of the mail
     */
    public function SendMail(string $toAddress, string $body, string $subject){
        if($this->useEventToSendMail){
            $this->SendMailUsingEvent($toAddress, $body, $subject, Helpers::Now());
        }else{
            $this->SendMailUsingSendInBlue($toAddress, $body, $subject);
        }
    }

    /** This function sends mail using SendInBlue */
    private function SendMailUsingSendInBlue(string $toAddress, string $body, string $subject){
        $this->mailer->SetFrom($this->fromAddress, $this->fromName);

        $this->mailer->addAddress($toAddress);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $body;
        $this->mailer->send();
    }

    /**
     * Function for sending mails and using a specified mail template
     * @param string $toAddress The address the mail must be send to
     * @param string $templateName The name of the mail template
     * @param string $subject The subject of the mail
     */
    public function SendMailFromTemplate(string $toAddress, string $templateName, string $subject){
        $bodyTemplate = $this->templatesService->GetTemplateByName($templateName, "mail");
        $headerTemplate = $this->templatesService->GetTemplateByName("mail-header", "mail");
        $footerTemplate = $this->templatesService->GetTemplateByName("mail-footer", "mail");

        $this->AssignVariable("mail-header", $headerTemplate->Content);
        $this->AssignVariable("mail-footer", $footerTemplate->Content);
        $mailBody = $this->ReplaceMailTemplateVariables($bodyTemplate->Content);

        if($this->useEventToSendMail){
            $this->SendMailUsingEvent($toAddress, $mailBody, $subject, Helpers::Now());
        }else{
            $this->SendMailUsingSendInBlue($toAddress, $mailBody, $subject);
        }
    }

    //** This function sends mail using the event table */
    public function SendMailUsingEvent(string $toAddress, string $body, string $subject, DateTime $scheduledTime){
        $this->databaseService->ClearParameters();
        $this->databaseService->AddParameter("receiver_email", $toAddress);
        $this->databaseService->AddParameter("subject", $subject);
        $this->databaseService->AddParameter("body", $body);
        $this->databaseService->AddParameter("date_scheduled", $scheduledTime->format("Y-m-d h:i:s"));
        $this->databaseService->AddParameter("type", "email");

        $this->databaseService->helpers->UpdateOrInsertRecordBasedOnParameters("__communication");
    }
}
?>