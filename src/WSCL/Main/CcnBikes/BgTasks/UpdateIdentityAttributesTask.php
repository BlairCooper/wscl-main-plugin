<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes\BgTasks;

use Psr\Log\LoggerInterface;
use RCS\WP\BgProcess\BgProcessInterface;
use RCS\WP\BgProcess\BgTaskInterface;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\CcnBikes\Entity\IdentityAttributeTuple;
use WSCL\Main\CcnBikes\Entity\IdentityAttributesResp;
use WSCL\Main\CcnBikes\Enums\IdentityAttributeType;
use WSCL\Main\Staging\RiderAttributeUpdate;
use WSCL\Main\Staging\Entity\CcnRiderImportRcd;


class UpdateIdentityAttributesTask implements RiderAttributeUpdate, BgTaskInterface
{
    protected int $identityId;
    protected string $racePlateName;
    protected string $raceGender;
    protected string $raceCategory;

    public function __construct(int $ccnIdentityId)
    {
        $this->identityId = $ccnIdentityId;
    }

    public function setRacePlateName(string $name): void
    {
        $this->racePlateName = $name;
    }

    public function setRaceCategory(string $category): void
    {
        $this->raceCategory = $category;
    }

    public function setRaceGender(string $gender): void
    {
        $this->raceGender = $gender;
    }

    public function commit(BgProcessInterface $bgProcess): void
    {
        if (isset($this->racePlateName) ||
            isset($this->raceGender) ||
            isset($this->raceCategory)
            ) {
                $bgProcess->pushToQueue($this);
            }
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\BgProcess\BgTaskInterface::run()
     */
    public function run(BgProcessInterface $bgProcess, LoggerInterface $logger, array $params) : bool
    {
        $result = false;

        /** @var \WSCL\Main\CcnBikes\CcnClient */
        $ccnClient = $params[CcnClient::class];

        /** @var IdentityAttributesResp|NULL */
        $attributeResp = $ccnClient->getIdentityAttributes();

        if ($attributeResp) {
            $attributes = array();

            $this->addAttribute(
                $attributeResp,
                $attributes,
                'Race Plate Name',
                $this->racePlateName ?? null
                );
            $this->addAttribute(
                $attributeResp,
                $attributes,
                'Race Category '.CcnRiderImportRcd::getSeasonTags()[0],
                $this->raceCategory ?? null
                );
            $this->addAttribute(
                $attributeResp,
                $attributes,
                'Race Gender',
                $this->raceGender ?? null
                );

            // Should never be empty but check anyway.
            // The task should never have been scheduled if there wasn't anything to do.
            if (!empty($attributes)) {
                $result = $ccnClient->setIdentityAttributes($this->identityId, $attributes);
            } else {
                $result = true;
            }
        }

        return $result;
    }

    /**
     *
     * @param IdentityAttributesResp $attributeResp
     * @param IdentityAttributeTuple[] $attributes
     * @param string $attrName
     * @param string $attrValue
     */
    private function addAttribute(
        IdentityAttributesResp $attributeResp,
        array &$attributes,
        string $attrName,
        ?string $attrValue
        ): void
    {
        if (null !== $attrValue) {
            $attr = $attributeResp->getAttribute($attrName);

            if (null !== $attr) {
                switch ($attr->valueType) {
                    case IdentityAttributeType::ShortText:
                        array_push($attributes, new IdentityAttributeTuple($attr->id, $attrValue));
                        break;

                    case IdentityAttributeType::SingleOption:
                        $option = $attr->getOption($attrValue);

                        if (null !== $option) {
                            array_push($attributes, new IdentityAttributeTuple($attr->id, $option->label, $option->id));
                        }
                        break;

                    case IdentityAttributeType::MultiOption:
                    case IdentityAttributeType::LongText:
                    case IdentityAttributeType::DateTime:
                    case IdentityAttributeType::File:
                    default:
                        break;
                }
            }
        }
    }
}
