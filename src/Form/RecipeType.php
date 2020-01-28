<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Taxe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', null,[
                'label'=>'entity.recipe.label.label',
                'required'=>true,
            ])
            ->add('marge', null,[
                'label'=>'entity.recipe.label.marge',
                'required'=>true,
            ])
            ->add('estimatedHours', NumberType::class,[
                'label'=>'entity.recipe.label.estimatedHours',
                'required'=>true,
                'scale'=>4,
            ])
//            ->add('taxes', EntityType::class, [
//                'class' => Taxe::class,
//                'multiple'=>true,
//                'label'=>'entity.recipe.label.taxes',
//
//
//            ])
            ->add('recipeComponents', CollectionType::class, [
                'entry_type' => RecipeComponentType::class,
                'allow_add' => true,
                'label'=>'entity.recipe.label.recipeComponents',

                'allow_delete'=>true,
                'entry_options' => [
                    'attr' => ['class' => 'row component-box'],
                ],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
