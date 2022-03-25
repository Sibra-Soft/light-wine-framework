<?php
namespace LightWine\Modules\FlasMessages\Enums;

abstract class RequestMethodesEnum
{
    const FLASH_ERROR = 'error';
    const FLASH_WARNING = 'warning';
    const FLASH_INFO = 'info';
    const FLASH_SUCCESS = 'success';
}