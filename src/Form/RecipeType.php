<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Taxe;
use App\Entity\Unit;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Constraints\File;

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
            ->add('proportion', null, [
                'label' => 'entity.recipe.label.proportion',
                'required' => true,
            ])
            ->add('unit', EntityTreeType::class, [
                'label' => 'entity.recipe.label.unit',
                'class' => Unit::class,
                'label_method' => 'getLibelle',
                'required' => true,
                'prefix' => '',
                'attr' => ['class' => 'form-control-sm'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'entity.recipe.label.description',
                'required' => false,
            ])
            ->add('community', CheckboxType::class, [
                'label' => 'entity.recipe.label.community',
                'required' => false,
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
            ->add('photoFile', FileType::class, [
                'label' => 'entity.recipe.label.photoPath',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/gif',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Seules les images : jpeg, png et gif sont acceptées et le fichier doit faire moins de 2Mo',
                    ])
                ],
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
