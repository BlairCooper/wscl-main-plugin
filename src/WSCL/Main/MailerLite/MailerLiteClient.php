<?php
declare(strict_types = 1);
namespace WSCL\Main\MailerLite;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\UriTemplate\UriTemplate;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RCS\Json\JsonClientTrait;
use RCS\WP\WpMail\WpMailWrapper;
use WSCL\Main\MailerLite\Entity\Field;
use WSCL\Main\MailerLite\Entity\Group;
use WSCL\Main\MailerLite\Entity\Subscriber;
use WSCL\Main\MailerLite\Enums\SubscriberType;
use WSCL\Main\MailerLite\Filter\MailerLiteAuthenticateFilter;
use WSCL\Main\MailerLite\Filter\MailerLiteThrottleFilter;
use WSCL\Main\MailerLite\Json\MailerLiteFactoryRegistry;
use WSCL\Main\CachedCookieJar;

class MailerLiteClient
{
    use JsonClientTrait;

    private const OFFSET = "offset";
    private const LIMIT = "limit";
    private const QUERY = "query";
    private const REST_URI = "https://api.mailerlite.com/api/v2/";

    private const CACHE_TTL = 10;

    private LoggerInterface $logger;
    private Client $client;

    public function __construct(
        private MailerLiteOptionsInterface $options,
        CacheInterface $cache,
        LoggerInterface $logger
        )
    {
        $this->initJsonClientTrait($cache, self::CACHE_TTL, MailerLiteFactoryRegistry::withPhpClassesAdded(true));

        $this->logger = $logger;

        $this->client = self::getHttpClient(
            $options->getMailerLiteApiKey(),
            $cache
//             ,Middleware::log(
//                 $this->logger,
//                 new MessageFormatter(MessageFormatter::DEBUG),
//                 'debug'
//                 )
            );
    }

    private static function getHttpClient(string $authKey, ?CacheInterface $cache = null, callable $logMiddleware = null): Client
    {
        $cookieJar = is_null($cache) ? new CookieJar() : new CachedCookieJar($cache, 'MailerLiteCacheKey');

        $stack = HandlerStack::create();

        $client = new Client([
            'base_uri' => self::REST_URI,
            'handler' => $stack,
            RequestOptions::VERIFY => true,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::COOKIES => $cookieJar,
            RequestOptions::HEADERS => [
                'User-Agent' => 'washgintonleague.org/Washington Student Cycling League'
                ]
            ]
        );

        $stack->push(new MailerLiteAuthenticateFilter($authKey));
        $stack->push(new MailerLiteThrottleFilter());

        if (!is_null($logMiddleware)) {
            $stack->push($logMiddleware);
        }

        return $client;
    }

