<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class SeatAsset implements JsonSerializable
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
     * @Assert\NotBlank()
     */
    private int $priority;

    /**
     * @ORM\ManyToOne(targetEntity=Seat::class, inversedBy="seatAssets")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private ?Seat $seat;

    /**
     * @ORM\ManyToOne(targetEntity=Asset::class, inversedBy="seatAssets")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private ?Asset $asset;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): void
    {
        $this->seat = $seat;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function setAsset(Asset $asset): void
    {
        $this->asset = $asset;
    }
}
