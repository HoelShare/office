<?php
declare(strict_types=1);

namespace App\Ldap;

use App\User\ImportUser;
use App\User\UserMapper;

class LdapProvider
{
    /**
     * @var resource|null
     */
    private $ldapConnection;

    public function __construct(
        private string $server,
        private string $searchFilter,
        private string $bindDn,
        private string $baseDn,
        private UserMapper $userMapper,
        ) {
    }

    public function initializeConnection(string $username, string $password): bool
    {
        $this->logout();

        // connect ldap
        $this->ldapConnection = $this->connect($this->server);

        ldap_set_option($this->ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapConnection, LDAP_OPT_REFERRALS, 0);

        // check login
        if (!$this->login($username, $password)) {
            return false;
        }

        return true;
    }

    /**
     * @return false|resource
     */
    private function connect(string $server)
    {
        return ldap_connect($server);
    }

    public function __destruct()
    {
        $this->logout();
    }

    private function logout(): void
    {
        if ($this->ldapConnection) {
            ldap_unbind($this->ldapConnection);
        }
    }

    private function login(string $username, string $password): bool
    {
        $username = $this->replaceUsername($username, $this->bindDn);

        return @ldap_bind($this->ldapConnection, $username, $password);
    }

    private function replaceUsername(string $username, string $text): string
    {
        return str_replace(
            '{username}',
            ldap_escape($username, '', LDAP_ESCAPE_FILTER),
            $text
        );
    }

    public function getUserData(string $username): ImportUser
    {
        $userFilter = $this->replaceUsername($username, $this->searchFilter);

        $search = ldap_search($this->ldapConnection, $this->baseDn, $userFilter);
        $result = ldap_get_entries($this->ldapConnection, $search);

        return $this->userMapper->mapUserInfo($result[0]);
    }
}
