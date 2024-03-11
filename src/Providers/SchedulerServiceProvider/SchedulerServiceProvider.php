<?php
namespace LightWine\Providers\SchedulerServiceProvider;

use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\HttpResponse;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Scheduler\Services\SchedulerService;

class SchedulerServiceProvider
{
    private SchedulerService $scheduler;
    private ConfigurationManagerService $settings;

    public function __construct(){
        $this->settings = new ConfigurationManagerService();
        $this->scheduler = new SchedulerService();
    }

    public function Render(){
        $token = $this->settings->GetAppSetting("scheduler")["token"];
        $requestToken = RequestVariables::Get("token");

        if($token !== $requestToken){
            Throw new \Exception("Invalid token");
        }else{
            HttpResponse::SetData($this->scheduler->CheckForScheduledEvents());
            exit();
        }
    }
}