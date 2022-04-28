<?php
namespace LightWine\Modules\Scheduler\Interfaces;

interface IWorkerService
{
    public function RunWorker(string $workerTemplate);
}
