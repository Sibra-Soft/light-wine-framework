<?php
namespace LightWine\Modules\Communication\Services;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Communication\Models\SmsMessageModel;
use LightWine\Modules\Communication\Models\MessageModel;
use LightWine\Modules\Communication\Models\MailMessageModel;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\StringTemplaterService;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Communication\Interfaces\IMessageQueueService;

class MessageQueueService implements IMessageQueueService
{
    private MailService $mailService;
    private SmsService $smsService;
    private MysqlConnectionService $databaseConnection;
    private ConfigurationManagerService $settings;
    private StringTemplaterService $stringTemplaterService;
    private TemplatesService $templatesService;

    public function __construct(){
        $this->mailService = new MailService();
        $this->smsService = new SmsService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->settings = new ConfigurationManagerService();
        $this->stringTemplaterService = new StringTemplaterService();
        $this->templatesService = new TemplatesService();
    }

    /**
     * Add a message to the queue based on a template
     * @param int $templateId The id of the template to use
     * @param array $variables A array of variables to use when creating the message
     * @return string The message with replaced variables
     */
    public function AddToMessageQueueBasedOnTemplate(int $templateId, array $variables = []){
        $templateService = new TemplatesService();
        $stringTemplater = new StringTemplaterService();

        // Get the template
        $template = $templateService->GetTemplateById($templateId);

        // Init the stringtemplater
        $stringTemplater->ClearVariables();
        $stringTemplater->AssignArrayOfVariables($variables);

        // Do replacements
        $template = $stringTemplater->DoReplacements($template->Content);

        return $template;
    }

    /**
     * Add a new message to the queue
     * @param MessageModel $message The model containing the details of the message
     */
    public function AddToMessageQueue(MessageModel $message){
        $this->databaseConnection->ClearParameters();

        $messageBody = $message->Body;

        if($message->ReplaceHeaderAndFooter){
            $headerTemplate = $this->templatesService->GetTemplateById($message->HeaderTemplateId)->Content;
            $footerTemplate = $this->templatesService->GetTemplateById($message->FooterTemplateId)->Content;

            $this->stringTemplaterService->AssignVariable("mail-header", $headerTemplate);
            $this->stringTemplaterService->AssignVariable("mail-footer", $footerTemplate);

            $messageBody = $this->stringTemplaterService->DoReplacements($messageBody);
        }

        $this->databaseConnection->AddParameter("receiver_email", $message->Receiver);
        $this->databaseConnection->AddParameter("subject", $message->Subject);
        $this->databaseConnection->AddParameter("body", $messageBody);
        $this->databaseConnection->AddParameter("date_scheduled", $message->DateScheduled->format("Y-m-d h:i:s"));
        $this->databaseConnection->AddParameter("type", $message->Type);

        $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_communication");
    }

    /**
     * Process the current queue
     */
    public function ProcessQueue(){
        $dataset = $this->databaseConnection->GetDataset("SELECT * FROM `_communication` WHERE date_sent IS NULL AND date_processed IS NULL");

        foreach($dataset as $row){
            $id = $row["id"];
            $receiver = $row["receiver_email"];
            $body = $row["body"];
            $subject = $row["subject"];

            // Update the table
            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("mailId", $id);
            $this->databaseConnection->ExecuteQuery("UPDATE `_communication` SET date_processed = NOW(), last_attempt = NOW() WHERE id = ?mailId");

            $messageType = $this->databaseConnection->DatasetFirstRow("type");

            switch($messageType){
                case "email":
                    $message = new MailMessageModel;

                    $message->Body = $body;
                    $message->Subject = $subject;
                    $message->EmailAddress = $receiver;
                    $message->FromName = $this->settings->GetAppSetting("Smtp")["FromName"];
                    $message->FromAddress = $this->settings->GetAppSetting("Smtp")["FromAddress"];

                    $this->mailService->SendMail($message);
                    break;

                case "sms":
                    $message = new SmsMessageModel;

                    $message->Subject = $subject;
                    $message->PhoneNumber = $receiver;
                    $message->Message = $body;

                    $this->smsService->SendSms($message);
                    break;
            }

            $this->databaseConnection->ExecuteQuery("UPDATE `_communication` SET date_sent = NOW() WHERE id = ?mailId");
        }
    }
}