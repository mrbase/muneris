<?php

namespace Muneris\Bundle\NnoBundle\Nno\Soap;

/**
 * NavneNumreBasis class
 *
 * @author    ulrik nielsen <ulrik@bellcom.dk>
 * @copyright bellcom open source aps
 * @package   integration
 */
class Client extends \SoapClient
{

    private static $classmap = array(
        'SearchQuestion'   => 'Muneris\Bundle\NnoBundle\Nno\Soap\SearchQuestion',
        'SearchIDQuestion' => 'Muneris\Bundle\NnoBundle\Nno\Soap\nnoSearchIDQuestion',
        'SubscriberResult' => 'Muneris\Bundle\NnoBundle\Nno\Soap\nnoSubscriberResult',
        'Subscriber'       => 'Muneris\Bundle\NnoBundle\Nno\Soap\nnoSubscriber',
    );

    public function  __construct($wsdl = "http://tunnel.nno.dk/NNService/1.0/NNService.wsdl", array $options = array('trace' => 1))
    {
        foreach(self::$classmap as $key => $value) {
            if(!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        parent::__construct($wsdl, $options);
    }

    /**
     *
     *
     * @param SearchQuestion $Question_1
     * @return SubscriberResult
     */
    public function lookupSubscribers(SearchQuestion $Question_1)
    {
        return $this->__soapCall('lookupSubscribers', array($Question_1), array(
            'uri' => 'http://tunnel.nno.dk/NNService/1.0',
            'soapaction' => ''
        ));
    }

    /**
     *
     *
     * @param SearchIDQuestion $Question_1
     * @return SubscriberResult
     */
    public function lookupSubscribersByID(SearchIDQuestion $Question_1)
    {
        return $this->__soapCall('lookupSubscribersByID', array($Question_1), array(
            'uri' => 'http://tunnel.nno.dk/NNService/1.0',
            'soapaction' => ''
        ));
    }

}
