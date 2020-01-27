<?php
namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityTreeType
 * @package Mdzzohrabi\Form
 */
class EntityTreeType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label_method' => '__toString',
            'max_level' => 5,
            'parent_method_name' => 'getParent',
            'children_method_name' => 'getChildren',
            'prefix' => '--',
        ]);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = [];

        $parent_method_name = $options['parent_method_name'];
        foreach ($view->vars['choices'] as $choice) {
            if ($choice->data->$parent_method_name() === null) {
                $choices[$choice->value] = $choice->data;
            }
        }

        $choices = $this->buildTreeChoices($choices, $options);
        $view->vars['choices'] = $choices;
    }

    /**
     * @param object[] $choices
     * @param array $options
     * @param int $level
     *
     * @return array
     */
    protected function buildTreeChoices($choices, array $options, $level = 0)
    {
        $max_level = $options['max_level'];
        if ($max_level == $level) {
            return [];
        }
        $result = [];
        $children_method_name = $options['children_method_name'];
        $label_method = $options['label_method'];

        foreach ($choices as $choice) {
            $result[$choice->getId()] = new ChoiceView(
                $choice,
                (string)$choice->getId(),
                str_repeat($options['prefix'], $level)  . $choice->$label_method(),
                ['data-lvl'=>$level]
            );

            if (!$choice->$children_method_name()->isEmpty()) {
                $newChoices = $choice->$children_method_name();
                $result = $result + $this->buildTreeChoices($newChoices, $options, $level + 1);
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return EntityType::class;
    }
}