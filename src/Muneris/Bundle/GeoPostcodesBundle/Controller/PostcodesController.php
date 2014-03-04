<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Controller;

use Muneris\Bundle\GeoPostcodesBundle\Entity\GeoPostcode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Stopwatch\Stopwatch;

class PostcodesController extends Controller
{
    /**
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @View()
     * @Cache(smaxage="86400")
     */
    public function getCountryPostcodesAction($country)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $postcodes = $this->getDoctrine()
            ->getRepository('MunerisGeoPostcodesBundle:GeoPostcode')
            ->findCities([':country' => $country])
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException("No zip codes available for '".$country."'");
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'     => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }

    /**
     * @param  string $country
     * @param  string $zip_code
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     * @View()
     */
    public function getCountryPostcodeAction($country, $zip_code)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        switch (strtoupper($country)) {
            case 'SE':
                $zip_code = [
                    $zip_code,
                    substr($zip_code, 0, (strlen($zip_code) -2)).' '.substr($zip_code, -2)
                ];
                break;
        }

        $postcodes = $this->getDoctrine()
            ->getRepository('MunerisGeoPostcodesBundle:GeoPostcode')
            ->findCities([
                ':zipCode' => $zip_code,
                ':country' => $country
        ]);

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('Zip code not found');
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'    => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }

    /**
     * @param  string $city
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     * @View()
     */
    public function getCountryCityAction($country, $city)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $postcodes = $this->getDoctrine()
            ->getRepository('MunerisGeoPostcodesBundle:GeoPostcode')
            ->findBy([
                'city'    => urldecode($city),
                'country' => $country
        ]);

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('City code not found');
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'    => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }

    /**
     * @param  string $fuzzy
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     * @View()
     */
    public function getCountryFuzyAction($country, $fuzzy)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $postcodes = $this->getDoctrine()
            ->getRepository('MunerisGeoPostcodesBundle:GeoPostcode')
            ->findByFuzzy($country, $fuzzy)
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('City code not found');
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'    => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }
}
