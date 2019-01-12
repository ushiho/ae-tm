<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExportPaymentType extends AbstractType
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
        ->add('project', EntityType::class, array(
            'class' => Project::class,
            'required' => true,
            'choice_label' => 'name',
            'placeholder' => '--Choose the project--',
        ))
        ->add('paymentOf', ChoiceType::class, [
            'choices' => [
                'Driver' => 1,
                'Supplier' => 2,
            ],
            'label' => 'Payment Of',
            'placeholder' => '--Select a Payment--',
            'required' => true,
            'multiple' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
