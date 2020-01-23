<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TransformEmailExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('transform_email', [$this, 'transformEmail']),
        ];
    }

    public function transformEmail($string)
    {
        $email = explode('@',$string);
        return "@".$email[0];
    }
}