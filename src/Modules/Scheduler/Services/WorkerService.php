<?php
namespace LightWine\Modules\Scheduler\Services;

use LightWine\Modules\Scheduler\Models\SchedulerModel;
use LightWine\Providers\ModuleProvider\Services\ModuleProviderService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Communication\Services\MailService;

class WorkerService
{
    private ModuleProviderService $moduleService;
    private MysqlConnectionService $databaseConnection;
    private MailService $communicationService;

    public function __construct(SchedulerModel $event){
        $this->moduleService = new ModuleProviderService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->communicationService = new MailService();
    }

    private function DownloadExternalFile($url, $destination){
        file_put_contents($destination, fopen($url, 'r'));
    }

    private function DeleteFileFromServer($file){
        unlink($file);
    }

    /** This function gets all the mail that must be send */
    private function SendMailGenerated(){
        $dataset = $this->databaseConnection->GetDataset("SELECT * FROM `_mail` WHERE date_sent IS NULL");

        foreach($dataset as $row){
            $mailId = $row["id"];
            $receiverMailAddress = $row["receiver_email"];
            $mailBody = $row["body"];
            $mailSubject = $row["subject"];

            // Update the table
            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("mailId", $mailId);
            $this->databaseConnection->ExecuteQuery("UPDATE `_mail` SET date_processed = NOW(), last_attempt = NOW() WHERE id = ?mailId");

            // Send the mail
            $this->communicationService->useEventToSendMail = false;
            $this->communicationService->SendMail($receiverMailAddress, $mailBody, $mailSubject);

            $this->databaseConnection->ExecuteQuery("UPDATE `_mail` SET date_sent = NOW() WHERE id = ?mailId");
        }
    }

    public function RunWorker($workerTemplate){
        $xml = simplexml_load_string($workerTemplate);

        foreach ($xml->task->children() as $node) {
            switch(strtolower($node->getName())){
                case "general-sendmail": $this->SendMailGenerated(); break;
            }
        }
    }
}