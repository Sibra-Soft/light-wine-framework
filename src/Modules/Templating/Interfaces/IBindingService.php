<?php
namespace LightWine\Modules\Templating\Interfaces;

use LightWine\Modules\Templating\Models\BindingReturnModel;

interface IBindingService
{
    /**
     * This function gets the bindings based on the specified templateId
     * @param int $templateId The templateId
     * @return BindingReturnModel
     */
    public function GetBindingBasedOnTemplateId(int $templateId): BindingReturnModel;
}
