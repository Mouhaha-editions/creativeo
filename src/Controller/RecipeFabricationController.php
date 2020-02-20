<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeFabrication;
use App\Entity\RecipeFabricationComponent;
use App\Form\RecipeFabricationType;
use App\Form\RecipeType;
use App\Repository\RecipeFabricationRepository;
use App\Repository\RecipeRepository;
use App\Repository\TaxeRepository;
use App\Service\ImageService;
use App\Service\InventoryService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/fabrication")
 */
class RecipeFabricationController extends AbstractController
{
    /**
     * @Route("/", name="fabrication_index", methods={"GET"})
     * @param RecipeFabricationRepository $recipeFabricationRepository
     * @return Response
     */
    public function index(RecipeFabricationRepository $recipeFabricationRepository): Response
    {
        return $this->render('front/recipe_fabrication/index.html.twig', [
            'recipes' => $recipeFabricationRepository->createQueryBuilder('r')
                ->leftJoin('r.recipe', 'recipe')
            ->where('recipe.user = :user')
                ->setParameter('user',$this->getUser())
                ->orderBy('r.createdAt','DESC')
                ->getQuery()->getResult()
        ]);
    }

}
