<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Driver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PaymentExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('project', EntityType::class, array(
            'class' => Project::class,
            'required' => true,
            'choice_label' => 'name',
            'placeholder' => '--Choose the project--',
        ))
        ->add('fileType', ChoiceType::class, array(
            'choices' => array(
                'EXCEL' => 1,
                'PDF'=> 2,
            ),
            'label' => 'File Type',
            'placeholder' => 'File Type',
            'required' => true,
            'multiple' => false,
        ))
        ->add('driver', EntityType::class, array(
            'class' => Driver::class,
            'required' => true,
            'choice_label' => function (Driver $driver) {
                return $driver->getLastName().' - '.$driver->getFirstName().' - '.$driver->getCin().' - '.$this->driverVehicleType($driver->getVehicleType()->toArray());
            },
            'placeholder' => 'Select a value',
            'attr' => array(
            'class' => 'bootstrap-select',
            'data-live-search' => 'true',
            'data-width' => '100%',
            'style' => 'cursor: pointer;',
            ),
        ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    public function driverVehicleType(array $vehcileTypes)
    {
        $types = ' ';
        foreach ($vehcileTypes as $type) {
            $types .= $type->getName().', ';
        }

        return $types;
    }
}
