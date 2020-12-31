<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity()
 */
class Booking implements JsonSerializable
{
    use EntitySerializableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $userId;

    /**
     * @ORM\Column(type="integer")
     */
    private int $seatId;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Seat::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Seat $seat;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $fromDay;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $untilDay;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $createdAt;

    /**
     * @ORM\Column
     * @TODO: Create enum type -> whole day, morning, afternoon
     */
    private string $type;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): void
    {
        $this->seat = $seat;
    }

    public function getFromDay(): DateTimeImmutable
    {
        return $this->fromDay;
    }

    public function setFromDay(DateTimeImmutable $fromDay): void
    {
        $this->fromDay = $fromDay;
    }

    public function getUntilDay(): DateTimeImmutable
    {
        return $this->untilDay;
    }

    public function setUntilDay(DateTimeImmutable $untilDay): void
    {
        $this->untilDay = $untilDay;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getSeatId(): int
    {
        return $this->seatId;
    }

    public function setSeatId(int $seatId): void
    {
        $this->seatId = $seatId;
    }
}
