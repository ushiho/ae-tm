<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('confirmPassword', PasswordType::class)
            ->add('phoneNumber', NumberType::class)
            ->add('role', ChoiceType::class, array(
                'choices' => array(
                    'Admin' => 1,
                    'User'  => 2,
                ),
            ))
            ->add('gender', ChoiceType::class, array(
                'choices' => array(
                    'Female' => 1,
                    'Male' => 2,
                    'None' => 3,
                ),
            ))
            ->add('country', CountryType::class)
            ->add('birthday', DateType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
