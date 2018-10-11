<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Project;
use App\Entity\Department;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('note', TextareaType::class, array(
                'attr' => [
                    'rows' => '3',
                    'cols' => '80',
                    'formnovalidate' => 'true',
                    'novalidate' => 'novalidate',
                ]
            ))
            ->add('project',EntityType::class,array(
                'class' => Project::class,
                'required'=>true,
                'choice_label' => 'name',
                'placeholder' => 'Link a project',
                'attr' => array(
                    'class'=>'bootstrap-select',
                    'data-live-search'=>'true',
                    'data-width'=>'100%',
                    'style' => 'width:200px;'
                )
            ))
            ->add('department',EntityType::class,array(
                'class' => Department::class,
                'required'=>true,
                'choice_label' => 'name',
                'placeholder' => 'Choose the department',
                'attr' => array(
                    'class'=>'bootstrap-select',
                    'data-live-search'=>'true',
                    'data-width'=>'100%',
                    'style' => 'width:200px;'
                )
            ))
            ->add('driver',EntityType::class,array(
                'class' => Driver::class,
                'required'=>true,
                'choice_label' => 'firstName',
                'placeholder' => 'Choose the driver',
                'attr' => array(
                    'class'=>'bootstrap-select',
                    'data-live-search'=>'true',
                    'data-width'=>'100%',
                    'style' => 'width:200px;'
                )
            ))
            ->add('finished', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false,
                ),
                'placeholder' => '--Select a state--',
                'attr' => array(
                    'style' => 'width:200px',
                )
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
