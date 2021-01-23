<?php
declare(strict_types=1);

namespace App\User;

use App\User\ImportUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserMapper
{
    private string $idPath;

    private string $emailPath;

    private string $displayNamePath;

    private string $fullNamePath;

    private string $rolesPath;

    private string $imagePath;

    public function __construct(string $authService, array $userMappings)
    {
        $userMapping = $userMappings[$authService];
        $this->idPath = $userMapping['id'];
        $this->emailPath = $userMapping['email'];
        $this->displayNamePath = $userMapping['display_name'];
        $this->fullNamePath = $userMapping['full_name'];
        $this->rolesPath = $userMapping['roles'];
        $this->imagePath = $userMapping['image'];
    }

    public function mapUserInfo(array $rawData): ImportUser
    {
        $user = new ImportUser();
        $user->id = $this->getField($rawData, $this->idPath);
        $user->email = $this->getField($rawData, $this->emailPath);
        $user->displayName = $this->getField($rawData, $this->displayNamePath) ?? $user->email;
        $user->fullName = $this->getField($rawData, $this->fullNamePath) ?? $user->displayName;
        $user->image = $this->getField($rawData, $this->imagePath);
        $user->roles = $this->getField($rawData, $this->rolesPath, true);

        return $user;
    }

    private function getField(array $rawData, string $fieldPath, bool $allowArray = false): null | string | array
    {
        if (!isset($rawData[$fieldPath])) {
            return null;
        }

        $data = $rawData[$fieldPath];

        if ($allowArray) {
            unset($data['count']);

            return $data;
        }

        return $data[0];
    }
}
