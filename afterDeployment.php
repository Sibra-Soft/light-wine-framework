<?php
/**
 * This file is executed after the deployment is finished
 */
class AfterDeployment
{
    public function __construct(){
        $requestToken = $_GET["token"];
        $configToken = json_decode(file_get_contents("app-config.json"), true)["DeploymentToken"];

        if($requestToken <> $configToken or $configToken == ""){
            header("HTTP/1.1 500 Internal Server Error");
            echo("error: wrong token specified");
            return;
        }

        // Install composer packages
        require_once($_SERVER["DOCUMENT_ROOT"]."/publish/_composer.php");

        // Copy production files
        copy($_SERVER["DOCUMENT_ROOT"]."/publish/index-live.php", "index.php");
        copy($_SERVER["DOCUMENT_ROOT"]."/publish/app-config-live.json", "app-config.json");

        // Disable downstate
        rename("index.html", "_index.html");

        echo("finished");
    }
}

$deployment = new AfterDeployment();