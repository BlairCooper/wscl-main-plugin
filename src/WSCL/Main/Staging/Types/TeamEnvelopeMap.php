<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\Rider;
use WSCL\Main\Staging\Entity\TeamEnvelopeRcd;

class TeamEnvelopeMap
{
    /** @var TeamEnvelopeRcd[] */
    private array $entries = array();

    public function add(Rider $rider): void
    {
        $team = $rider->getTeam();

        if (!isset($this->entries[$team])) {
            $this->entries[$team] = new TeamEnvelopeRcd($team);
        }

        $this->entries[$team]->riderCount++;
    }

    /**
     *
     * @return TeamEnvelopeRcd[] Array of TeamEnvelopeRcds sort by name
     */
    public function getEntries(): array
    {
        usort($this->entries, array($this, 'compare'));

        return $this->entries;
    }

    private function compare(TeamEnvelopeRcd $t1, TeamEnvelopeRcd $t2): int
    {
        return $t1->team <=> $t2->team;
    }
}
