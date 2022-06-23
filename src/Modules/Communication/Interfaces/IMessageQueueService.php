<?php
namespace LightWine\Modules\Communication\Interfaces;

use LightWine\Modules\Communication\Models\MessageModel;

interface IMessageQueueService
{
    public function AddToMessageQueueBasedOnTemplate(int $templateId, array $variables = []);
    public function AddToMessageQueue(MessageModel $message);
    public function ProcessQueue();
}
