<?php

namespace App\Form;

use App\Entity\Component;
use App\Entity\RecipeComponent;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecipeComponentType extends AbstractType
{

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface|null
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
            ->add('component', EntityType::class, [
                "choice_label" => "label",
                'label' => 'entity.recipe_component.component',

                "class" => Component::class,
                "query_builder" => function (EntityRepository $er) use($user) {
                    return $er->createQueryBuilder('c')->where('c.user = :user')->setParameter('user', $user)->orderBy('c.label');
                },
            ])
            ->add('quantity', null, [
                'label' => 'entity.recipe_component.quantity'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeComponent::class,
        ]);
    }
}
