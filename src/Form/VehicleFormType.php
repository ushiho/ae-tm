<?php

namespace App\Form;

use App\Entity\Vehicle;
use App\Entity\VehicleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class VehicleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reg', TextType::class)
            ->add('mileage', NumberType::class)
            ->add('type', EntityType::class, array(
                'class' => VehicleType::class,
                'placeholder' => '--Select the type--',
                'required' => true,
                'choice_label' => 'name',
                'attr' => [
                    'style' => 'width:270px',
                    ]
            ))
            ->add('brand', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}
