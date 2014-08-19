<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Entity;

use Doctrine\ORM\QueryBuilder;

class FuzzyBuilder
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $qb;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $fuzzy;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @param QueryBuilder $qb
     * @param string       $country
     * @param string       $fuzzy
     */
    public function __construct(QueryBuilder $qb, $country, $fuzzy)
    {
        $this->qb      = $qb;
        $this->country = $country;

        $this->fuzzy   = array_map(function($v) {
            return trim(str_replace('*', '%', $v));
        }, explode(',', $fuzzy));

        error_log(print_r($this->fuzzy,1));
    }


    /**
     * Fuzz the query
     * Currently it handles FI, GB, GR, MT, PT AND SV
     *
     * @param array $params
     * @return QueryBuilder
     */
    public function fuzz(array $params)
    {
        $this->params = $params;

        foreach ($this->fuzzy as $fuzz) {
            $result = $this->build($fuzz);
        }

        return $result;
    }

    /**
     * @return QueryBuilder
     */
    public function getBuilder()
    {
        return $this->qb;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    protected function build($fuzz)
    {
        static $loop = 0;
        $loop++;

        if (preg_match('/^[a-z -]+$/i', $fuzz)) {
            $this->qb->andWhere('g.city LIKE :city'.$loop);
            $this->params[':city'.$loop] = $fuzz;

            return $this->qb;
        }

        $or = $this->qb->expr()->orX();

        if (!preg_match('/^[0-9 -]+$/', $fuzz)) {
            $this->params[':city'.$loop] = $fuzz;
            $or->add($this->qb->expr()->like('g.city', ':city'.$loop));
        }

        $this->params[':zip'.$loop] = $fuzz;
        $or->add($this->qb->expr()->like('g.zipCode', ':zip'.$loop));

        $loop++;
        switch (strtoupper($this->country)) {
            case 'FI':
                // in Finland zip codes is always 5 digits with left padded zeros
                $or->add($this->qb->expr()->like('g.zipCode', ':zip'.$loop));
                $this->params[':zip'.$loop] = sprintf('%05d', $fuzz);
                break;

            case 'GB':
                $or->add($this->qb->expr()->like('g.zipCode', ':zip'.$loop));
                $this->params[':zip'.$loop] = str_replace(' ', '', $fuzz);
                break;

            case 'NL':
                if (!preg_match('/^[0-9]+$/', $fuzz)) {
                    $or->add($this->qb->expr()->like('g.zipCode', ':zip'.$loop));
                    $this->params[':zip'.$loop] = substr($fuzz, 0, (strlen($fuzz) -2)).' '.substr($fuzz, -2);

                }
                break;

            case 'PT':
                // in Portugal they use - as binder, id " " is send we convert it.
                $or->add($this->qb->expr()->like('g.zipCode', ':zip'.$loop));
                $this->params[':zip'.$loop] = str_replace(' ', '-', $fuzz);
                break;

            case 'GR':
            case 'SE':
                // Sweeden and Greese uses the format "xxx xx" for zip codes, if a number is send, we convert it.
                if (preg_match('/^[0-9]+$/', $fuzz)) {
                    $or->add($this->qb->expr()->like('g.zipCode', ':zip'.$loop));
                    $this->params[':zip'.$loop] = substr($fuzz, 0, (strlen($fuzz) -2)).' '.substr($fuzz, -2);
                }
                break;
        }

        $this->qb->andWhere($or);

        return [
            $this->qb, $this->params
        ];
    }
}
