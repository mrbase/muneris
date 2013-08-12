<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeoPostcode
 *
 * @ORM\Table(indexes={@ORM\Index(name="country_zip_index", columns={"country", "zip_code"})})
 * @ORM\Entity(repositoryClass="Muneris\Bundle\GeoPostcodesBundle\Entity\GeoPostcodeRepository")
 */
class GeoPostcode
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=2)
     */
    private $language;

    /**
     * @var integer
     *
     * @ORM\Column(name="sequence", type="integer")
     */
    private $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="region_code", type="string", length=6)
     */
    private $regionCode;

    /**
     * @var string
     *
     * @ORM\Column(name="region_1", type="string", length=60)
     */
    private $region1;

    /**
     * @var string
     *
     * @ORM\Column(name="region_2", type="string", length=60)
     */
    private $region2;

    /**
     * @var string
     *
     * @ORM\Column(name="region_3", type="string", length=60)
     */
    private $region3;

    /**
     * @var string
     *
     * @ORM\Column(name="region_4", type="string", length=60)
     */
    private $region4;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=10)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=60)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="area_1", type="string", length=80)
     */
    private $area1;

    /**
     * @var string
     *
     * @ORM\Column(name="area_2", type="string", length=80)
     */
    private $area2;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="decimal", precision=10, scale=8)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="decimal", precision=10, scale=8)
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="tz", type="string", length=30)
     */
    private $tz;

    /**
     * @var string
     *
     * @ORM\Column(name="utc", type="string", length=10)
     */
    private $utc;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dst", type="boolean")
     */
    private $dst;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return GeoPostcode
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return GeoPostcode
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set sequence
     *
     * @param integer $sequence
     * @return GeoPostcode
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence
     *
     * @return integer
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set regionCode
     *
     * @param string $regionCode
     * @return GeoPostcode
     */
    public function setRegionCode($regionCode)
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    /**
     * Get regionCode
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * Set region1
     *
     * @param string $region1
     * @return GeoPostcode
     */
    public function setRegion1($region1)
    {
        $this->region1 = $region1;

        return $this;
    }

    /**
     * Get region1
     *
     * @return string
     */
    public function getRegion1()
    {
        return $this->region1;
    }

    /**
     * Set region2
     *
     * @param string $region2
     * @return GeoPostcode
     */
    public function setRegion2($region2)
    {
        $this->region2 = $region2;

        return $this;
    }

    /**
     * Get region2
     *
     * @return string
     */
    public function getRegion2()
    {
        return $this->region2;
    }

    /**
     * Set region3
     *
     * @param string $region3
     * @return GeoPostcode
     */
    public function setRegion3($region3)
    {
        $this->region3 = $region3;

        return $this;
    }

    /**
     * Get region3
     *
     * @return string
     */
    public function getRegion3()
    {
        return $this->region3;
    }

    /**
     * Set region4
     *
     * @param string $region4
     * @return GeoPostcode
     */
    public function setRegion4($region4)
    {
        $this->region4 = $region4;

        return $this;
    }

    /**
     * Get region4
     *
     * @return string
     */
    public function getRegion4()
    {
        return $this->region4;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     * @return GeoPostcode
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return GeoPostcode
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set area1
     *
     * @param string $area1
     * @return GeoPostcode
     */
    public function setArea1($area1)
    {
        $this->area1 = $area1;

        return $this;
    }

    /**
     * Get area1
     *
     * @return string
     */
    public function getArea1()
    {
        return $this->area1;
    }

    /**
     * Set area2
     *
     * @param string $area2
     * @return GeoPostcode
     */
    public function setArea2($area2)
    {
        $this->area2 = $area2;

        return $this;
    }

    /**
     * Get area2
     *
     * @return string
     */
    public function getArea2()
    {
        return $this->area2;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return GeoPostcode
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return GeoPostcode
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set tz
     *
     * @param string $tz
     * @return GeoPostcode
     */
    public function setTz($tz)
    {
        $this->tz = $tz;

        return $this;
    }

    /**
     * Get tz
     *
     * @return string
     */
    public function getTz()
    {
        return $this->tz;
    }

    /**
     * Set utc
     *
     * @param string $utc
     * @return GeoPostcode
     */
    public function setUtc($utc)
    {
        $this->utc = $utc;

        return $this;
    }

    /**
     * Get utc
     *
     * @return string
     */
    public function getUtc()
    {
        return $this->utc;
    }

    /**
     * Set dst
     *
     * @param boolean $dst
     * @return GeoPostcode
     */
    public function setDst($dst)
    {
        $this->dst = $dst;

        return $this;
    }

    /**
     * Get dst
     *
     * @return boolean
     */
    public function getDst()
    {
        return $this->dst;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return GeoPostcode
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return GeoPostcode
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
