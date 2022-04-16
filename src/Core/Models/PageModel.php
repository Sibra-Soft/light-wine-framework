<?php
namespace LightWine\Core\Models;

class PageModel
{
    public string $Content;
    
    public array $Scripts;
    public array $Stylesheets;

    public int $SizeInBytes;

    public float $RenderTimeInMs;
}