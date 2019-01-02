<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('cin')
            ->add('email', EmailType::class)
            ->add('confirmEmail', EmailType::class)
            ->add('phoneNumber', TelType::class)
            ->add('role', ChoiceType::class, array(
                'label' => 'Role',
                'choices' => array(
                    'Admin' => 1,
                    'User' => 2,
                    'Pompie' => 3,
                ),
            ))
            ->add('gender', ChoiceType::class, array(
                'label' => 'Gender',
                'choices' => array(
                    'Female' => 1,
                    'Male' => 2,
                ),
            ))
            ->add('country', CountryType::class)
            ->add('birthday', DateType::class, array(
                'widget' => 'single_text',
            ))

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
