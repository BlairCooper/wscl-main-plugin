<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Csv;

class RecordFilter
{
    /**
     *
     * @param string[] $record
     * @param int $offset
     *
     * @return bool
     */
    public static function leagueCsvFilter(array $record, int $offset): bool
    {
        $filteredRecord = array_filter($record, fn($value) => !empty($value));

        // Does the filtered record have at least 10% of the fields
        return count($filteredRecord) > count($record) / 10;
    }
}
