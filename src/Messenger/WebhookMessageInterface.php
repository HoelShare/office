<?php
declare(strict_types=1);

namespace App\Messenger;

interface WebhookMessageInterface
{
    public function toArray(): array;

    public function getUserId(): ?int;
}
