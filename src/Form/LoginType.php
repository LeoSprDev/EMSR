<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', TextType::class, [
                'label' => 'Login',
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Votre nom d\'utilisateur',
                    'autofocus' => true
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre login.'])
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Votre mot de passe'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez saisir votre mot de passe.'])
                ]
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label' => 'Se souvenir de moi',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Cette form n'est pas liée à une entité
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }
}