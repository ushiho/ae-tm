<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Supplier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExportPaymentSupplierType extends AbstractType
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
        ->add('supplier', EntityType::class, array(
            'class' => Supplier::class,
            'required' => true,
            'choice_label' => function (Supplier $supplier) {
                return $supplier->getLastName().' - '.$supplier->getFirstName();
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
