<?php


namespace App\Interfaces;


use App\Entity\Component;
use App\Entity\Recipe;
use App\Entity\Unit;

interface IRecipeComponent
{

    public function getComponent(): ?Component;

    public function getQuantity();

    public function getBaseQuantity();

    public function getUnit(): ?Unit;

    public function getOptionLabel();

}