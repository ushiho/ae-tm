<?php

namespace App\Form;

use App\Entity\PaymentSupplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentSupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datePayment')
            ->add('price')
            ->add('totalPricePaid')
            ->add('totalPriceToPay')
            ->add('remainingPrice')
            ->add('note')
            ->add('payment')
            ->add('allocate')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentSupplier::class,
        ]);
    }
}
