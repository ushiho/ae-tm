<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\VehicleType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\ORM\Query\Expr\Select;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email', EmailType::class)
            ->add('numberPhone', TelType::class)
            ->add('cin')
            ->add('adress', TextareaType::class, array(
                'attr' => array(
                    'cols' => 35,
                    'rows' => 2,
                )
            ))
            ->add('licenceNumber')
            ->add('gender', ChoiceType::class, array(
                'label' => 'Gender',
                'choices' => array(
                    'Femal' => 1,
                    'Male' => 2,
                ),
                'attr' => array(
                    'style'=>'width:270px;',
                ),
                'placeholder' => '--Select Gender--',
                'required' => true,
                'multiple' => false,
                
            ))
            ->add('vehicleType', EntityType::class, array(
                'class' => VehicleType::class,
                'placeholder' => '--Select the type--',
                'required' => true,
                'choice_label' => 'name',
                'attr' => [
                    'style' => 'width:270px;height:50px;',
                ],
                'multiple' => true,
                'by_reference' => false,
            ))
            ->add('salaire', NumberType::class, array(
                'attr' => array(
                    'style' => 'width:265px;',
                )
            ))
            ->add('periodOfTravel', ChoiceType::class, array(
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
            'data_class' => Driver::class,
        ]);
    }
}
