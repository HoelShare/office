<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Floor implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(nullable=true, type="integer")
     */
    private ?int $number;

    /**
     * @ORM\Column
     * @Assert\NotBlank()
     */
    private string $floorPath;

    /**
     * @ORM\ManyToOne(targetEntity=Building::class, inversedBy="floors")
     * @ORM\JoinColumn(nullable=false, fieldName="buildingId")
     * @Assert\NotBlank
     */
    private ?Building $building;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }

    public function setBuilding(?Building $building): void
    {
        $this->building = $building;
    }

    public function getFloorPath(): string
    {
        return $this->floorPath;
    }

    public function setFloorPath(string $floorPath): void
    {
        $this->floorPath = $floorPath;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
