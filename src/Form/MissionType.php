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

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('driver', EntityType::class, [
                'class'        => Driver::class,
                'choice_label' => 'driver',
                'required'     => true,
                'placeholder'  => 'choose driver',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.firstName', 'ASC');
                },
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'department',
                'required' => true,
                'placeholder' => 'choose department',
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('d')
                            ->orderBy('d.name', 'ASC');
                },
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'project',
                'required' => true,
                'placeholder' => 'link a project',
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('p')
                            ->orderBy('p.name', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
