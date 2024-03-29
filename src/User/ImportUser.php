<?php
declare(strict_types=1);

namespace App\User;

class ImportUser
{
    public ?string $id;

    public ?string $email;

    public ?string $displayName;

    public ?string $fullName;

    public ?array $roles = [];

    public ?string $image = null;
}
