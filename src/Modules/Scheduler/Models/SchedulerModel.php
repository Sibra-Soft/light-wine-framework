<?php
namespace LightWine\Modules\Scheduler\Models;

use \DateTime;

class SchedulerModel
{
    public DateTime $NextRunDate;
    public DateTime $LastRunDate;

    public bool $IsRun = false;

    public string $CronExpression;
    public string $WorkerTemplate;
    public string $Name;

    public int $Id;
}