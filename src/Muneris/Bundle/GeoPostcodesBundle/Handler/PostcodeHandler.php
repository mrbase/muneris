<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Handler;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Muneris\Bundle\GeoPostcodesBundle\Entity\LocalGeoPostcode;
use Muneris\Bundle\GeoPostcodesBundle\Form\LocalGeoPostcodeType;
use Muneris\Bundle\JsonpBundle\JsonpHandler;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Stopwatch\Stopwatch;
use Doctrine\Common\Persistence\ObjectManager;

class PostcodeHandler
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * @var JsonpHandler
     */
    protected $jsonp;

    /**
     * @var FormFactory
     */
    protected $form_factory;

    /**
     * @var ObjectRepository
     */
    protected $postcode_repository;

    /**
     * @var \ObjectRepository
     */
    protected $local_postcode_repository;

    /**
     * @param ObjectManager $om
     * @param JsonpHandler  $jsonp
     * @param FormFactory   $form_factory
     * @param string        $postcode_entity
     * @param string        $local_postcode_entity
     */
    public function __construct(ObjectManager $om, JsonpHandler $jsonp, FormFactory $form_factory, $postcode_entity, $local_postcode_entity)
    {
        $this->om                        = $om;
        $this->jsonp                     = $jsonp;
        $this->form_factory              = $form_factory;
        $this->postcode_repository       = $om->getRepository($postcode_entity);
        $this->local_postcode_repository = $om->getRepository($local_postcode_entity);
    }


    /**
     * @param LocalGeoPostcode $postcode
     * @param array            $parameters
     * @param string           $method
     * @param string           $format
     * @return View|Response
     */
    public function processLocal(LocalGeoPostcode $postcode, array $parameters, $method, $format)
    {
        $state = $postcode->getId() ? Codes::HTTP_NO_CONTENT : Codes::HTTP_CREATED;
        $form  = $this->form_factory->create('localpostcode', $postcode);
        $form->submit($parameters);

        if ($form->isValid()) {
            $this->om->persist($postcode);
            $this->om->flush();

            // on PUT and PATCH return 204 and not redirect response.
            if ('POST' !== $method) {
                return new Response('', Codes::HTTP_NO_CONTENT);
            }

            return View::createRouteRedirect('get_country_postcode', [
                'country'  => $postcode->getCountry(),
                'zip_code' => $postcode->getZipCode(),
                '_format'  => $format,
            ], $state);
        }

        return View::create($form, 400);
    }


    /**
     * Perform fuzzy search
     *
     * @param $country
     * @param $search
     * @return array|JsonResponse
     */
    public function fuzzySearch($country, $search)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        try {
            $postcodes = $this->tryLocalFuzzy($country, $search);
        } catch (NotFoundHttpException $e) {
            $postcodes = $this->tryGlobalFuzzy($country, $search);
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'     => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        if ($this->jsonp->isJsonpRequest()) {
            return $this->jsonp->sendResponse($response);
        }

        return $response;
    }


    /**
     * @param $country
     * @param $city
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function citySearch($country, $city)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $postcodes = $this
            ->postcode_repository
            ->findBy([
                'city'    => urldecode($city),
                'country' => $country
            ])
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('City code not found');
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'     => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        if ($this->jsonp->isJsonpRequest()) {
            return $this->jsonp->sendResponse($response);
        }

        return $response;

    }


    /**
     * @param $country
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function countrySearch($country)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $postcodes = $this
            ->postcode_repository
            ->findCities([':country' => $country])
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException("No zip codes available for '".$country."'");
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'     => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        if ($this->jsonp->isJsonpRequest()) {
            return $this->jsonp->sendResponse($response);
        }

        return $response;
    }


    /**
     * @param $country
     * @param $fuzzy
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function countryFuzzySearch($country, $fuzzy)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('lookup');

        $postcodes = $this
            ->postcode_repository
            ->findByFuzzy($country, $fuzzy)
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('City code not found');
        }

        $response = [
            'postcodes' => $postcodes,
            '_time'     => $stopwatch->stop('lookup')->getDuration().'ms',
        ];

        if ($this->jsonp->isJsonpRequest()) {
            return $this->jsonp->sendResponse($response);
        }

        return $response;
    }

    public function findLocalById($id)
    {
        return $this->local_postcode_repository->find($id);
    }

    /**
     * Try to find postal code in the locale database
     *
     * @param string $country
     * @param string $search
     * @return array
     * @throws NotFoundHttpException
     */
    private function tryLocalFuzzy($country, $search)
    {
        $postcodes = $this->local_postcode_repository
            ->findByFuzzy($country, $search)
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('Zip code not found in locale db.');
        }

        return $postcodes;
    }


    /**
     * Try to find postal code in the global database (aka. GeoPostalcodes databasen)
     *
     * @param string $country
     * @param string $search
     * @return array
     * @throws NotFoundHttpException
     */
    private function tryGlobalFuzzy($country, $search)
    {
        $postcodes = $this->postcode_repository
            ->findByFuzzy($country, $search)
        ;

        if (0 == count($postcodes)) {
            throw new NotFoundHttpException('Zip code not found in global db.');
        }

        return $postcodes;
    }
}
