<?php
namespace LightWine\Modules\Scheduler\Services;

use LightWine\Core\HttpResponse;
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

            $workerService->RunWorker($event->WorkerTemplate);
        }

        return $event;
    }

    /**
     * Update the events table
     */
    private function SyncWorkersAndEvents(){
        $dataset = $this->databaseConnection->GetDataset("
            SELECT
                `id`,
                `name`,
                `template_version_dev`
            FROM site_templates
            WHERE type = 'worker'
        ");

        foreach($dataset as $row){
            $workerId = $row["id"];
            $workerName = $row["name"];
            $guid = md5($workerId.$workerName);
            $workertemplate = $this->templatesService->GetTemplateById($workerId);

            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("workerGuid", $guid);
            $this->databaseConnection->GetDataset("SELECT `id`, `next_run`, `expression`, `name` FROM `_events` WHERE guid = ?workerGuid LIMIT 1;");

            if($this->databaseConnection->rowCount == 0){
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
    }

    /**
     * Checks for events that can be run based on there specified time value
     */
    public function CheckForScheduledEvents(): string {
        $event = new SchedulerModel();
        $runArray = [];

        libxml_use_internal_errors(true);

        $this->SyncWorkersAndEvents();

        $dataset = $this->databaseConnection->GetDataset("SELECT * FROM `_events` WHERE next_run < NOW()");

        foreach($dataset as $row){
            $this->databaseConnection->ClearParameters();

            $eventId = $row["id"];
            $workertemplate = $this->templatesService->GetTemplateById($row["template"]);

            $event->WorkerTemplate = $workertemplate->Content;
            $event->NextRunDate = $this->databaseConnection->DatasetFirstRow("next_run", "datetime");
            $event->CronExpression = $this->databaseConnection->DatasetFirstRow("expression");
            $event->Name = $this->databaseConnection->DatasetFirstRow("name");
            $event->Id = $this->databaseConnection->DatasetFirstRow("id");

            $event = $this->RunScheduledEvent($event);

            if($event->IsRun){
                $this->databaseConnection->AddParameter("next_run", $event->NextRunDate->format("Y-m-d H:i:s"));
                $this->databaseConnection->AddParameter("last_run", $event->LastRunDate->format("Y-m-d H:i:s"));

                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("_events", $eventId);

                unset($event->WorkerTemplate);
                array_push($runArray, $event);
            }
        }

        HttpResponse::SetReturnJson($runArray);

        return "";
    }
}