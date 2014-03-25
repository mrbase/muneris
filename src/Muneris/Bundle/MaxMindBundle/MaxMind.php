<?php

namespace Muneris\Bundle\MaxMindBundle;

use GeoIp2\WebService\Client;
use Muneris\Bundle\MaxMindBundle\Entity\MaxMindCache;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;

class MaxMind
{
    protected $uid;
    protected $key;
    protected $logger;

    public function __construct(LoggerInterface $logger, EntityManager $entity_manager, $uid, $key)
    {
        $this->logger = $logger;
        $this->em     = $entity_manager;
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
        $cache = $this->em->getRepository('MunerisMaxMindBundle:MaxMindCache')->findByIp($ip);

        if ($cache && (1 === count($cache))) {
            return $cache[0]->getData();
        }

        $client = new Client($this->uid, $this->key);

        if (!method_exists($client, $type)) {
            throw new \InvalidArgumentException("'{$type}' is not a valid call type");
        }

        $this->logger->debug("MaxMind '{$type}' lookup", ['ip' => $ip]);
        $data = $client->$type($ip);

        $response = [
            'ip' => [
                'ip'   => $ip,
                'ipv4' => ip2long($ip),
            ],
            'name' => $data->city->name,
            'zip_code' => $data->postal->code,
            'country'  => [
                'code' => $data->country->isoCode,
                'name' => $data->country->name,
            ],
            'continent' => [
                'name' => $data->continent->name,
                'code' => $data->continent->code,
            ],
            'location' => [
                'longitude' => $data->location->longitude,
                'latitude'  => $data->location->latitude,
                'time_zone' => $data->location->timeZone,
            ],
        ];

        $this->em
            ->createQuery("DELETE FROM MunerisMaxMindBundle:MaxMindCache i WHERE i.ip = :ip")
            ->setParameter('ip', $ip)
            ->execute()
        ;

        $cache = new MaxMindCache();
        $cache->setIp($ip);
        $cache->setData($response);
        $cache->setCreatedAt(new \DateTime());
        $this->em->persist($cache);
        $this->em->flush();

        return $response;
    }
}
