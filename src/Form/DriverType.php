<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\VehicleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('numberPhone')
            ->add('cin')
            ->add('adress')
            ->add('licenceNumber')
            ->add('gender', ChoiceType::class, array(
                'label' => 'Gender',
                'choices' => array(
                    'Femal' => 1,
                    'Male' => 2,
                ),
                'attr' => array(
                    'style'=>'width:200px;',
                )
            ))
            ->add('vehicleType', EntityType::class, array(
                'class' => VehicleType::class,
                'placeholder' => '--Select the type--',
                'required' => true,
                'choice_label' => 'name',
                'attr' => [
                    'style' => 'width:200px',
                    ]
            ))
            ->add('salaire')
            ->add('periodOfTravel', ChoiceType::class, array(
                'choices' => array(
                    'Daily' => 1,
                    'Weekly' => 2,
                    'Monthly' => 3,
                ),
                'placeholder' => '--Select a period--',
                'required' => true,
                'attr' => [
                    'style' => 'width:200px',
                ],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Driver::class,
        ]);
    }
}
