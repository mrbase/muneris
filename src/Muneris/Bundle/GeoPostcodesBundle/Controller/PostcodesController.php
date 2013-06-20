<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Controller;

use Muneris\GeoPostcodesBundle\Entity\GeoPostcode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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
        $postcodes = $this->getDoctrine()
            ->getRepository('MunerisGeoPostcodesBundle:GeoPostcode')
            ->findBy(['country' => $country])
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException("No zip codes available for '".$country."'");
        }

        $response = ['postcodes' => $postcodes];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }

    /**
     * @param  string $zip_code
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     * @View()
     */
    public function getCountryPostcodeAction($country, $zip_code)
    {
        $postcode = $this->getDoctrine()
            ->getRepository('MunerisGeoPostcodesBundle:GeoPostcode')
            ->findOneBy([
                'zipCode' => $zip_code,
                'country'  => $country
        ]);

        if (!$postcode instanceof GeoPostcode) {
            throw new NotFoundHttpException('Zip code not found');
        }

        $response = ['postcode' => $postcode];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }
}
