<?php
namespace LightWine\Modules\Resources\Interfaces;

use LightWine\Modules\Templates\Models\TemplateModel;

interface IResourceService
{
    /**
     * This function downloads all the external content from the specified CDN servers
     */
    public function DownloadFromDeploymentServer();

    /**
     * This function gets the resources based on the specified filename
     * @param string $filename The filename of the resource
     * @param string $type The type of resource (javascript or stylesheet)
     * @param bool $singleFileRequest Tells if this is a request for a single file
     * @return string The content of the request resource(s)
     */
    public function GetResourcesBasedOnFilename(string $filename, string $type, bool $singleFileRequest);

    /**
     * Generates the resource URL based on the selected Javascript and Styling templates
     * @param string $type The type of URL to generate (scripts, styling)
     * @return string The generated resource URLs
     */
    public function GenerateResourceURL(string $type, TemplateModel $template): string;
}
