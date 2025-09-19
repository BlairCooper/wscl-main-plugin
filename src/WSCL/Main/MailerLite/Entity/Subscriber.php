<?php
declare(strict_types=1);
namespace WSCL\Main\MailerLite\Entity;

use RCS\Json\JsonEntity;
use WSCL\Main\MailerLite\Enums\FieldType;
use WSCL\Main\MailerLite\Enums\SubscriberType;

class Subscriber extends JsonEntity implements \JsonSerializable
{
    public const EMAIL = 'email';
    public const NAME = 'name';
    public const LAST_NAME = 'last_name';
    public const ADDRESS = 'address';
    public const CITY = 'city';
    public const STATE = 'state';
    public const ZIP = 'zip';
    public const PHONE = 'phone';

    public const STUDENT = 'student';
    public const PARENT  = 'parent';
    public const COACH   = 'coach';

    public int $id;
    public string $email;
    public string $name;
    public int $sent;
    public int $opened;
    public int $clicked;
    public SubscriberType $type;
    public \DateTime $dateCreated;    // "2017-05-23 14:50:03",
    public ?\DateTime $dateUpdated;
    public ?\DateTime $dateSubscribed;
    public ?\DateTime $dateUnsubscribed;

    /** @var SubscriberField[] */
    public array $fields;

    public function __construct()
    {
        $this->fields = array();
    }

    /**
     * Helper to keep default constructor.
     *
     * @param string $email
     * @param string $name
     *
     * @return self
     */
    public static function create(string $email, string $name): self
    {
        $class = get_called_class();
        $sub = new $class();

        $sub->setEmail($email);
        $sub->setName($name);

        return $sub;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->addField(new SubscriberField(self::NAME, FieldType::TEXT, $name));
    }

    public function getEmail(): ?string
    {
        // If the email isn't set, try setting it from the 'email' field.
        if (null == $this->email) {
            $this->email = $this->getFieldValue(self::EMAIL);
        }

        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = strtolower($email);
        $this->addField(new SubscriberField(self::EMAIL, FieldType::TEXT, $this->email));
    }

    public function getLastName(): ?string
    {
        return $this->getFieldValue(self::LAST_NAME);
    }

    public function getType(): SubscriberType
    {
        return $this->type;
    }

    public function setType(SubscriberType $type): void
    {
        $this->type = $type;
    }

    /**
     *
     * @return SubscriberField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $key): ?SubscriberField
    {
        $result = null;

        foreach ($this->fields as $field) {
            if ($key == $field->key) {
                $result = $field;
                break;
            }
        }

        return $result;
    }

    public function getFieldValue(string $key): ?string
    {
        $result = null;

        $field = $this->getField($key);

        if (!is_null($field)) {
            $result = $field->value;
        }

        return $result;
    }

    public function addField(SubscriberField $field): void
    {
        // filter the existing fields to those without the incoming field.
        $this->fields = array_filter($this->fields, fn($entry) => $entry->key != $field->key);
        // Add the incoming field.
        $this->fields[] = $field;
    }

    public function jsonSerialize(): mixed
    {
        $fieldsJson = array_reduce(
            $this->fields,
            function ($result, $entry) { $result[$entry->key] = $entry->value; return $result; },
            array()
            );

        $json = [
            'email' => $this->email,
            'name' => $this->name,
            'fields' => $fieldsJson,
            'resend_autoresponders' => false
        ];

        if (isset($this->id)) {
            $json['id'] = $this->id;
        }

        return $json;
    }

    public function __toString()
    {
        return sprintf('Subscriber: %s (%s)', $this->email, $this->name);
    }

    public function hashcode(): string
    {
        return hash('sha256', json_encode($this->jsonSerialize()));
    }
}