    public static function isValidApiKey(string $authKey): bool
    {
        $result = false;

        try {
            $client = self::getHttpClient($authKey);

            $resp = $client->get('webooks');

            if (200 == $resp->getStatusCode()) {
                $json = json_decode((string) $resp->getBody());

                if (isset($json->webhooks)) {
                    $result = true;
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $ce) {
        }

        return $result;
    }

//     public function hasValidApiKey(): bool
//     {
//         $result = false;

//         try {
//             $resp = $this->client->get('webooks');

//             if (200 == $resp->getStatusCode()) {
//                 $json = json_decode((string) $resp->getBody());

//                 if (isset($json->webhooks)) {
//                     $result = true;
//                 }
//             }
//         } catch (\GuzzleHttp\Exception\ClientException $ce) {
//         }

//         return $result;
//     }

    /**
     * Fetch the field definitions.
     *
     * @return Field[]|NULL An array of Field objects or null if the request
     *      failed.
     */
    public function getFields(): ?array
    {
        $cacheKey = $this->getCacheKey(__FUNCTION__);
        $result = $this->cache->get($cacheKey);

        if (!isset($result)) {
            $resp = $this->client->get('fields');

            if ($resp->getStatusCode() == 200) {
                /** @var Field[] */
                $result = $this->processJsonResponse((string) $resp->getBody(), new Field(), $cacheKey);
            }
        }

        return $result;
    }

    /**
     * Fetch some number of subscribers starting at a particular offset.
     *
     * @param int $offset The offset from the beginning of the list of
     *      subscribers. Defaults to 0;
     * @param int $limit The number of subscribers to retrieve. Defaults to
     *      100 and is capped at 5000.
     *
     * @return Subscriber[]|null An array of Subscriber objects or null if
     *      the request failed.
     */
    public function getSubscribers(int $offset = 0, int $limit = 100): ?array
    {
        $result = null;

        if ($limit > 5000) {
            $limit = 5000;
        }

        $resp = $this->client->get(
            'subscribers',
            array (
                RequestOptions::QUERY => array (
                    self::OFFSET => $offset,
                    self::LIMIT => $limit
                )
            )
            );

        if ($resp->getStatusCode() == 200) {
            /** @var Subscriber[] */
            $result = $this->processJsonResponse((string) $resp->getBody(), new Subscriber());
        }

        return $result;
    }

    /**
     * Fetch a subscriber by email address.
     *
     * @param string $email An email address
     *
     * @return Subscriber|null The subscriber or null if they were not found.
     */
    public function findSubscriber(string $email): ?Subscriber
    {
        $result = null;

        $resp = $this->client->get(
            'subscribers/search',
            array (
                RequestOptions::QUERY => array (
                    self::QUERY => $email
                )
            )
            );

        if ($resp->getStatusCode() == 200) {
            /** Subscriber[] */
            $subs = $this->processJsonResponse((string) $resp->getBody(), new Subscriber());

            if (is_array($subs) && 1 == count($subs)) {
                $result = array_shift($subs);
            }
        }

        return $result;
    }

    public function updateSubscriberFields(Subscriber $subscriber): ?Subscriber
    {
        $resultSub = null;

        // Check for empty updates
        if (null == $subscriber->getName() ||
            null == $subscriber->getId() ||
            0 == strlen(trim($subscriber->getName())) ||
            0 == count($subscriber->getFields())
            ) {
                $resultSub = $subscriber;
            } else {
                $json = array();
                $fields = array();

                foreach ($subscriber->getFields() as $field) {
                    $fields[$field->key] = $field->value;
                }

                $json['name'] = $subscriber->getName();
                $json['fields'] = $fields;
                $json['resend_autoresponders'] = false;

                $resp = $this->client->put(
                    UriTemplate::expand('subscribers/{id}', array('id' => $subscriber->getId())),
                    array(
                        RequestOptions::JSON => $json
                    )
                    );

                if (200 != $resp->getStatusCode()) {
                    $this->logger->error(
                        'Unable to update subscriber ({first} {last} / {email}) in MailerLite: {err}',
                        array (
                            'first' => $subscriber->getName(),
                            'last' => $subscriber->getLastName() ?? 'nln',
                            'email' => $subscriber->getEmail() ?? 'nea',
                            'err' => (string)$resp->getBody()
                            )
                        );
                } else {
                    $resultSub = $this->processJsonResponse((string) $resp->getBody(), new Subscriber());
                }
            }

            return $resultSub;
    }


    /**
     * Add a subscriber to the system.
     *
     * @param Subscriber $subscriber A subscriber object.
     *
     * @return Subscriber|NULL The saved subscriber or null if the request failed.
     */
    public function addSubscriber(Subscriber $subscriber): ?Subscriber
    {
        $result = null;

        $fieldsJson = array();
        foreach ($subscriber->getFields() as $field) {
            $fieldsJson[$field->key] = $field->value;
        }

        $json = array();
        $json['email'] = $subscriber->getEmail();
        $json['name'] = $subscriber->getName();
        $json['fields'] = $fieldsJson;
        $json['resubscribe'] = false;
        $json['type'] = SubscriberType::ACTIVE->value;

        $resp = $this->client->post(
            'subscribers',
            array(
                RequestOptions::JSON => $json
            )
            );

        if (200 == $resp->getStatusCode()) {
            $result = $this->processJsonResponse((string) $resp->getBody(), new Subscriber());
        } else {
            $msg = sprintf(
                'Unable to add subscriber (%s %s / %s) in MailerLite: %s',
                $subscriber->getName(),
                $subscriber->getLastName(),
                $subscriber->getEmail(),
                (string)$resp->getBody()
                );
            $this->logger->error($msg);

            $result = (new WpMailWrapper($this->logger))
                ->addTo($this->options->getDeveloperEmailAddress())
                ->setSubject('Unable to add subscriber to MailerLite')
                ->setPlainBody($msg)
                ->setFrom($this->options->getDeveloperEmailAddress(), 'MailerLite Cron Error')
                ->sendMessage()
            ;
        }

        return $result;
    }

    /**
     * Fetch a list of groups.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return Group[]|null An array of Groups or null if the request failed.
     */
    public function getGroups(int $offset = 0, int $limit = 100): ?array
    {
        $result = null;

        if ($limit > 5000) {
            $limit = 5000;
        }

        $resp = $this->client->get(
            'groups',
            array (
                RequestOptions::QUERY => array (
                    self::OFFSET => $offset,
                    self::LIMIT => $limit
                )
            )
            );

        if ($resp->getStatusCode() == 200) {
            /** @var Group[] */
            $result = $this->processJsonResponse((string) $resp->getBody(), new Group());
        }

        return $result;
    }

    /**
     * Fetch a group by name.
     *
     * @param string $groupName The name of a group
     *
     * @return Group|NULL A Group or null if the request failed.
     */
    public function getGroup(string $groupName): ?Group
    {
        $result = null;

        $json = array();
        $json['group_name'] = $groupName;

        $resp = $this->client->post(
            'groups/search',
            array (
                RequestOptions::JSON => $json
                )
            );

        if ($resp->getStatusCode() == 200) {
            /** Group[] */
            $groups = $this->processJsonResponse((string) $resp->getBody(), new Group());

            if (is_array($groups) && 1 == count($groups)) {
                $result = array_shift($groups);
            }
        }

        return $result;
    }

    /**
     * Fetch the subscribers for a group.
     *
     * @param int $groupId The group id.
     * @param int $offset An offset into the list of subscribers. Defaults to
     *      0.
     * @param int $limit A limit of how many subscribers to return. Defaults
     *      to 500. The maxiumum allowed is 5000.
     *
     * @return Subscriber[]|NULL An array of Subscriber objects or null if the request fails.
     */
    public function getGroupSubscribers(int $groupId, int $offset = 0, int $limit = 500): ?array
    {
        $result = null;

        if ($limit > 5000) {
            $limit = 5000;
        }

        $resp = $this->client->get(
            UriTemplate::expand('groups/{groupId}/subscribers', array('groupId' => $groupId)),
            array (
                RequestOptions::QUERY => array (
                    self::OFFSET => $offset,
                    self::LIMIT => $limit
                )
            )
            );

        if ($resp->getStatusCode() == 200) {
            /** @var Subscriber[] */
            $result = $this->processJsonResponse((string) $resp->getBody(), new Subscriber());
        }

        return $result;
    }

    public function assignSubscriberToGroup(Subscriber $subscriber, string $groupName): ?Subscriber
    {
        $resultSub = null;

        $json = array();
        $json['group_name'] = $groupName;

        $resp = $this->client->post(
            UriTemplate::expand(
                'groups/group_name/subscribers/{subId}/assign',
                array(
                    'subId' => $subscriber->getId()
                )
            ),
            array (
                RequestOptions::JSON => $json
            )
            );

        if (200 != $resp->getStatusCode()) {
            $this->logger->error(
                'Unable to add subscriber ({first} {last} / {email}) to group ({groupName}) in MailerLite: {err}',
                array (
                    'first' => $subscriber->getName(),
                    'last' => $subscriber->getLastName(),
                    'email' => $subscriber->getEmail(),
                    'groupName' => $groupName,
                    'err' => (string)$resp->getBody()
                )
                );
        } else {
            $resultSub = $this->processJsonResponse((string) $resp->getBody(), new Subscriber());
        }

        return $resultSub;
    }

    /**
     *
     * @param int $groupId
     * @param string[] $emailList
     */
    public function assignSubscribersToGroup(int $groupId, array $emailList): void
    {
        $json = array();
        $json['subscribers'] = [];

        foreach ($emailList as $email) {
            $obj = new \stdClass();
            $obj->email = $email;

            $json['subscribers'][] = $obj;
        }

        $resp = $this->client->post(
            UriTemplate::expand(
                'groups/{groupId}/subscribers/import',
                array(
                    'groupId' => $groupId
                )
                ),
            array (
                RequestOptions::JSON => $json
            )
            );

        if (200 != $resp->getStatusCode()) {
            $this->logger->error(
                'Unable to add assign subscribers to group ({groupId}) in MailerLite: {err}',
                array (
                    'groupId' => $groupId,
                    'err' => (string)$resp->getBody()
                )
                );
        }
    }


    /**
     * Create a new field.
     *
     * @param Field $field The field to be created.
     *
     * @return Field|null The saved field, or null if the request failed.
     */
    public function addField(Field $field): ?Field
    {
        $result = null;

        $resp = $this->client->post(
            'fields',
            array (
                RequestOptions::JSON => $field
                )
            );

        if ($resp->getStatusCode() == 201) {
            /** @var Field */
            $result = $this->processJsonResponse((string) $resp->getBody(), new Field());
        }

        return $result;
    }

    /**
     * Create a new group.
     *
     * @param Group $group The group to be created.
     *
     * @return Group|null The saved group, or null if the request failed.
     */
    public function addGroup(Group $group): ?Group
    {
        $result = null;

        $resp = $this->client->post(
            'groups',
            array (
                RequestOptions::JSON => $group
            )
            );

        if ($resp->getStatusCode() == 201) {
            /** @var Group */
            $result = $this->processJsonResponse((string) $resp->getBody(), new Group());
        }

        return $result;
    }
}
