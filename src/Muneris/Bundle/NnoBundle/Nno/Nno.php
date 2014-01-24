<?php

namespace Muneris\Bundle\NnoBundle\Nno;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

use Muneris\Bundle\NnoBundle\Nno\Soap\Client;
use Muneris\Bundle\NnoBundle\Nno\Soap\SearchQuestion;
use Muneris\Bundle\NnoBundle\Nno\Soap\nnoSubscriber;
use Muneris\Bundle\NnoBundle\Nno\Soap\nnoSubscriberResult;

Class Nno
{
    /**
     * @var string
     */
    var $api_key;

    /**
     * @var LoggerInterface
     */
    var $logger;


    /**
     * Constructor
     *
     * @param string          $api_key Api key provided by NNO
     * @param LoggerInterface $logger  Logger class
     */
    public function __construct($api_key, LoggerInterface $logger)
    {
        $this->api_key = $api_key;
        $this->logger = $logger;
    }


    /**
     * Perform the lookup against NNO's webservice.
     *
     * @param  mixed $number            Phone number to lookup
     * @return mixed                    Returns subscribers information if found, else false
     * @throws InvalidArgumentException If the $number is not a valid danish phonenumber
     */
    public function lookup($number)
    {
        $number = preg_replace('/[^0-9]+/', '', $number);

        if (substr($number, 0, 4) == '0045') {
            $number = substr($number, 4);
        } elseif (strlen($number) > 8 && (substr($number, 0, 2) == '45')) {
            $number = substr($number, 2);
        }

        if (strlen($number) != 8) {
            throw new InvalidArgumentException('Phone numbers in Denmark is always 8 digits');
        }

        $lookup = new SearchQuestion();
        $lookup->phone = $number;
        $lookup->username = $this->api_key;

        $this->logger->info('Looking up: '.$number.' via NNO service.');

        $nno = new Client();
        $result = $nno->lookupSubscribers($lookup);

        if (($result instanceof nnoSubscriberResult) && (count($result->subscribers) == 1)) {
            return $result->subscribers;
        }

        return false;
    }

    public function findOne($number)
    {
        $result = $this->lookup($number);
        if (($result) &&
            (count($result) == 1) &&
            ($result[0] instanceof nnoSubscriber)
        ) {
            $address = (array) $result[0];
            unset($address['_t_d_c__p_i_d'], $address['TDC_PID']);

            // if there is no surname, we parse the christian name
            if (empty($address['surname'])) {
                $address['surname'] = '';

                if (strpos($address['christianname'], ' ') > 0) {
                    $names = explode(' ', $address['christianname']);
                    $address['surname'] = array_pop($names);
                    $address['christianname'] = implode(' ', $names);
                }
            }

            return $address;
        }

        return false;
    }
}
