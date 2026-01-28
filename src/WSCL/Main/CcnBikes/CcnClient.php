<?php
declare(strict_types = 1);
namespace WSCL\Main\CcnBikes;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\UriTemplate\UriTemplate;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RCS\Json\JsonClientTrait;
use WSCL\Main\CcnBikes\Entity\Coupon;
use WSCL\Main\CcnBikes\Entity\CouponAlt;
use WSCL\Main\CcnBikes\Entity\CouponsResponse;
use WSCL\Main\CcnBikes\Entity\IdentityAttributeTuple;
use WSCL\Main\CcnBikes\Entity\IdentityAttributesResp;
use WSCL\Main\CcnBikes\Entity\MemberDetails;
use WSCL\Main\CcnBikes\Entity\MembershipListResponse;
use WSCL\Main\CcnBikes\Entity\MembershipOrg;
use WSCL\Main\CcnBikes\Entity\MembershipOrgConfig;
use WSCL\Main\CcnBikes\Entity\MembershipOrgListing;
use WSCL\Main\CcnBikes\Entity\MembershipOrgReportData;
use WSCL\Main\CcnBikes\Entity\MembershipOrgResp;
use WSCL\Main\CcnBikes\Entity\ReportStartResponse;
use WSCL\Main\CcnBikes\Entity\ReportStatusResponse;
use WSCL\Main\CcnBikes\Enums\MembershipStatusEnum;
use WSCL\Main\CcnBikes\Enums\ReportStateEnum;
use WSCL\Main\CcnBikes\Filter\CcnAuthenticateFilter;
use WSCL\Main\CcnBikes\Json\CcnFactoryRegistry;
use WSCL\Main\CachedCookieJar;
use GuzzleHttp\Cookie\CookieJar;


class CcnClient
{
    use JsonClientTrait;

//     private const USER_AGENT_PARTS = array (
//         'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
//         'AppleWebKit/605.1.15 (KHTML, like Gecko)',
//         'Chrome/140.0.7339.186',
//         'Safari/605.1.15'
//     );

    private const CACHE_TTL = 600;

    private Client $client;
    private Client $downloadClient;
    private string $restHost;

    public function __construct(
        CcnBikesOptionsInterface $options,
        CacheInterface $cache,
        private LoggerInterface $logger
        )
    {
        $this->initJsonClientTrait($cache, self::CACHE_TTL, CcnFactoryRegistry::withPhpClassesAdded(true));

        $uri = new Uri($options->getCcnRestApiUrl());
        $this->restHost = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

        $logMiddleware =
//            null;
            \GuzzleHttp\Middleware::log(
                $this->logger,
                new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG),
                'debug'
                );

        $this->client = self::getHttpClient(
            $options->getCcnRestApiUrl(),
            $options->getCcnUsername(),
            $options->getCcnPassword(),
            $cache,
            $logMiddleware,
            $logger
            );

        $downloadStack = HandlerStack::create();
//         $downloadStack->push($logMiddleware);

