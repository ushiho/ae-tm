<?php

namespace App\Form;

use App\Entity\PaymentSupplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PaymentSupplierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datePayment', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('price', NumberType::class, array(
                'attr' => [
                    'placeholder' => 'Price Paid (DH)'
                ]
            ))
            ->add('note', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => '3',
                    'cols' => '60',
                    'placeholder' => 'Some Note about this payment',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentSupplier::class,
        ]);
    }
}
