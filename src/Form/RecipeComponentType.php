<?php

namespace App\Form;

use App\Entity\Component;
use App\Entity\RecipeComponent;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeComponentType extends AbstractType
{

    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('component', EntityType::class, [
                "choice_label" => "label",
                'label'=>'entity.recipe_component.component',

                "class" => Component::class,
                "query_builder" => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.label');
                },
        ])
        ->add('quantity',null,[
            'label'=>'entity.recipe_component.quantity'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeComponent::class,
        ]);
    }
}
