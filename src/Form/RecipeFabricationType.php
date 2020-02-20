<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\RecipeFabrication;
use App\Entity\Taxe;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecipeFabricationType extends AbstractType
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
            ->add('marge', null, [
                'label' => 'entity.recipe_fabrication.label.marge',
                'required' => true,
            ])
            ->add('hours', HiddenType::class, [
                'label' => 'entity.recipe_fabrication.label.hours',
                'required' => false,
            ]) ->add('ended', HiddenType::class, [
                'label' => 'entity.recipe_fabrication.label.ended',
                'required' => false,
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'entity.recipe_fabrication.label.quantity',
                'required' => true,
                'scale' => 2,
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
                'label' => 'entity.recipe_fabrication.label.taxes',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeFabrication::class,
        ]);
    }
}
