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

    /** Number of divisions within HS/MS */
    private int $divisionCnt = 1;

    /** @var TeamSizeEntry[] */
    private array $teamMap = array();

    public function __construct(int $divisionCnt)
    {
        $this->divisionCnt = $divisionCnt;
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
        $sizes = array_filter($sizes, fn($v, $k) => 1 != $v || 'Independent' != $k, ARRAY_FILTER_USE_BOTH);
        // Sort into size order, maintaining team name key
        asort($sizes);
        // Divide into chunks based on the number of divisions
        $chunks = array_chunk(
            $sizes,
            $this->determineChunkSize(count($sizes), $this->divisionCnt),
            true
            );
        // Convert each chunk into a list of teams
        $teamChunks = array_map(fn($entry): array => array_keys($entry), $chunks);
        // Invert the list so the larger teams come first
        $teamChunks = array_reverse($teamChunks);

        return $this->relabelChunks($teamChunks);
    }

    /**
     * Determine what size each division should be.
     *
     * @param int $teamCnt
     * @param int $divisionCnt
     *
     * @return int
     */
    private function determineChunkSize(int $teamCnt, int $divisionCnt): int
    {
        $remainder = $teamCnt % $divisionCnt;
        $numerator = $teamCnt / $divisionCnt;

        return 0 == $remainder ? $numerator : ceil($numerator);
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
}
