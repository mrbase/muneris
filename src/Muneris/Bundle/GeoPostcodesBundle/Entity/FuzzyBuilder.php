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
        $this->fuzzy   = $fuzzy;
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

        if (preg_match('/^[a-z -]+$/i', $this->fuzzy)) {
            $this->qb->andWhere('g.city LIKE :city');
            $this->params[':city'] = $this->fuzzy;

            return $this->qb;
        }

        $or = $this->qb->expr()->orX();

        if (!preg_match('/^[0-9 -]+$/', $this->fuzzy)) {
            $this->params[':city'] = $this->fuzzy;
            $or->add($this->qb->expr()->like('g.city', ':city'));
        }

        $this->params[':zip'] = $this->fuzzy;
        $or->add($this->qb->expr()->like('g.zipCode', ':zip'));

        switch (strtoupper($this->country)) {
            case 'FI':
                // in Finland zip codes is always 5 digits with left padded zeros
                $or->add($this->qb->expr()->like('g.zipCode', ':zip1'));
                $this->params[':zip1'] = sprintf('%05d', $this->fuzzy);
                break;

            case 'GB':
                $or->add($this->qb->expr()->like('g.zipCode', ':zip1'));
                $this->params[':zip1'] = str_replace(' ', '', $this->fuzzy);
                break;

            case 'NL':
                if (!preg_match('/^[0-9]+$/', $this->fuzzy)) {
                    $or->add($this->qb->expr()->like('g.zipCode', ':zip1'));
                    $this->params[':zip1'] = substr($this->fuzzy, 0, (strlen($this->fuzzy) -2)).' '.substr($this->fuzzy, -2);

                }
                break;

            case 'PT':
                // in Portugal they use - as binder, id " " is send we convert it.
                $or->add($this->qb->expr()->like('g.zipCode', ':zip1'));
                $this->params[':zip1'] = str_replace(' ', '-', $this->fuzzy);
                break;

            case 'GR':
            case 'SE':
                // Sweeden and Greese uses the format "xxx xx" for zip codes, if a number is send, we convert it.
                if (preg_match('/^[0-9]+$/', $this->fuzzy)) {
                    $or->add($this->qb->expr()->like('g.zipCode', ':zip1'));
                    $this->params[':zip1'] = substr($this->fuzzy, 0, (strlen($this->fuzzy) -2)).' '.substr($this->fuzzy, -2);
                }
                break;
        }

        $this->qb->andWhere($or);

        return [
            $this->qb, $this->params
        ];
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
}
