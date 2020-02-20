<?php

namespace App\Form;

use App\Entity\Component;
use App\Entity\RecipeComponent;
use App\Entity\Unit;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecipeFabricationComponentType extends AbstractType
{

    /**
     * @var TokenInterface|null
     */
    private $token;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->token->getUser();
        $builder

            ->add('optionLabel', ChoiceType::class, [
                'label' => 'entity.recipe_fabrication_component.label.optionLabel',
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => 'entity.recipe_fabrication_component.placeholder.optionLabel',
                    ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'entity.recipe_component.label.quantity',
                'attr' => [
                    'class' => 'form-control-sm',
                    'placeholder' => 'entity.recipe_component.placeholder.quantity',
                ],
                'scale'=>4
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeFabricationComponent::class,
        ]);
    }
}
