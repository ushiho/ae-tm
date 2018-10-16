<?php

namespace App\Form;

use App\Entity\Mission;
use App\Entity\Vehicle;
use App\Entity\Allocate;
use App\Entity\Supplier;
use Doctrine\DBAL\Types\DecimalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('period', ChoiceType::class, array(
                'choices' => [
                    'Daily' => 1,
                    'Weekly' => 2,
                    'Monthly' => 3,
                ],
                'placeholder' => '--Select a period--',
                'required' => true,
                'attr' => [
                    'style' => 'width:250px',
                ]
            ))
            ->add('price', NumberType::class)
            ->add('withDriver', ChoiceType::class, array(
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'placeholder' => '-- Select --',
                'attr' => [
                    'style' => 'width:250px',
                ]
            ))
            ->add('note', TextareaType::class, array(
                'attr' => [
                    'rows' => '3',
                    'cols' => '60'
                ]
            ))
            ->add('supplier', EntityType::class, array(
                'class' => Supplier::class,
                'placeholder' => '--Choose a supplier--',
                'required' => true,
                'choice_label' => 'firstName',
                'attr' => [
                    'style' => 'width:250px',
                ]
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Allocate::class,
        ]);
    }
}
