<?php
namespace LightWine\Modules\Templates\Interfaces;

use LightWine\Modules\Templates\Models\TemplateModel;

interface ITemplatesService
{
    /**
     * This function gets a template based on the specified name
     * @param string $templateName The name of the template
     * @param string $templateType The type of the template (html, css, javascript, sql, module, mail)
     * @param string $folderName Optional: foldername where the template is located
     * @return TemplateModel Model containing all the details of the specified template
     */
    public function GetTemplateByName(string $templateName, string $templateType = "html", string $folderName = "*"): TemplateModel;

    /**
     * This function gets a template based on the specified templateId
     * @param int $templateId The id of the template
     * @return TemplateModel Model containing all the details of the specified template
     */
    public function GetTemplateById(int $templateId): TemplateModel;
}
