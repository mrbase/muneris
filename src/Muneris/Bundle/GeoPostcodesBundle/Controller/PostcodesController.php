<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use Muneris\Bundle\GeoPostcodesBundle\Entity\LocalGeoPostcode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\View\View AS FOSView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostcodesController extends FOSRestController
{
    /**
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @View()
     *
     * @ApiDoc(
     *   resource     = true,
     *   description  = "Get all zip codes in a given country.",
     *   output       = "Muneris\Bundle\GeoPostcodesBundle\Entity\GeoPostcode",
     *   requirements = {
     *     {"name"="country", "description"="Iso2 country code.", "dataType"="string"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the resource is not found."
     *   }
     * )
     *
     * @Cache(smaxage="86400")
     */
    public function getCountryPostcodesAction($country)
    {
        return $this
            ->container
            ->get('muneris_geo_postcodes.postcode.handler')
            ->countrySearch($country)
        ;
    }


    /**
     * @param  string $country
     * @param  string $zip_code
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     *
     * @ApiDoc(
     *   resource     = true,
     *   description  = "Search for zip code in a given country.",
     *   output       = "Muneris\Bundle\GeoPostcodesBundle\Entity\GeoPostcode",
     *   requirements = {
     *     {"name"="country", "description"="Iso2 country code.", "dataType"="string"},
     *     {"name"="zip_code", "description"="Zip code to lookup", "dataType"="string"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the resource is not found."
     *   }
     * )
     *
     * @View()
     */
    public function getCountryPostcodeAction($country, $zip_code)
    {
        return $this
            ->container
            ->get('muneris_geo_postcodes.postcode.handler')
            ->fuzzySearch($country, $zip_code)
        ;
    }


    /**
     * @param  string $city
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     *
     * @ApiDoc(
     *   resource     = true,
     *   description  = "Search for city names in a given country.",
     *   output       = "Muneris\Bundle\GeoPostcodesBundle\Entity\GeoPostcode",
     *   requirements = {
     *     {"name"="country", "description"="Iso2 country code.", "dataType"="string"},
     *     {"name"="city", "description"="City name to lookup", "dataType"="string"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the resource is not found."
     *   }
     * )
     *
     * @View()
     */
    public function getCountryCityAction($country, $city)
    {
        return $this
            ->container
            ->get('muneris_geo_postcodes.postcode.handler')
            ->citySearch($country, $city)
        ;
    }

    /**
     * @param  string $fuzzy
     * @param  string $country
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     *
     * @ApiDoc(
     *   resource     = true,
     *   description  = "Do funkey search for postal codes for a given country.",
     *   output       = "Muneris\Bundle\GeoPostcodesBundle\Entity\GeoPostcode",
     *   requirements = {
     *     {"name"="country", "description"="Iso2 country code.", "dataType"="string"},
     *     {"name"="fuzzy", "description"="Postal code and/or city name to lookup", "dataType"="string"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the resource is not found."
     *   }
     * )
     *
     * @View()
     */
    public function getCountryFuzyAction($country, $fuzzy)
    {
        return $this
            ->container
            ->get('muneris_geo_postcodes.postcode.handler')
            ->fuzzySearch($country, $fuzzy)
        ;
    }


    /**
     * @return string
     * @ApiDoc(
     *   resource    = true,
     *   description = "Creates a new page from the submitted data.",
     *   input       = "Muneris\Bundle\GeoPostcodesBundle\Form\LocalGeoPostcodeType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @View(
     *  template    = "MunerisGeoPostcodesBundle:Postcodes:new.html.twig",
     *  statusCode  = Codes::HTTP_BAD_REQUEST,
     *  templateVar = "form"
     * )
     */
    public function postPostcodeAction()
    {
        return $this->processForm(new LocalGeoPostcode());
    }


    /**
     * Update existing page from the submitted data or create a new page at a specific location.
     *
     * @ApiDoc(
     *   resource    = true,
     *   description = "Updating an existing postcode.",
     *   input       = "Muneris\Bundle\GeoPostcodesBundle\Form\LocalGeoPostcodeType",
     *   statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors",
     *     404 = "Returned if the postal code is not found in the local namespace."
     *   }
     * )
     *
     * @View(
     *  template    = "MunerisGeoPostcodesBundle:Postcodes:new.html.twig",
     *  templateVar = "form"
     * )
     *
     * @param int     $id      the page id
     *
     * @return FormTypeInterface|View
     *
     * @throws NotFoundHttpException when page not exist
     */
    public function putPostcodeAction($id)
    {
        if (null === $postcode = $this->container->get('muneris_geo_postcodes.postcode.handler')->findLocalById($id)) {
            throw new NotFoundHttpException('No such postcode.');
        }

        return $this->processForm($postcode);
    }


    /**
     * Form processing for working with the Local copies of postcodes.
     *
     * @param  LocalGeoPostcode $postcode
     * @return \FOS\RestBundle\View\View|int
     */
    private function processForm(LocalGeoPostcode $postcode)
    {
        $request = $this->getRequest();

        return $this
            ->container
            ->get('muneris_geo_postcodes.postcode.handler')
            ->processLocal(
                $postcode,
                $request->request->all(),
                $request->getMethod(),
                $request->get('_format')
        );
    }


    /**
     * Presents the form to use to create a new postcode.
     *
     * @ApiDoc(
     *   resource    = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @View()
     *
     * @return FormTypeInterface
     */
    public function newPostcodeAction()
    {
        return $this->createForm('localpostcode', new LocalGeoPostcode());
    }

    /**
     * @param  int $id
     * @return array
     * @throws NotFoundHttpException
     * @Cache(smaxage="86400")
     *
     * @ApiDoc(
     *   resource     = true,
     *   description  = "Fetch a local postcode by it's id.",
     *   output       = "Muneris\Bundle\GeoPostcodesBundle\Entity\LocalGeoPostcode",
     *   requirements = {
     *     {"name"="id", "description"="Local id to lookup", "dataType"="int"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the resource is not found."
     *   }
     * )
     *
     * @View()
     */
    public function getPostcodeAction($id)
    {
        if (null === $postcode = $this->container->get('muneris_geo_postcodes.postcode.handler')->findLocalById($id)) {
            throw new NotFoundHttpException('No such postcode.');
        }

        return $postcode;
    }

    /**
     * Deletes a local postcode.
     *
     * @param  int $id
     * @return array
     * @throws NotFoundHttpException
     *
     * @ApiDoc(
     *   resource     = true,
     *   description  = "Delete local postcode by it's id.",
     *   requirements = {
     *     {"name"="id", "description"="Local id to delete", "dataType"="int"}
     *   },
     *   statusCodes = {
     *     204 = "Returned when successfully deleted",
     *     404 = "Returned when the resource is not found."
     *   }
     * )
     *
     * @View()
     */
    public function deletePostcodeAction($id)
    {
        if (null === $postcode = $this->container->get('muneris_geo_postcodes.postcode.handler')->findLocalById($id)) {
            throw new NotFoundHttpException('No such postcode.');
        }

        $om = $this->getDoctrine()->getManager();
        $om->remove($postcode);
        $om->flush();

        return $this->view(null, Codes::HTTP_NO_CONTENT);
    }
}
