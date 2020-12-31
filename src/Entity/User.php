<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity()
 */
class User implements UserInterface, JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private int $ldapId;

    /**
     * @ORM\Column()
     */
    private string $email;

    /**
     * @ORM\Column()
     */
    private string $name;

    /**
     * @ORM\Column()
     */
    private string $fullName;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $active;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles;

    /**
     * @ORM\OneToMany(targetEntity=LdapToken::class, mappedBy="user", orphanRemoval=true)
     */
    private Collection $ldapTokens;

    public function __construct()
    {
        $this->ldapTokens = new ArrayCollection();
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->getName();
    }

    public function eraseCredentials(): void
    {
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getLdapId(): int
    {
        return $this->ldapId;
    }

    public function setLdapId(int $ldapId): void
    {
        $this->ldapId = $ldapId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword()
    {
        return null;
    }

    /**
     * @return Collection|LdapToken[]
     */
    public function getLdapTokens(): Collection
    {
        return $this->ldapTokens;
    }

    public function addLdapToken(LdapToken $ldapToken): self
    {
        if (!$this->ldapTokens->contains($ldapToken)) {
            $this->ldapTokens[] = $ldapToken;
            $ldapToken->setUser($this);
        }

        return $this;
    }

    public function removeLdapToken(LdapToken $ldapToken): self
    {
        if ($this->ldapTokens->removeElement($ldapToken)) {
            // set the owning side to null (unless already changed)
            if ($ldapToken->getUser() === $this) {
                $ldapToken->setUser(null);
            }
        }

        return $this;
    }
}
