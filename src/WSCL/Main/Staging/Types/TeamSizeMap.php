<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging\Types;

use WSCL\Main\Staging\Entity\RegistrationRcd;

/**
 * Implements a mapping of teams to their sizes.
 */
class TeamSizeMap
{
    const DIVISION_MAP = ['D1', 'D2', 'D3', 'D4'];

    /** @var array<string, string[]> A map of division suffix to teams in that division */
    private array $highSchoolDivisions;
    /** @var array<string, string[]> A map of division suffix to teams in that division */
    private array $middleSchoolDivisions;

    /** @var TeamSizeEntry[] */
    private array $teamMap = array();

    /**
     *
     * @param int $divisionCnt  Number of divisions within HS/MS
     */
    public function __construct(
        private int $divisionCnt = 1
        )
    {
    }

    public function addRider(string $team, RegistrationRcd $regRcd): void
    {
        // Get the current entry from the map (or null)
        $currEntry = $this->teamMap[$team] ?? null;

        // If there is an entry, update last season points
        if (!isset($currEntry)) {
            $currEntry = new TeamSizeEntry($team);
            $this->teamMap[$team] = $currEntry;
        }

        $currEntry->addRider($regRcd->getCategory());
    }

    public function initializeDivisions(): void
    {
        if (1 != $this->divisionCnt) {
            $this->highSchoolDivisions = $this->determineDivisionChunks($this->teamMap, 'getHighSchoolSize');
            $this->middleSchoolDivisions = $this->determineDivisionChunks($this->teamMap, 'getMiddleSchoolSize');
        }
    }

    /**
     * Determine the distribution of teams accross the divisions
     *
     * @param TeamSizeEntry[] $sizeArray
     * @param string $mapFunc
     *
     * @return array<string, string[]>
     */
    private function determineDivisionChunks(array $sizeArray, string $mapFunc): array
    {
        // Get a mapping of team to rider count
        $sizes = array_map(fn($entry): int => $entry->$mapFunc(), $sizeArray);
        // Filter out the teams with only 1 rider or the 'team' is independent riders
        $sizes = array_filter($sizes, fn($v, $k) => $v > 1 && 'Independent' != $k, ARRAY_FILTER_USE_BOTH);
        // Sort into size order, maintaining team name key
        asort($sizes);

        $sizeGrouping = $this->groupByTeamSize($sizes);
        $divisionSize = $this->determineDivisionSize(count($sizes), $this->divisionCnt);

        $chunkNdx = 0;
        $chunks = [
            $chunkNdx => []
        ];

        while (!empty($sizeGrouping)) {
            $group = array_shift($sizeGrouping);

            // If there isn't room in the current chunk for the next group,
            //  add a new chunk
            if (count($chunks[$chunkNdx]) + count($group) > $divisionSize &&
                $chunkNdx < ($this->divisionCnt - 1)
                )
            {
                $chunkNdx++;
                $chunks[$chunkNdx] = [];
            }

            $chunks[$chunkNdx] += $group;
        }

        // Convert each chunk into a list of teams
        $teamChunks = array_map(fn($entry): array => array_keys($entry), $chunks);

        return $this->relabelChunks($teamChunks);
    }

    /**
     * Determine what size each division should be. In the event that there
     * are an odd number of teams, the larger size is returned.
     *
     * @param int $teamCnt
     * @param int $divisionCnt
     *
     * @return int
     */
    private function determineDivisionSize(int $teamCnt, int $divisionCnt): int
    {
        $remainder = $teamCnt % $divisionCnt;
        $numerator = $teamCnt / $divisionCnt;

        return 0 == $remainder ? $numerator : intval(ceil($numerator));
    }

    /**
     * Generates an array where the index is a team size and the values are
     * an array of teams that are that size. Each if these entries are
     * keyed by the team name and the value is the team size.
     *
     * @param array<string, int> $input An array of team names to team sizes.
     *
     * @return array<int, array<string, int>> An array of teams grouped by
     *      their size, largest to smallest.
     */
    private function groupByTeamSize(array $input): array
    {
        // 1. Group by value
        $groups = [];
        foreach ($input as $team => $value) {
            $groups[$value][$team] = $value;
        }

        // 2. Sort values descending (largest first)
        krsort($groups);

        return $groups;
    }

    /**
     * Change the array keys to be the division identifiers (e.g. D1)
     *
     * @param array<int, string[]> $chunks
     *
     * @return array<string, string[]>
     */
    private function relabelChunks(array $chunks): array
    {
        $result = [];

        foreach($chunks as $key => $value) {
            $result[self::DIVISION_MAP[$key]] = $value;
        }

        return $result;
    }

    public function get(string $team): ?TeamSizeEntry
    {
        return $this->teamMap[$team] ?? null;
    }

    public function getDivisionSuffix(string $team, bool $forHighSchool): string
    {
        $result = '';

        if ($this->divisionCnt != 1) {
            $divisionMap = $forHighSchool ? $this->highSchoolDivisions : $this->middleSchoolDivisions;

            foreach($divisionMap as $key => $value) {
                if (in_array($team, $value)) {
                    $result = $key;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @return array<string, string[]> A map of division suffix to teams in that division
     */
    public function getSchoolDivisionList(bool $forHighSchool): array
    {
        return $forHighSchool ? $this->highSchoolDivisions : $this->middleSchoolDivisions;
    }
}
