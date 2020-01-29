<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('hourCost', NumberType::class, [
//                "label" => "entity.user.label.hourCost",
//                "scale" => 4
//            ])
            ->add('weeklyHours',NumberType::class,[
                "label" => "entity.user.label.weeklyHours",
                "scale" => 2
            ])
            ->add('monthlyCharges',NumberType::class,[
                "label" => "entity.user.label.monthlyCharges",
                "scale" => 4
            ])
            ->add('monthlySalary',NumberType::class,[
                "label" => "entity.user.label.monthlySalary",
                "scale" => 2
            ])
            ->add('publicHolidaysWeeks',NumberType::class,[
                "label" => "entity.user.label.publicHolidaysWeeks",
                "scale" => 0
            ])

            ->add('moneyUnit', null, [
                "label" => "entity.user.label.moneyUnit"
            ])
            ->add('defaultMarge', null, [
                "label" => "entity.user.label.defaultMarge"
            ])
            ->add('useOrderPreference', ChoiceType::class, [
                "choices"=>[
                    "option.user.useOrderPreference.desc"=>"DESC",
                    "option.user.useOrderPreference.asc"=>"ASC",
                ],
                "label" => "entity.user.label.useOrderPreference"

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
