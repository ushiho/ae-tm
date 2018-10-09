<?php

namespace App\Form;

use App\Entity\Allocate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate')
            ->add('endDate')
            ->add('period')
            ->add('price')
            ->add('withDeiver')
            ->add('createdAt')
            ->add('note')
            ->add('supplier')
            ->add('vehicle')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Allocate::class,
        ]);
    }
}
