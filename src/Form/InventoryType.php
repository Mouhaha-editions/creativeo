<?php

namespace App\Form;

use App\Entity\Inventory;
use App\Entity\Unit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productLabel',null,[
                'label'=>'entity.inventory.label.productLabel',
                'required'=>true,
                'attr'=>['class'=>'form-control-sm'],
            ])
            ->add('quantity',NumberType::class,[
                'label'=>'entity.inventory.label.quantity',
                'scale'=>4,
                'required'=>true,
                'attr'=>['class'=>'form-control-sm'],

            ])
            ->add('unit',EntityTreeType::class,[
                'label'=>'entity.inventory.label.unit',
                'class'=>Unit::class,
                'label_method'=>'getLibelle',
                'required'=>true,
                'prefix'=>'',
                'attr'=>['class'=>'form-control-sm'],

            ])
            ->add('price',NumberType::class,[
                'label'=>'entity.inventory.label.unitPrice',
                'scale'=>4,
                'required'=>true,
                'attr'=>['class'=>'form-control-sm'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inventory::class,
        ]);
    }
}
