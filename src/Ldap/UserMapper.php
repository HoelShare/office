<?php
declare(strict_types=1);

namespace App\Ldap;

use Exception;

class UserMapper
{
    private string $idPath;

    private string $emailPath;

    private string $displayNamePath;

    private string $fullNamePath;

    private string $rolesPath;

    private string $imagePath;

    public function __construct(array $userMapping)
    {
        $this->idPath = $userMapping['id'];
        $this->emailPath = $userMapping['email'];
        $this->displayNamePath = $userMapping['display_name'];
        $this->fullNamePath = $userMapping['full_name'];
        $this->rolesPath = $userMapping['roles'];
        $this->imagePath = $userMapping['image'];
    }

    public function mapUserInfo(array $rawLdapData): LdapUser
    {
        $user = new LdapUser();
        $user->id = $this->getField($rawLdapData, $this->idPath);
        $user->email = $this->getField($rawLdapData, $this->emailPath);
        $user->displayName = $this->getField($rawLdapData, $this->displayNamePath);
        $user->fullName = $this->getField($rawLdapData, $this->fullNamePath);
        $user->image = $this->getField($rawLdapData, $this->imagePath);
        $user->roles = $this->getField($rawLdapData, $this->rolesPath, true);

        return $user;
    }

    private function getField(array $rawData, string $fieldPath, bool $allowArray = false): string | array
    {
        if (!isset($rawData[$fieldPath])) {
            throw new Exception(sprintf('Field %s not found ', $fieldPath));
        }

        $data = $rawData[$fieldPath];

        if ($allowArray) {
            unset($data['count']);

            return $data;
        }

        return $data[0];
    }
}
