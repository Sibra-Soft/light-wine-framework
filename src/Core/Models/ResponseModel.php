<?php
namespace LightWine\Core\Models;

use \LightWine\Core\Models\PageModel;

use \DateTime;

class ResponseModel
{
    public PageModel $Page;
    
    public bool $NotFound;

    public int $Length;

    public DateTime $ResponseTime;
}