<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('login', TextType::class, [
                'label' => 'Login',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom d\'utilisateur'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le login ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le login doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le login ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de famille (optionnel)'
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prénom (optionnel)'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Ressources Humaines' => 'ROLE_RH',
                    'Service Informatique' => 'ROLE_INFO',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Sélectionnez un ou plusieurs rôles pour cet utilisateur.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Au moins un rôle doit être sélectionné.'])
                ]
            ]);

        // Mot de passe : obligatoire en création, optionnel en édition
        if (!$isEdit) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'new-password'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le mot de passe ne peut pas être vide.']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ])
                ]
            ]);
        } else {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Laissez vide pour conserver l\'actuel'
                ],
                'help' => 'Laissez ce champ vide si vous ne souhaitez pas modifier le mot de passe.',
                'constraints' => [
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ])
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}