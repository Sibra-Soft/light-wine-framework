<?php
namespace LightWine\Modules\Scheduler\Services;

use LightWine\Modules\Scheduler\Models\SchedulerModel;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\Logger\Services\LoggerService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Scheduler\Interfaces\ISchedulerService;

use Cron\CronExpression;

class SchedulerService implements ISchedulerService
{
    private LoggerService $logger;
    private MysqlConnectionService $databaseConnection;
    private TemplatesService $templatesService;

    public function __construct(){
        $this->logger = new LoggerService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->templatesService = new TemplatesService();
    }

    /**
     * Runs a scheduled event based on the specified schedulermodel
     * @param SchedulerModel $event Model containing all the details of the scheduled event
     * @return SchedulerModel Model containg all the details of the last executed event
     */
    private function RunScheduledEvent(SchedulerModel $event): SchedulerModel {
        $workerService = new WorkerService();
        $cron = new CronExpression($event->CronExpression);
        $now = Helpers::Now();

        if($event->NextRunDate < $now){
            $event->IsRun = true;
            $event->NextRunDate = $cron->getNextRunDate();
            $event->LastRunDate = $now;

            $workerService->event = $event;
            $workerService->RunWorker($event->WorkerTemplate);
        }

        return $event;
    }

    /**
     * Checks for events that can be run based on there specified time value
     */
    public function CheckForScheduledEvents(): string {
        $event = new SchedulerModel;

        libxml_use_internal_errors(true);

        $dataset = $this->databaseConnection->GetDataset("
            SELECT `id`, `name`, `template_version_dev` FROM site_templates WHERE type = 'worker'
        ");

        foreach($dataset as $row){
            $eventId = null;
            $guid = md5($workerId.$workerName);
            $workerId = $row["id"];
            $workerName = $row["name"];
            $workertemplate = $this->templatesService->GetTemplateById($workerId);

            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("workerGuid", $guid);
            $this->databaseConnection->GetDataset("SELECT `id`, `next_run`, `expression`, `name` FROM `_events` WHERE guid = ?workerGuid LIMIT 1;");

            $eventId = $this->databaseConnection->DatasetFirstRow("id");

            if($this->databaseConnection->rowCount > 0){
                $this->databaseConnection->ClearParameters();

                $event->WorkerTemplate = $workertemplate->Content;
                $event->NextRunDate = $this->databaseConnection->DatasetFirstRow("next_run", "datetime");
                $event->CronExpression = $this->databaseConnection->DatasetFirstRow("expression");
                $event->Name = $this->databaseConnection->DatasetFirstRow("name");
                $event->Id = $this->databaseConnection->DatasetFirstRow("id");

                // Only if the event is run
                $event = $this->RunScheduledEvent($event);
                if($event->IsRun){
                    $this->databaseConnection->AddParameter("next_run", $event->NextRunDate->format("Y-m-d H:i:s"));
                    $this->databaseConnection->AddParameter("last_run", $event->LastRunDate->format("Y-m-d H:i:s"));

                    $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_events", $eventId);

                    return "run";
                }
            }else{
                $xml = simplexml_load_string($workertemplate->Content);
                $expression = $xml->schedule->expression;
                
                $cron = new CronExpression($expression);

                $this->databaseConnection->ClearParameters();
                $this->databaseConnection->AddParameter("guid", $guid);
                $this->databaseConnection->AddParameter("expression", $cron->getExpression());
                $this->databaseConnection->AddParameter("next_run", $cron->getNextRunDate()->format("Y-m-d H:i:s"));
                $this->databaseConnection->AddParameter("name", $row["name"]);
                $this->databaseConnection->AddParameter("template", $row["id"]);

                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_events");
            }
        }

        return "nothing";
    }
}