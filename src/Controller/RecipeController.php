<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Repository\TaxeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/recipe")
 */
class RecipeController extends AbstractController
{
    /**
     * @Route("/", name="recipe_index", methods={"GET"})
     */
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('front/recipe/index.html.twig', [
            'recipes' => $recipeRepository->findBy(['user' => $this->getUser()], ['label' => "ASC"]),
        ]);
    }

    /**
     * @Route("/new", name="recipe_new", methods={"GET","POST"})
     */
    public function new(Request $request, TaxeRepository $taxeRepository): Response
    {
        $recipe = new Recipe();
        $recipe->setMarge($this->getUser()->getDefaultMarge());
        foreach ($taxeRepository->findBy(['user' => $this->getUser(), 'isDefault' => true, 'enabled' => true]) AS $taxe) {
            $recipe->addTax($taxe);
        }
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setUser($this->getUser());
            foreach ($recipe->getRecipeComponents() AS $component) {
                $component->setRecipe($recipe);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipe);
            $entityManager->flush();

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe/new.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="recipe_show", methods={"GET"})
     */
    public function show(Recipe $recipe): Response
    {
        return $this->render('front/recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="recipe_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Recipe $recipe): Response
    {
        if($this->getUser() != $recipe->getUser()){
            $this->addFlash('danger','text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
        $form = $this->createForm(RecipeType::class, $recipe);
        $originalTags = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($recipe->getRecipeComponents() as $recipeComponent) {
            $originalTags->add($recipeComponent);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($originalTags AS $component) {
                if (false === $recipe->getRecipeComponents()->contains($component)) {
                    $component->setRecipe(null);
                    $entityManager->persist($component);
//                     $entityManager->remove($component);
                }
            }
            foreach ($recipe->getRecipeComponents() AS $component) {
                $component->setRecipe($recipe);
            }
            $entityManager->flush();

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="recipe_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Recipe $recipe): Response
    {
        if($this->getUser() != $recipe->getUser()){
        $this->addFlash('danger','text.danger.not_yours');
        return $this->redirectToRoute('taxe_index');
    }
        if ($this->isCsrfTokenValid('delete' . $recipe->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($recipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('recipe_index');
    }
}
