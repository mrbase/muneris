<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LocalGeoPostcodeType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country')
            ->add('language')
            ->add('zipCode')
            ->add('city')
            ->add('lat')
            ->add('lng')
            ->add('createdAt', null, ['data' => new \DateTime()])
            ->add('updatedAt')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'      => 'Muneris\Bundle\GeoPostcodesBundle\Entity\LocalGeoPostcode',
            'intention'       => 'localpostcode',
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'localpostcode';
    }
}
