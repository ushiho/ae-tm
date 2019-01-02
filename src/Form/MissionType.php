<?php

namespace App\Form;

use App\Entity\Mission;
use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MissionType extends AbstractType
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
            ->add('note', TextareaType::class, array(
                'required' => false,
                'attr' => [
                    'rows' => '3',
                    'cols' => '60',
                    'formnovalidate' => 'true',
                    'novalidate' => 'novalidate',
                ],
                ))
            ->add('department', EntityType::class, array(
                'class' => Department::class,
                'required' => true,
                'choice_label' => 'name',
                'placeholder' => '--Choose the department--',
                'attr' => array(
                    'style' => 'width:270px;',
                    'class' => 'selectDepa',
                ),
            ))
            ->add('salaire', NumberType::class)
            ->add('periodOfWork', ChoiceType::class, array(
                'label' => 'Period Of Workd',
                'choices' => array(
                    'Daily' => 1,
                    'Weekly' => 2,
                    'Monthly' => 3,
                ),
                'placeholder' => '--Select a period--',
                'required' => true,
                'attr' => [
                    'style' => 'width:270px',
                ],
                'multiple' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
