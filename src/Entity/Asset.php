<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Asset implements JsonSerializable
{
    use EntitySerializableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    private string $type;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity=SeatAsset::class, mappedBy="asset")
     */
    private Collection $seatAssets;

    public function __construct()
    {
        $this->seatAssets = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSeatAssets(): ArrayCollection | Collection
    {
        return $this->seatAssets;
    }

    public function setSeatAssets(ArrayCollection | Collection $seatAssets): void
    {
        $this->seatAssets = $seatAssets;
    }
}
