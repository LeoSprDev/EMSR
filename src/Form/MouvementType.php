<?php

namespace App\Form;

use App\Entity\Enum\MouvementType as MouvementTypeEnum;
use App\Entity\Mouvement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MouvementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de mouvement',
                'choices' => MouvementTypeEnum::getChoices(),
                'placeholder' => 'Sélectionnez un type',
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le type de mouvement est obligatoire.'])
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de famille'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prénom'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('numeroAgent', TextType::class, [
                'label' => 'Numéro d\'agent',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: A123456'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro d\'agent est obligatoire.']),
                    new Assert\Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro d\'agent ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('emploi', TextType::class, [
                'label' => 'Emploi',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Intitulé du poste'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'emploi est obligatoire.']),
                    new Assert\Length([
                        'max' => 150,
                        'maxMessage' => 'L\'emploi ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('contrat', TextType::class, [
                'label' => 'Contrat',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: CDI, CDD, Titulaire, etc.'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le contrat est obligatoire.']),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Le contrat ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('service', TextType::class, [
                'label' => 'Service',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Service d\'affectation'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le service est obligatoire.']),
                    new Assert\Length([
                        'max' => 150,
                        'maxMessage' => 'Le service ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('dateEffet', DateType::class, [
                'label' => 'Date d\'effet',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotNull(['message' => 'La date d\'effet est obligatoire.']),
                    new Assert\Type([
                        'type' => '\DateTime',
                        'message' => 'La date d\'effet doit être une date valide.'
                    ])
                ]
            ])
            ->add('remarque', TextareaType::class, [
                'label' => 'Remarque',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Informations complémentaires (optionnel)'
                ]
            ]);

        // Le mois de référence est calculé automatiquement
        // La prise en compte INFO n'est pas dans le formulaire (gérée séparément)
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mouvement::class,
        ]);
    }
}