<?php
/**
 * Created by JetBrains PhpStorm.
 * User: un
 * Date: 19/06/13
 * Time: 22.15
 * To change this template use File | Settings | File Templates.
 */

namespace Muneris\Bundle\JsonpBundle;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JsonpHandler
{
    protected $request;

    /**
     * Construct
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Figure out whether or not the request is a jsonp request
     *
     * @return bool
     */
    public function isJsonpRequest()
    {
        if (('json' == $this->request->getRequestFormat()) && $this->request->query->has('callback')) {
            return true;
        }

        return false;
    }

    /**
     * Return Jsonp encoded Response object
     *
     * @param $data
     * @return JsonResponse
     */
    public function sendResponse($data)
    {
        $response = new JsonResponse($data);
        $response->setCallback($this->request->query->get('callback'));
        return $response;
    }
}