<?php


namespace App\Interfaces;


use App\Entity\Component;
use App\Entity\Recipe;
use App\Entity\Unit;
use Doctrine\Common\Collections\Collection;

interface IRecipe
{

    public function getMarge(): ?string;

    public function getTheRecipeComponents(): ?Collection;

    public function getTaxes(): ?Collection;

}