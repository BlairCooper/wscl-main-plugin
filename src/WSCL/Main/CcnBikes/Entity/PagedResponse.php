<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;

class PagedResponse extends JsonEntity
{
    public int $count;
    public ?string $next;
    public ?string $previous;

//    private List<T> results;

    public function getCnt(): int
    {
        return $this->count;
    }

    public function hasPrevious(): bool
    {
        return !is_null($this->previous);
    }

    public function hasNext(): bool
    {
        return !is_null($this->next);
    }

//     public List<T> getResults() {
//         return results;
//     }
}
