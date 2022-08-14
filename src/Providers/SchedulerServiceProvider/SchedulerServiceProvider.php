<?php
namespace LightWine\Providers\SchedulerServiceProvider;

use LightWine\Core\HttpResponse;
use LightWine\Modules\Scheduler\Services\SchedulerService;

class SchedulerServiceProvider
{
    private SchedulerService $scheduler;

    public function __construct(){
        $this->scheduler = new SchedulerService();
    }

    public function Render(){
        HttpResponse::SetData($this->scheduler->CheckForScheduledEvents());
        exit();
    }
}