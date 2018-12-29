<?php

namespace App\Form;

use App\Entity\Allocate;
use App\Entity\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
            ])
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
                ],
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
                ],
            ))
            ->add('note', TextareaType::class, array(
                'required' => false,
                'attr' => [
                    'rows' => '3',
                    'cols' => '60',
                    'formnovalidate' => 'true',
                    'novalidate' => 'novalidate',
                ],
                ))
            ->add('supplier', EntityType::class, array(
                'class' => Supplier::class,
                'placeholder' => '--Choose a supplier--',
                'required' => true,
                'choice_label' => 'firstName',
                'attr' => [
                    'style' => 'width:250px',
                ],
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
