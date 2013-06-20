<?php

namespace Muneris\Bundle\MaxMindBundle;

use GeoIP2\WebService\Client;
use Psr\Log\LoggerInterface;

class MaxMind
{
    protected $uid;
    protected $key;
    protected $logger;

    public function __construct(LoggerInterface $logger, $uid, $key)
    {
        $this->logger = $logger;
        $this->uid    = $uid;
        $this->key    = $key;
    }

    /**
     * @param string $ip
     * @param string $type
     * @throws \InvalidArgumentException
     * @return object
     */
    public function lookup($ip, $type)
    {
        $client = new Client($this->uid, $this->key);

        if (!method_exists($client, $type)) {
            throw new \InvalidArgumentException("'{$type}' is not a valid call type");
        }

        $this->logger->debug("MaxMind '{$type}' lookup", ['ip' => $ip]);
        $response = $client->$type($ip);

        return $response;
    }
}