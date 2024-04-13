<?php
/**
 * This file is executed before the deployment starts
 */
class BeforeDeployment
{
    /**
     * Removed a directory and subdirectories
     * @param mixed $dirname The directory that must be deleted
     * @return bool True if the directory is deleted
     */
    private function RemoveDir($dirname){
        // Sanity check
        if (!file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }

        // Loop through the folder
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Recurse
            $this->RemoveDir($dirname . DIRECTORY_SEPARATOR . $entry);
        }

        // Clean up
        $dir->close();
        return rmdir($dirname);
    }

    public function __construct(){
        $requestToken = $_GET["token"];
        $configToken = json_decode(file_get_contents("app-config.json"), true)["DeploymentToken"];

        if($requestToken <> $configToken or $configToken == ""){
            header("HTTP/1.1 500 Internal Server Error");
            echo("error: wrong token specified");
            return;
        }

        // Delete directories
        $this->RemoveDir("vendor");
        $this->RemoveDir("cache");

        // Remove index file
        unlink("index.php");
        
        // Activate offline mode
        rename("_index.html", "index.html");

        echo("finished");
    }
}

$deployment = new BeforeDeployment();