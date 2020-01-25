<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hourCost', NumberType::class, [
                "label" => "entity.user.label.hourCost",
                "scale" => 4
            ])
            ->add('moneyUnit', null, [
                "label" => "entity.user.label.moneyUnit"
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
