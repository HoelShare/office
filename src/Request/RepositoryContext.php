<?php
declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Security\Core\User\UserInterface;

class RepositoryContext
{
    public function __construct(
        private ?string $orderBy = null,
        private string $orderDirection = 'ASC',
        private ?int $limit = 10,
        private ?int $offset = 0,
        private ?UserInterface $user = null,
        private ?array $where = null,
    ) {
        if ($this->limit === null) {
            $this->limit = 10;
        }
        if ($this->offset === null) {
            $this->offset = 0;
        }
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getWhere(): ?array
    {
        return $this->where;
    }
}
