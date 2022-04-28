<?php
namespace LightWine\Modules\Scheduler\Interfaces;

interface ISchedulerService
{
    public function CheckForScheduledEvents(): string;
}
