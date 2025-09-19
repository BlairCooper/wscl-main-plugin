<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\Entity;

use RCS\Json\JsonEntity;

class MembershipOrgReportData extends JsonEntity
{
    /** @var ReportTypeGroup[] */
    public array $reportTypeGroups;
//     @JsonProperty("report_type_groups")
//     public List<ReportTypeGroup> groups;


    public function findReportType(string $reportName): ?ReportType
    {
        $result = null;

        foreach ($this->reportTypeGroups as $group) {
            foreach ($group->reportTypes as $type) {
                if (null != $type->name && $type->name == $reportName) {
                    $result = $type;
                    break;
                }
            }
            if (null != $result) {
                break;
            }
        }

        return $result;
    }

    public function getReportDownloadUrl(string $reportName): ?string
    {
        $result = null;

        foreach ($this->reportTypeGroups as $group) {
            foreach ($group->reportTypes as $type) {
                if (null != $type->name && $type->name == $reportName) {
                    $csvFiles = array_filter(
                        $type->lastReport->reportFiles,
                        fn($reportFile) => 'CSV' == $reportFile->format
                        );
                    if (!empty($csvFiles)) {
                        $csvFile = array_shift($csvFiles);
                        $result = $csvFile->url;
                    }
                    break;
                }
            }
            if (null != $result) {
                break;
            }
        }

        return $result;
    }
}

class ReportTypeGroup
{
    public int $id;
    public string $name;
    public string $description;

    /** @var ReportType[] */
    public array $reportTypes;
}

class ReportType
{
    public int $id;
    public ?string $name;
    public string $description;
    public string $updateLink;

    public ?LastReport $lastReport;

    public ?bool $canBeEdited;
}

class LastReport
{
    public int $id;
    public string $taskUuid;
    public string $finishedAt;

    /** @var ReportFile[] */
    public array $reportFiles;

    public bool $isBeingGenerated;
    public bool $hasCrashed;
    public bool $wasRequestedToday;
    public bool $wasRequestedThisWeek;
}

class ReportFile
{
    public string $url; //"https://eventsquare-ccn-prod.s3.amazonaws.com/reports/membership_app/organization/8412431fb9ad4c5104497930857c07fdadd96df3/YzVkODRlMWItN2I1ZS00YjUyLWJjNDQtMGMyYjQ5MGU2Nz/Sep-28-2022-wscl-timing-export-2022-fall-session-washington-student-cycling-league-ZjUy.csv",
    public ?string $name;
    public string $format;  // XSLX, CSV
}
