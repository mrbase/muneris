<?php

namespace Muneris\Bundle\MaxMindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

class IpController extends Controller
{
    /**
     * @param  Request $request
     * @param  string  $ip
     * @return array
     * @throws NotFoundHttpException
     * @View()
     * @Cache(smaxage="86400")
     */
    public function getCityAction(Request $request, $ip)
    {
        // TODO:
        //  - use $info->queriesRemaining to keep track of quota
        //  - implement cache

        $info = $this->get('muneris.max_mind.service')->lookup($ip, 'city');

        $data = [
            'city' => [
                'name' => $info->city->name,
                'zip_code' => $info->postal->code,
                'continent' => $info->continent->name,
                'location' => [
                    'longitude' => $info->location->latitude,
                    'latitude'  => $info->location->latitude,
                    'time_zone' => $info->location->timeZone,
                ]
            ]
        ];

        $response = ['city' => $data];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }

    /**
     * @param  string $ip
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     * @View()
     * @Cache(smaxage="86400")
     */
    public function getOmniAction($ip)
    {
        throw new Exception('Omni lookup not implemented yet.');

        $info = $this->get('muneris.max_mind.service')->lookup($ip, 'omni');

        return ['omni' => $info];
    }

    /**
     * @param  string $ip
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     * @View()
     * @Cache(smaxage="86400")
     */
    public function getCountryAction($ip)
    {
        throw new Exception('Country lookup not implemented yet.');

        $info = $this->get('muneris.max_mind.service')->lookup($ip, 'country');

        return ['country' => $info];
    }

    /**
     * @param  string $ip
     * @return array
     * @throws NotFoundHttpException
     * @throws Exception
     * @View()
     * @Cache(smaxage="86400")
     */
    public function getCityisporgAction($ip)
    {
        throw new Exception('cityIspOrg lookup not implemented yet.');

        $info = $this->get('muneris.max_mind.service')->lookup($ip, 'cityIspOrg');

        return ['cityisporg' => $info];
    }
}
