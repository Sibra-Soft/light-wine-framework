<?php
namespace LightWine\Modules\Scheduler\Services;

use LightWine\Modules\Communication\Services\MessageQueueService;
use LightWine\Modules\Scheduler\Interfaces\IWorkerService;
use LightWine\Modules\SiteModule\Services\SiteModuleService;

class WorkerService implements IWorkerService
{
    private MessageQueueService $messageQueueService;
    private SiteModuleService $siteModuleService;

    public function __construct(){
        $this->messageQueueService = new MessageQueueService();
        $this->siteModuleService = new SiteModuleService();
    }

    /**
     * Executes a site extension template from the cms
     * @param string $moduleNmae The name of the template you want to execute
     */
    private function ExecuteSiteExtension(string $moduleNmae){
        $this->siteModuleService->RunModule($moduleNmae);
    }

    /**
     * Downloads a external file to the server
     * @param string $url The url of the file to download
     * @param string $destination The location the file must be saved to
     */
    private function DownloadExternalFile(string $url, string $destination){
        file_put_contents($destination, fopen($url, 'r'));
    }

    /**
     * Deletes the specified file from the server
     * @param string $file The file that must be deleted
     */
    private function DeleteFileFromServer(string $file){
        unlink($file);
    }

    /** This function gets all the mail that must be send */
    private function SendMailGenerated(){
        $this->messageQueueService->ProcessQueue();
    }

    /**
     * Runs the worker based on the template
     * @param string $workerTemplate The worker template
     */
    public function RunWorker(string $workerTemplate){
        $xml = simplexml_load_string($workerTemplate);

        foreach ($xml->task->children() as $node) {
            switch(strtolower($node->getName())){
                case "general-sendmail": $this->SendMailGenerated(); break;
                case "run-module": $this->siteModuleService->RunModule($node->body); break;
            }
        }
    }
}