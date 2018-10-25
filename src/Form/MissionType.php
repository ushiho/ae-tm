<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Project;
use App\Form\DriverType;
use App\Entity\Department;
use App\Form\DepartmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
                'attr' => [
                    'rows' => '3',
                    'cols' => '60',
                    'formnovalidate' => 'true',
                    'novalidate' => 'novalidate',
                ]
            ))
            ->add('department',EntityType::class,array(
                'class' => Department::class,
                'required'=>true,
                'choice_label' => 'name',
                'placeholder' => '--Choose the department--',
                'attr' => array(
                    'style' => 'width:250px;',
                    'class' => 'selectDepa',
                )
            ))
            ->add('finished', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false,
                ),
                'placeholder' => '--Select a state--',
                'attr' => array(
                    'style' => 'width:250px',
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
