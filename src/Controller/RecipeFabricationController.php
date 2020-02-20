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
                ->setParameter('user', $this->getUser())
                ->orderBy('r.createdAt', 'DESC')
                ->getQuery()->getResult()
        ]);
    }


    /**
     * @Route("/sell/{id}", name="fabrication_sell", methods={"GET"})
     * @param RecipeFabricationRepository $recipeFabricationRepository
     * @return Response
     */
    public function sell(RecipeFabricationRepository $recipeFabricationRepository): Response
    {
        return $this->render('front/recipe_fabrication/index.html.twig', [
            'recipes' => $recipeFabricationRepository->createQueryBuilder('r')
                ->leftJoin('r.recipe', 'recipe')
                ->where('recipe.user = :user')
                ->setParameter('user', $this->getUser())
                ->orderBy('r.createdAt', 'DESC')
                ->getQuery()->getResult()
        ]);
    }

    /**
     * @Route("/inventoring/{id}", name="fabrication_inventoring", methods={"GET"})
     * @param RecipeFabricationRepository $recipeFabricationRepository
     * @return Response
     */
    public function inventoring(RecipeFabricationRepository $recipeFabricationRepository): Response
    {
        return $this->render('front/recipe_fabrication/index.html.twig', [
            'recipes' => $recipeFabricationRepository->createQueryBuilder('r')
                ->leftJoin('r.recipe', 'recipe')
                ->where('recipe.user = :user')
                ->setParameter('user', $this->getUser())
                ->orderBy('r.createdAt', 'DESC')
                ->getQuery()->getResult()
        ]);
    }
    /**
     * @Route("/{id}/fabrique", name="recipe_fabricate", methods={"GET","POST"})
     * @param Request $request
     * @param Recipe $recipe
     * @param InventoryService $inventoryService
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function start(Request $request, Recipe $recipe, InventoryService $inventoryService, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() != $recipe->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
        $recipeFabrication = new RecipeFabrication();
        $recipeFabrication->setRecipe($recipe);
        foreach ($recipe->getTaxes() AS $taxe) {
            $recipeFabrication->addTax($taxe);
        }
        $options = $request->get('options', []);
        $prices = $request->get('prices', []);

        foreach ($recipe->getRecipeComponents() AS $component) {
            $recipeCompo = new RecipeFabricationComponent();
            $recipeCompo->setQuantity($component->getQuantity());
            $recipeCompo->setComponent($component->getComponent());
            if (isset($options[$component->getId()])) {
                $recipeCompo->setOptionLabel($options[$component->getId()]);
            }
            if (isset($prices[$component->getId()])) {
                $recipeCompo->setAmount($prices[$component->getId()]);
            }

            $recipeCompo->setDescription($component->getDescription());
            $recipeCompo->setUnit($component->getUnit());
            $recipeCompo->setRecipeFabrication($recipeFabrication);
            $recipeFabrication->addRecipeFabricationComponents($recipeCompo);
        }
        $recipeFabrication->setMarge($recipe->getMarge());
        $recipeFabrication->setQuantity(1);

        $form = $this->createForm(RecipeFabricationType::class, $recipeFabrication);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($recipeFabrication->getEnded() == true) {
                foreach ($recipeFabrication->getRecipeFabricationComponents() AS $component) {
                    $inventoryService->sub($component, floatval($recipeFabrication->getQuantity()) * floatval($component->getQuantity()));
                }
            }
            $entityManager->persist($recipeFabrication);
            $entityManager->flush();

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe_fabrication/start.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
            'fabrication' => $recipeFabrication,
        ]);
    }

    /**
     * @Route("/{id}/fabrique/continue", name="recipe_continue_fabricate", methods={"GET","POST"})
     * @param Request $request
     * @param RecipeFabrication $recipeFabrication
     * @param InventoryService $inventoryService
     * @return Response
     */
    public function continue(Request $request, RecipeFabrication $recipeFabrication, InventoryService $inventoryService): Response
    {
        if ($this->getUser() != $recipeFabrication->getRecipe()->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
        $form = $this->createForm(RecipeFabricationType::class, $recipeFabrication);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($recipeFabrication->getEnded() == true) {
                foreach ($recipeFabrication->getRecipeFabricationComponents() AS $component) {
                    $inventoryService->sub($component, floatval($recipeFabrication->getQuantity()) * floatval($component->getQuantity()));
                }
            }

            $options = $request->get('options', []);
            $prices = $request->get('prices', []);
            foreach ($recipeFabrication->getRecipeFabricationComponents() AS $component) {
                if (isset($options[$component->getId()])) {
                    $component->setOptionLabel($options[$component->getId()]);
                }
                if (isset($options[$component->getId()])) {
                    $component->setAmount($prices[$component->getId()]);
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipeFabrication);
            $entityManager->flush();
            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe_fabrication/start.html.twig', [
            'recipe' => $recipeFabrication->getRecipe(),
            'form' => $form->createView(),
            'fabrication' => $recipeFabrication,
        ]);
    }

    /**
     * @param RecipeFabrication $recipeFabrication
     * @return Response
     * @Route("/ajax/calcul-pose/{id}", name="fabrication_calcul")
     */
    public function calculPose(RecipeFabrication $recipeFabrication)
    {
        return $this->render('front/recipe_fabrication/partial/addition.html.twig', [
            'recipe' => $recipeFabrication
        ]);
    }
}
