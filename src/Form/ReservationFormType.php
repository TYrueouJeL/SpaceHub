<?php

namespace App\Form;

use App\Entity\EventType;
use App\Entity\Place;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxCapacity = null !== $options['place'] ? $options['place']->getCapacity() : 1000;

        $placeFieldOptions = [
            'label' => "Lieu",
            'attr' => ['class' => 'form-input w-full'],
            'class' => Place::class,
            'choice_label' => 'name',
            'placeholder' => 'Choisir un lieu',
            'required' => false,
        ];

        if (null !== $options['place']) {
            $placeFieldOptions['choices'] = [$options['place']];
            $placeFieldOptions['placeholder'] = false;
            $placeFieldOptions['data'] = $options['place'];
            $placeFieldOptions['disabled'] = true;
        }

        $builder
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'attr' => ['class' => 'form-input w-full', 'type' => 'date', 'required' => 'required'],
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'attr' => ['class' => 'form-input w-full', 'type' => 'date', 'required' => 'required'],
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('peopleNumber', ChoiceType::class, [
                'label' => 'Nombre de personnes',
                'attr' => ['class' => 'form-input w-full', 'required' => 'required'],
                'choices' => array_combine(range(1, $maxCapacity), range(1, $maxCapacity)),
                'placeholder' => 'Choisir le nombre',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('eventType', EntityType::class, [
                'label' => "Type d'évènement",
                'placeholder' => 'Choisir un type',
                'attr' => ['class' => 'form-input w-full'],
                'class' => EventType::class,
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('place', EntityType::class, $placeFieldOptions)
            ->add('submit', SubmitType::class, [
                'label' => 'Réserver maintenant',
                'attr' => ['class' => 'btn btn-primary w-full mb-sm'],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            // Récupération des dates depuis l'entité si présente, sinon depuis les champs
            if ($data instanceof Reservation) {
                $start = $data->getStartDate();
                $end = $data->getEndDate();
            } else {
                // cas où data est un array (sécurité)
                $start = $form->has('startDate') ? $form->get('startDate')->getData() : null;
                $end = $form->has('endDate') ? $form->get('endDate')->getData() : null;
            }

            if ($start && $end && $end < $start) {
                $form->get('endDate')->addError(new FormError('La date de fin doit être supérieure ou égale à la date de début.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'place' => null,
        ]);

        $resolver->setAllowedTypes('place', [Place::class, 'null']);
    }
}
