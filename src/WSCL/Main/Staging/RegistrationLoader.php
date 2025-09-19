<?php
declare(strict_types = 1);
namespace WSCL\Main\Staging;

use function GuzzleHttp\json_encode;
use JsonMapper\JsonMapperInterface;
use JsonMapper\Middleware\FinalCallback;
use JsonMapper\ValueObjects\PropertyMap;
use JsonMapper\Wrapper\ObjectWrapper;
use League\Csv\Reader;
use League\Csv\Statement;
use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use WSCL\Main\CcnBikes\BgTasks\UpdateIdentityAttributesTask;
use WSCL\Main\Staging\Entity\CcnRiderImportRcd;
use WSCL\Main\Staging\Entity\RegistrationImportRcd;
use WSCL\Main\Staging\Types\RegisteredRiderMap;

class RegistrationLoader
{
    private RegisteredRiderMap $regRiderMap;
    private bool $isMissingData = false;

    public function __construct(
        private LoggerInterface $logger,
        private BgProcessInterface $bgProcess
        )
    {
        $this->regRiderMap = new RegisteredRiderMap();
    }

    public function getRiderMap(): RegisteredRiderMap
    {
        return $this->regRiderMap;
    }

    public function isMissingData(): bool
    {
        return $this->isMissingData;
    }

    public function loadRegistrationFile(string $regFile): void
    {
        if (!empty($regFile)) {
            $mapper = (new \JsonMapper\JsonMapperFactory())->bestFit();

            $mapper->push(CcnRiderImportRcd::getValueTransformer());
            $mapper->push($this->getCallback());

            $reader = Reader::createFromPath($regFile);
            $reader->skipInputBOM();
            $reader->setHeaderOffset(0);

            $header = $reader->getHeader(); //returns the CSV header record
            $header = $this->remapHeaders($header, self::getCategoryHeaderMap());
            $header = $this->remapHeaders($header, CcnRiderImportRcd::getColumnPropertyMap());

            $resultSet = Statement::create()
                ->where(array('WSCL\Main\CcnBikes\Csv\RecordFilter', 'leagueCsvFilter'))
                ->process($reader, $header);

            $records = $resultSet->getRecords($header);

            foreach ($records as $rcd) {
                $json = json_encode($rcd);

                $mapper->mapObjectFromString($json, new CcnRiderImportRcd());
            }
        }
    }

    /**
     *
     * @return array<string, string>
     */
    public static function getCategoryHeaderMap(): array
    {
        $result = [];

        $year = intval(date("Y"));
        $month = intval(date("m"));

        $currSeason = $month <=6 ? 'Sp'.$year : 'Fall'.$year;
        $lastSeason = $month <=6 ? 'Fall'.($year-1) : 'Sp'.$year;
        $prevSeason = $month <=6 ? 'Sp'.($year-1) : 'Fall'.($year-1);

        $result[CcnRiderImportRcd::RACE_CATEGORY.$currSeason] = CcnRiderImportRcd::RACE_CATEGORY_CURRENT;
        $result[CcnRiderImportRcd::RACE_CATEGORY.$lastSeason] = CcnRiderImportRcd::RACE_CATEGORY_LAST;
        $result[CcnRiderImportRcd::RACE_CATEGORY.$prevSeason] = CcnRiderImportRcd::RACE_CATEGORY_PREVIOUS;

        return $result;
    }

    private function getCallback() : FinalCallback
    {
        return new FinalCallback(
            function (
                \stdClass $json,
                ObjectWrapper $objWrapper,
                PropertyMap $propMap,
                JsonMapperInterface $mapper): void
                {
                    /** @var RegistrationImportRcd */
                    $rider = $objWrapper->getObject();

                    if (!empty($rider->getRegSysId())) {
                        $this->regRiderMap->put($rider->getRegSysId(), $rider);

                        $bgTask = new UpdateIdentityAttributesTask($rider->getRegSysId());

                        $rider->validateRider($this->logger, $bgTask);

                        $bgTask->commit($this->bgProcess);

                        $this->isMissingData = $this->isMissingData || $rider->isMissingData();
                    }
            });
    }

    /**
     * Renames values in the header array with values provided in the mapping
     * array.
     *
     * @param string[] $header The array of headers
     * @param array<string, string> $mapping A mapping of old headers to new headers.
     *
     * @return string[] The updated header array.
     */
    private function remapHeaders(array $header, array $mapping): array
    {
        return array_map(
            fn ($field) => $mapping[$field] ?? $field,
            $header
            );
    }
}
