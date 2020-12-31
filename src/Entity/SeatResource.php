<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity()
 */
class SeatResource implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $order;

    /**
     * @ORM\ManyToOne(targetEntity=Seat::class, inversedBy="seatResources")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Seat $seat;

    /**
     * @ORM\ManyToOne(targetEntity=Resource::class, inversedBy="seatResource")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Resource $resource;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): void
    {
        $this->seat = $seat;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource): void
    {
        $this->resource = $resource;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
