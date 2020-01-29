<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Taxe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecipeType extends AbstractType
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
            ->add('label', null, [
                'label' => 'entity.recipe.label.label',
                'required' => true,
            ])
            ->add('marge', null, [
                'label' => 'entity.recipe.label.marge',
                'required' => true,
            ])
            ->add('estimatedHours', NumberType::class, [
                'label' => 'entity.recipe.label.estimatedHours',
                'required' => true,
                'scale' => 4,
            ])
            ->add('taxes', EntityType::class, [
                'class' => Taxe::class,
                'multiple' => true,
                'choice_label' => function (Taxe $taxe) use ($user) {
                    return $taxe->getLibelle() . " " . number_format($taxe->getValue(), 2, ',', ' ') . " " .
                        ($taxe->getType() == Taxe::TYPE_PERCENTAGE ? "%" : $user->getMoneyunit());
                },
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('t')->Where('t.enabled = true')
                        ->andWhere('t.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('t.libelle');
                },
                'expanded' => true,
                'label' => 'entity.recipe.label.taxes',

            ])
            ->add('recipeComponents', CollectionType::class, [
        'entry_type' => RecipeComponentType::class,
        'allow_add' => true,
        'label' => 'entity.recipe.label.recipeComponents',

        'allow_delete' => true,
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
