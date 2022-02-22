<?php
namespace LightWine\Modules\Templates\Models;

class TemplatePolicyModel
{
    public bool $NO_MINIFICATION = false;
    public bool $ALLOW_TO_RUN_THIS_MODULE_IN_BROWSER = false;
    public bool $LOAD_TEMPLATE_ON_EVERY_PAGE = false;
    public bool $HANDLE_SESSION_VARIABLES = true;
    public bool $HANDLE_GET_VARIABLES = true;
    public bool $HANDLE_POST_VARIABLES = true;
    public bool $HANDLE_DATABASE_BINDINGS = true;
    public bool $NEVER_REMOVE_OLD_VERSIONS = false;
    public bool $USERS_MUST_LOGIN = false;
    public bool $ALLOW_EXPORT = false;
    public bool $CAN_RUN_AS_SERVICE_WORKER = false;
    public bool $BYPASS_MASTERPAGE = false;
    public bool $ENABLE_BASIC_AUTHENTICATION = false;
    public bool $CACHE_TEMPLATE = false;
}