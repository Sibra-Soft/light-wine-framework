<?php
namespace LightWine\Providers\ModalServiceProvider;

use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\TemplatingService;

class ModalServiceProvider {
    private TemplatesService $templateService;
    private MysqlConnectionService $databaseConnection;
    private TemplatingService $templatingService;

    public function __construct(){
        $this->templateService = new TemplatesService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->templatingService = new TemplatingService();
    }

    public function Render(){
        $templateName = RequestVariables::Get("templatename");

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("template_name", $templateName);

        $this->databaseConnection->GetDataset("
            SELECT
	            modal.id AS modal_template,
	            javascript.id AS javascript_template
            FROM `site_templates` AS modal
            LEFT JOIN site_templates AS javascript ON javascript.parent_id = modal.id AND javascript.type = 'javascript'

            WHERE modal.`name` = ?template_name
	            AND modal.type = 'modal'
        ");

        if($this->databaseConnection->rowCount == 0){
            HttpResponse::ShowError(404, "Template not found", "Specified template could not be found");
        }else{
            $modalHtmlTemplateId = $this->databaseConnection->DatasetFirstRow("modal_template");
            $modalHtmlTemplate = $this->templateService->GetTemplateById($modalHtmlTemplateId);
            $modalHtml = $this->templatingService->RenderTemplateAndDoAllReplacements($modalHtmlTemplate->Id);

            $modalJavascriptTemplateId = $this->databaseConnection->DatasetFirstRow("javascript_template");

            if(!StringHelpers::IsNullOrWhiteSpace($modalJavascriptTemplateId)){
                $modalJavascriptTemplate = $this->templateService->GetTemplateById($modalJavascriptTemplateId);
                $modalJavascript = $this->templatingService->RenderTemplateAndDoAllReplacements($modalJavascriptTemplate->Id);
                
                HttpResponse::SetReturnJson([
                    "modal_html" => $modalHtml->Content,
                    "modal_javascript" => $modalJavascript->Content
                ]);
            }else{
                HttpResponse::SetReturnJson([
                    "modal_html" => $modalHtml->Content,
                    "modal_javascript" => ""
                ]);
            }
        }

        return "";
    }
}
?>