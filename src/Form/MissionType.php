<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\Project;
use App\Entity\Department;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('driver', Driver::class)
            ->add('payment', Payment::class)
            ->add('department', Department::class)
            ->add('project', Project::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
