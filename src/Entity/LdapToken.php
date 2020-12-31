<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity()
 */
class LdapToken implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $token;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeImmutable $expire;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ldapTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpire(): ?DateTimeImmutable
    {
        return $this->expire;
    }

    public function setExpire(?DateTimeImmutable $expire): self
    {
        $this->expire = $expire;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
