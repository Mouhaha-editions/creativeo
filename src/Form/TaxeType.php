<?php

namespace App\Form;

use App\Entity\Taxe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'entity.taxe.label.libelle',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'entity.taxe.label.description',
                'required' => false,

            ])
            ->add('value', NumberType::class, [
                'scale' => 5,
                'label' => 'entity.taxe.label.value',
                'required' => true,
            ])
            ->add('isDefault', CheckboxType::class, [
                'required' => false,
                'label' => 'entity.taxe.label.isDefault',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => Taxe::Types,
                'required' => true,
                'label' => 'entity.taxe.label.type',
            ])->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => 'entity.taxe.label.enabled',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Taxe::class,
        ]);
    }
}
