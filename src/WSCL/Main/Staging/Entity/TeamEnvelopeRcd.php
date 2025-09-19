<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Entity;

use RCS\Csv\CsvBindByName;
use RCS\Csv\CsvBindByNameTrait;

class TeamEnvelopeRcd
{
    use CsvBindByNameTrait;

    #[CsvBindByName(column: 'Team')]
    public string $team;

    #[CsvBindByName(column: 'Rider Count')]
    public int $riderCount;

    public function __construct(string $team)
    {
        $this->team = $team;
        $this->riderCount = 0;
    }
}
