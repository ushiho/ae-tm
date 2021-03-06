<?php

namespace App\Form;

use App\Entity\PaymentDriver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PaymentDriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datePayment', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('daysPaid', NumberType::class, array(
                'attr' => [
                    'placeholder' => 'Days paid',
                ],
            ))
            ->add('note', TextareaType::class, [
                    'required' => false,
                    'attr' => [
                        'rows' => '3',
                        'cols' => '60',
                        'placeholder' => 'Some Note about this payment',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaymentDriver::class,
        ]);
    }
}