        /**
         * Create a seperate client for downloading files as they don't come
         * directory from ccnbikes.com.
         */
        $this->downloadClient = new Client(array(
            'handler' => $downloadStack,
            RequestOptions::VERIFY => true,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::DECODE_CONTENT => 'gzip, deflate',
            RequestOptions::HEADERS => array(
//                'User-Agent' => sprintf('"%s"', join(' ', self::USER_AGENT_PARTS)),
                )
            )
        );
    }

    private static function getHttpClient(
        string $url,
        string $username,
        string $password,
        ?CacheInterface $cache = null,
        ?callable $logMiddleware = null,
        LoggerInterface $logger = null): Client
    {
        $uri = new Uri($url);
        $cookieJar = is_null($cache) ? new CookieJar() : new CachedCookieJar($cache, 'CcnClientCookies');

        $stack = HandlerStack::create();

        $restHost = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        $referer = sprintf('%s/management/tools/', $restHost);

        $client = new Client(array(
            'base_uri' => $uri,
            'handler' => $stack,
            RequestOptions::VERIFY => true,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::COOKIES => $cookieJar,
            RequestOptions::DECODE_CONTENT => 'gzip, deflate',
            RequestOptions::HEADERS => array(
//                'User-Agent' => sprintf('"%s"', join(' ', self::USER_AGENT_PARTS)),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Referer' => $referer
            )
        )
            );

        $stack->push(
            new CcnAuthenticateFilter(
                $client,
                $cookieJar,
                $username,
                $password,
                $logger
                )
            );

        if (!is_null($logMiddleware)) {
            $stack->push($logMiddleware);
        }

        return $client;
    }

    public static function areCredentialsValid(string $url, string $username, string $password): bool
    {
        $result = false;

        $client = self::getHttpClient($url, $username, $password);

        $resp = $client->get('membership-organizations/accessible_list/?page=1');

        if ($resp->getStatusCode() == 200) {
            $result = true;
        }

        return $result;
    }

    /**
     * Fetch the set of Identity Attributes.
     *
     * I.e. Attributes for People
     *
     * @return IdentityAttributesResp
     */
    public function getIdentityAttributes(): ?IdentityAttributesResp
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__);
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                'identity-attributes/?application=management_tools&page_size=1000&viewable_to_identity_type=HUMAN',
                [
                    RequestOptions::QUERY => [
                        'application' => 'management_tools',
                        'page_size' => 1000,
                        'viewable_to_identity_type' => 'HUMAN'
                    ]
                ]
                );

            if ($resp->getStatusCode() == 200) {
                /** @var IdentityAttributesResp */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new IdentityAttributesResp(),
                    $cacheKey
                    );
            } else {
                $this->logger->error(
                    'Error getting Identity Attributes: {code}/{msg}',
                    array (
                        'code' => $resp->getStatusCode(),
                        'msg' => $resp->getReasonPhrase()
                        )
                    );
            }
        }

        return $result;
    }

    /**
     * Save a set of Identity Attributes for someone.
     *
     * @param int $identity Identifier for a CCN person. Usually looks like 100381289
     * @param IdentityAttributeTuple[] $attributes
     */
    public function setIdentityAttributes(int $identity, array $attributes): bool
    {
        $result = false;

        $resp = $this->client->put(
            UriTemplate::expand('identities/{identity}/attribute_values/', array('identity' => $identity)),
            array (
                RequestOptions::JSON => array ('attribute_values' => $attributes)
            )
            );

        if ($resp->getStatusCode() == 200) {
            $result = true;
        }

        return $result;
    }


    /**
     * Fetch a page of Membership Organizations
     *
     * @param int $pageNum The page of organizations to return, based on the
     *      current pagination. Page numbers start at 1.
     *
     * @return MembershipOrgResp|null
     */
    public function getMembershipOrganizations(int $pageNum): ?MembershipOrgResp
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($pageNum));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                'membership-organizations/accessible_list/',
                array(
                    RequestOptions::QUERY => array('page' => $pageNum)
                    )
                );

            if ($resp->getStatusCode() == 200) {
                /** @var MembershipOrgResp */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new MembershipOrgResp(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    /**
     *
     * @return MembershipOrg[]
     */
    public function getMembershipOrgs(): array
    {
        /** @var MembershipOrg[] */
        $orgs = array();

        $pageNum = 1;

        do {
            /** @var MembershipOrgResp|NULL */
            $memberOrgsResp = $this->getMembershipOrganizations($pageNum);

            if (null != $memberOrgsResp) {
                $orgs = array_merge($orgs, $memberOrgsResp->results);
            }
            $pageNum++;
        } while (!is_null($memberOrgsResp) && $memberOrgsResp->hasNext());

        return $orgs;
    }


    public function getMembershipOrganizationConfig(int $orgId): ?MembershipOrgConfig
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($orgId));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                UriTemplate::expand('membership-organizations/config/{orgId}', array('orgId' => $orgId)),
                );

            if ($resp->getStatusCode() == 200) {
                /** @var MembershipOrgConfig */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new MembershipOrgConfig(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    public function getMembershipOrganizationListing(string $slug): ?MembershipOrgListing
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, strval($slug));
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                UriTemplate::expand('listing_app/association-listings/{slug}/', array('slug' => $slug)),
                );

            if ($resp->getStatusCode() == 200) {
                /** @var MembershipOrgResp */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new MembershipOrgListing(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    public function getMembershipReportData(int $orgId): ?MembershipOrgReportData
    {
        $result = null;

        $resp = $this->client->get(
            'report/report/report_dashboard_data/',
            array (
                RequestOptions::QUERY => array (
                    'access_level' => 'view',
                    'object_id' => $orgId,
                    'object_type' => 'morganization',
                    'standalone' => 1
                )
            )
            );

        if ($resp->getStatusCode() == 200) {
            /** @var MembershipOrgReportData */
            $result = $this->processJsonResponse(
                (string) $resp->getBody(),
                new MembershipOrgReportData()
                );
        }

        return $result;
    }

    public function startReport(string $updateLink): ?ReportStartResponse
    {
        $result = null;

        $resp = $this->client->get($this->restHost . $updateLink);

        if ($resp->getStatusCode() == 200) {
            /** @var ReportStartResponse */
            $result = $this->processJsonResponse(
                (string) $resp->getBody(),
                new ReportStartResponse()
                );
        }

        return $result;
    }

    public function waitForReport(string $taskUUID, int $waitTime): ReportStateEnum
    {
        $state = ReportStateEnum::STARTED;
        $abortTime = microtime(true) + $waitTime;

        // Pause while CCN starts the report. If we ask for status too
        // too fast we may get a 404 response.
        if ($abortTime > microtime()) {
            $state = $this->getReportState($taskUUID);
        }

        return $state;
    }

    public function getReportState(string $taskUUID): ReportStateEnum
    {
        $state = ReportStateEnum::STARTED;

        $resp = $this->client->get(
            UriTemplate::expand('task_app/tasks/{uuid}/', array ('uuid' => $taskUUID))
            );

        if (200 == $resp->getStatusCode()) {
            /** @var ReportStatusResponse */
            $statusObj = $this->processJsonResponse(
                (string) $resp->getBody(),
                new ReportStatusResponse()
                );

            $state = $statusObj->state;
        }

        return $state;
    }

    public function downloadFile(string $url): ?string
    {
        $result = null;

        $tmpFile = tempnam(sys_get_temp_dir(), 'CCN_');
        rename($tmpFile, $tmpFile .= '.csv');

        $resp = $this->downloadClient->get(
            $url,
            array (
                RequestOptions::SINK => $tmpFile
                )
            );

        if (200 == $resp->getStatusCode()) {
            $result = $tmpFile;
        } else {
            $this->logger->error(
                'Error downloading file from {url}: {code}/{msg}',
                array (
                    'url' => $url,
                    'code' => $resp->getStatusCode(),
                    'msg' => $resp->getReasonPhrase()
                )
                );
            unlink($tmpFile);
        }

        return $result;
    }

    /**
     *
     * @param int $orgId
     * @param MembershipStatusEnum[] $statuses
     * @param int $pageNum
     *
     * @return MembershipListResponse|NULL
     */
    public function getMembershipList(int $orgId, array $statuses, int $pageNum): ?MembershipListResponse
    {
        $result = null;
        $statusStrs = array();

        foreach ($statuses as $status) {
            $statusStrs[] = $status->value;
        }

        $resp = $this->client->get(
            'membership_app/identity-memberships/accessible_list/',
            array(
                RequestOptions::QUERY => array (
                    'membership_organization_ids' => $orgId,
                    'membership_status' => join(',', $statusStrs),
                    'page' => $pageNum
                )
            )
            );

        if ($resp->getStatusCode() == 200) {
            /** @var MembershipListResponse */
            $result = $this->processJsonResponse(
                (string) $resp->getBody(),
                new MembershipListResponse()
                );
        }

        return $result;
    }

    public function getMemberDetails(int $memberId): ?MemberDetails
    {
        $result = null;

        $resp = $this->client->get(
            UriTemplate::expand('membership_app/identity-memberships/{memberId}/', array ('memberId' => $memberId))
            );

        if ($resp->getStatusCode() == 200) {
            /** @var MemberDetails */
            $result = $this->processJsonResponse(
                (string) $resp->getBody(),
                new MemberDetails()
                );
        }

        return $result;
    }

    public function findCoupons(string $search): ?CouponsResponse
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__, $search);
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                'coupons/',
                array(
                    RequestOptions::QUERY => array(
                        'search' => $search
                    )
                )
            );

            if ($resp->getStatusCode() == 200) {
                /** @var CouponsResponse */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new CouponsResponse(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    public function getCoupon(int $id): ?Coupon
    {
        $result = null;

        $resp = $this->client->get(
            UriTemplate::expand('coupons/{couponId}/', array ('couponId' => $id))
            );

        if ($resp->getStatusCode() == 200) {
            /** @var Coupon */
            $result = $this->processJsonResponse(
                (string) $resp->getBody(),
                new Coupon()
                );
        }

        return $result;
    }

    public function getCoupons(): ?CouponsResponse
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__);
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get(
                'coupons/',
                array(
                    RequestOptions::QUERY => array(
                        'page' => 1,
                        'page_size' => 1000
                    )
                )
                );

            if ($resp->getStatusCode() == 200) {
                /** @var CouponsResponse */
                $result = $this->processJsonResponse(
                    (string) $resp->getBody(),
                    new CouponsResponse(),
                    $cacheKey
                    );
            }
        }

        return $result;
    }

    public function createCoupon(Coupon $coupon): ?CouponAlt
    {
        $result = null;

        $resp = $this->client->post(
            'coupons/',
            [
                RequestOptions::JSON => $coupon
            ]
            );

        if ($resp->getStatusCode() == 201) {
            /** @var CouponAlt */
            $result = $this->processJsonResponse(
                (string) $resp->getBody(),
                new CouponAlt()
                );
        } else {
            $this->logger->error(
                'Error creating coupon: {code}/{msg}',
                array (
                    'code' => $resp->getStatusCode(),
                    'msg' => $resp->getReasonPhrase()
                )
                );
        }

        return $result;
    }

    public function updateCoupon(Coupon $coupon): bool
    {
        $result = false;

        $resp = $this->client->put(
            UriTemplate::expand('coupons/{couponId}/', array ('couponId' => $coupon->getId())),
            [
                RequestOptions::JSON => $coupon
            ]
            );

        if ($resp->getStatusCode() == 200) {
            $result = true;
        } else {
            $this->logger->error(
                'Error updating coupon: {code}/{msg}',
                array (
                    'code' => $resp->getStatusCode(),
                    'msg' => $resp->getReasonPhrase()
                )
                );
        }

        return $result;
    }
}
