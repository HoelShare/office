<?php
declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use function in_array;
use function is_array;

class ContextFactory
{
    public function create(Request $request, ?UserInterface $user): RepositoryContext
    {
        $whereCondition = null;
        if ($request->get('where') !== null) {
            $whereCondition = $this->extractCondition($request->get('where'));
        }

        $order = $request->get('orderBy');
        $direction = 'ASC';
        if ($order !== null && str_starts_with($order, '-')) {
            $direction = 'DESC';
            $order = mb_substr($order, 1);
        }

        return new RepositoryContext(
            orderBy: $order,
            orderDirection: $direction,
            limit: (int) $request->get('limit', 10),
            offset: (int) $request->get('offset', 0),
            user: $user,
            where: $whereCondition,
        );
    }

    private function extractCondition(string | array $where): array
    {
        if (!is_array($where)) {
            $where = json_decode($where, true, 512, JSON_THROW_ON_ERROR);
        }

        return $this->mapConditions($where);
    }

    private function mapConditions(array $conditions): array
    {
        $mapped = [];

        foreach ($conditions as $key => $condition) {
            if (is_numeric($key)) {
                throw new BadRequestHttpException();
            }

            if (is_array($condition)) {
                $mapped[$key] = $this->validateCondition($condition);
            } else {
                $mapped[$key] = $this->validateCondition([
                    'type' => FilterTypes::EQUALS,
                    'value' => $condition,
                ]);
            }
        }

        return $mapped;
    }

    private function validateCondition(array $condition): array
    {
        if (!isset($condition['type'], $condition['value'])) {
            throw new BadRequestHttpException();
        }

        if (!in_array($condition['type'], [
            FilterTypes::EQUALS,
            FilterTypes::NOT_EQUALS,
            FilterTypes::GREATER_THAN,
            FilterTypes::GREATER_THAN_EQUALS,
            FilterTypes::LESS_THAN,
            FilterTypes::LESS_THAN_EQUALS,
        ], true)) {
            throw new BadRequestHttpException();
        }

        return $condition;
    }
}
