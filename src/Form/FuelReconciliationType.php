<?php

namespace App\Form;

use App\Entity\FuelReconciliation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Entity\Driver;
use App\Entity\Department;
use App\Entity\GasStation;
use App\Entity\Project;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Invoice;

class FuelReconciliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('totalAmount', NumberType::class, array(
                'required' => 'true',
            ))
            ->add('totalLitres', NumberType::class, array(
                'required' => 'true',
            ))
            ->add('kilometrage', NumberType::class, array(
                'required' => 'true',
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
            ->add('driver', EntityType::class, array(
                'required' => false,
                'class' => Driver::class,
                'choice_label' => 'lastName',
                'placeholder' => 'Select a value',
                'attr' => array(
                    'class' => 'bootstrap-select',
                    'data-live-search' => 'true',
                    'data-width' => '100%',
                ),
            ))
            // ->add('department', EntityType::class, array(
            //     'class' => Department::class,
            //     'choice_label' => 'name',
            //     'placeholder' => 'Select a value',
            //     'attr' => array(
            //         'class' => 'bootstrap-select',
            //         'data-live-search' => 'true',
            //         'data-width' => '100%',
            //     ),
            // ))
            ->add('gasStation', EntityType::class, array(
                'required' => false,
                'class' => GasStation::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a value',
                'attr' => array(
                    'class' => 'bootstrap-select',
                    'data-live-search' => 'true',
                    'data-width' => '100%',
                ),
            ))
            // ->add('project', EntityType::class, array(
            //     'class' => Project::class,
            //     'choice_label' => 'name',
            //     'placeholder' => 'Select a value',
            //     'attr' => array(
            //         'class' => 'bootstrap-select',
            //         'data-live-search' => 'true',
            //         'data-width' => '100%',
            //     ),
            // ))
            ->add('vehicle', EntityType::class, array(
                'required' => false,
                'class' => Vehicle::class,
                'choice_label' => 'matricule',
                'placeholder' => 'Select a value',
                'attr' => array(
                    'class' => 'bootstrap-select',
                    'data-live-search' => 'true',
                    'data-width' => '100%',
                ),
            ))
            // ->add('invoice', EntityType::class, array(
            //     'class' => Invoice::class,
            //     'choice_label' => 'N° Invoice',
            //     'placeholder' => 'Select a value',
            //     'attr' => array(
            //         'class' => 'bootstrap-select',
            //         'data-live-search' => 'true',
            //         'data-width' => '100%',
            //     ),
            // ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FuelReconciliation::class,
        ]);
    }
}
