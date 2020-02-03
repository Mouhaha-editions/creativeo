<?php

namespace App\Form;

use App\Entity\Component;
use App\Entity\RecipeComponent;
use App\Entity\Unit;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecipeComponentType extends AbstractType
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
            ->add('component', EntityType::class, [
                "choice_label" => "label",
                'label' => 'entity.recipe_component.label.component',
                'attr' => ['class' => 'form-control-sm'],

                "class" => Component::class,
                "query_builder" => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')->where('c.user = :user')->setParameter('user', $user)->orderBy('c.label');
                },
            ])
            ->add('quantity', null, [
                'label' => 'entity.recipe_component.label.quantity',
                'attr' => ['class' => 'form-control-sm'],

            ])
            ->add('unit', EntityTreeType::class, [
                'label' => 'entity.recipe_component.label.unit',
                'class' => Unit::class,
                'label_method' => 'getLibelle',
                'required' => true,
                'query_builder' => function (UnitRepository $er) {
                    return $er->createQueryBuilder('u')->orderBy('IFELSE(u.parent IS NULL, u.id, u.parent)', 'asc')
                        ->addOrderBy("u.parentRatio", 'ASC');
                },
                'prefix' => '',
                'attr' => ['class' => 'form-control-sm'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeComponent::class,
        ]);
    }
}
