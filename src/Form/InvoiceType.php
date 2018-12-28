<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', null, array(
                'label' => 'Invoice N°',
                'attr' => array(
                    'class' => 'form-control',
                ),
            ))
            ->add('totalLitres', null, array(
                'label' => 'Number of liters',
                'attr' => array(
                    'class' => 'form-control',
                ),
            ))
            ->add('totalAmounts', null, array(
                'label' => 'Amount',
                'attr' => array(
                    'class' => 'form-control',
                ),
            ))
            ->add('createdAt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                ),
            ])
            ->add('isPaid', ChoiceType::class, [
                'label' => 'Is Paid',
                'choices' => [
                    'NO' => false,
                    'YES' => true,
                ],
                'attr' => array(
                    'class' => 'form-control',
                ),
            ])
            ->add('excelFile', TextType::class, array(
                'required' => true,
                'label' => 'Excel FileName',
                'attr' => array(
                    'class' => 'form-control',
                ),
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Invoice',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_invoice';
    }
}
