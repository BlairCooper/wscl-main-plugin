<?php
namespace WSCL\Main\Scholarships\Cron;

use Psr\Log\LoggerInterface;
use RCS\WP\CronJob;
use RCS\WP\Formidable\Formidable;
use WSCL\Main\CcnBikes\CcnClient;
use WSCL\Main\CcnBikes\Entity\CouponsResponse;
use WSCL\Main\Scholarships\ScholarshipsHelper;

class ScholarshipsCronJob extends CronJob
{
    public function __construct(
        private CcnClient $ccnClient,
        LoggerInterface $logger
        )
    {
        parent::__construct($logger);

        $this->initializeCronJob('WsclScholarshipDailyCron', 'daily');
    }

    /**
     *
     * {@inheritDoc}
     * @see \RCS\WP\CronJob::runJob()
     */
    public function runJob(): void
    {
        $this->logger->debug('Running ScholarshipCronJob');

        // Fetch the coupon information from CCN
        $couponResp = $this->ccnClient->getCoupons();

        if (!is_null($couponResp)) {
            $this->processEntries(
                $couponResp,
                Formidable::getFieldId(ScholarshipsHelper::FLD_COACH_COUPON_USED),
                Formidable::getFieldId(ScholarshipsHelper::FLD_COACH_COUPON_CODE)
                );
            $this->processEntries(
                $couponResp,
                Formidable::getFieldId(ScholarshipsHelper::FLD_STUDENT_COUPON_USED),
                Formidable::getFieldId(ScholarshipsHelper::FLD_STUDENT_COUPON_CODE)
                );
        } else {
            $this->logger->error('CCN Client did not return any coupons');
        }
    }

    private function processEntries(CouponsResponse $ccnCoupons, int $useField, int $codeField): void
    {
        // Get items where coupon use is 0
        $useEntries = \FrmEntryMeta::getAll([
            'it.field_id' => $useField,
            'it.meta_value' => 0
        ]);

        if (!empty($useEntries)) {
            // Get all of the  coupons
            $couponEntries = \FrmEntryMeta::getAll([
                'it.field_id' => $codeField
            ]);

            // For each of the unused coupons...
            foreach ($useEntries as $entry) {
                // Get the matching couponEntry
                $couponEntry = array_filter($couponEntries, fn($value) => $value->item_id == $entry->item_id);

                if (1 == count($couponEntry)) {
                    $couponEntry = array_shift($couponEntry);

                    // Find the CCN coupon object
                    /** @var \WSCL\Main\CcnBikes\Entity\Coupon|null */
                    $coupon = $ccnCoupons->getCoupon($couponEntry->meta_value);

                    // If we found the coupon object and it has been used...
                    if (!is_null($coupon) && $coupon->useCount == 1) {
                        // Save the usage
                        \FrmEntryMeta::update_entry_meta($entry->item_id, $useField, null, 1);
                    }
                } else {
                    $this->logger->warning('Search for coupon entries return 0 or >1 results ({id}). Likely an application where no scholarship offer was made.', ['id' => $entry->item_id]);
                }
            }
        }
    }
}
