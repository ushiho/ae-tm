<?php

namespace App\Form;

use App\Entity\PaymentDriver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentDriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datePayment')
            ->add('price')
            ->add('totalPrice')
            ->add('pricePaid')
            ->add('period')
            ->add('remainingPrice')
            ->add('note')
            ->add('driver')
            ->add('payment')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentDriver::class,
        ]);
    }
}
