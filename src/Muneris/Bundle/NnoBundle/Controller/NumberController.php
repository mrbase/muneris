<?php

namespace Muneris\Bundle\NnoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Stopwatch\Stopwatch;

class NumberController extends Controller
{
    /**
     * @param  string $number
     * @return array
     * @throws NotFoundHttpException
     * @View()
     * @Cache(smaxage="86400")
     */
    public function getNumberAction($number)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $info = $this->get('muneris.nno.service')->findOne($number);

        if (false === $info) {
            throw new NotFoundHttpException("Unknown number '{$number}'");
        }

        $response = [
            'number' => $info,
            '_time'  => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        $jsonp = $this->get('muneris.jsonp.handler');
        if ($jsonp->isJsonpRequest()) {
            return $jsonp->sendResponse($response);
        }

        return $response;
    }
}
