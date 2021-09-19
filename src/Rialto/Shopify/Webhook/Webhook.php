<?php

namespace Rialto\Shopify\Webhook;


/**
 * A Shopify webhook.
 */
class Webhook
{
    private $id = null;
    private $topic;
    private $address;
    private $format;

    public function __construct($topic, $address, $format = 'json')
    {
        $this->topic = $topic;
        $this->address = $address;
        $this->format = $format;
    }

    /** @return Webhook */
    public static function fromArray(array $data)
    {
        $hook = new self($data['topic'], $data['address'], $data['format']);
        $hook->id = $data['id'];
        return $hook;
    }

    public function toArray()
    {
        return array_filter([
            'id' => $this->id,
            'topic' => $this->topic,
            'address' => $this->address,
            'format' => $this->format,
        ]);
    }

    public function __toString()
    {
        return sprintf("%s => %s", $this->topic, $this->address);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getFormat()
    {
        return $this->format;
    }
}
