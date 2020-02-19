<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeFabrication;
use App\Entity\RecipeFabricationComponent;
use App\Form\RecipeFabricationType;
use App\Form\RecipeType;
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
 * @Route("/recipe")
 */
class RecipeController extends AbstractController
{
    /**
     * @Route("/", name="recipe_index", methods={"GET"})
     */
    public function index(RecipeRepository $recipeRepository): Response
    {
        return $this->render('front/recipe/index_widget.html.twig', [
            'recipes' => $recipeRepository->findBy(['user' => $this->getUser()], ['label' => "ASC"]),
        ]);
    }

    /**
     * @Route("/new", name="recipe_new", methods={"GET","POST"})
     * @param Request $request
     * @param TaxeRepository $taxeRepository
     * @param ImageService $imageService
     * @return Response
     */
    public function new(Request $request, TaxeRepository $taxeRepository, ImageService $imageService): Response
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
            $this->gestionUploadVignette($recipe, $form,$imageService);
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
     * @param Recipe $recipe
     * @Route("/ajax/calcul-pose/{id}", name="recipe_calcul")
     * @return Response
     */
    public function calculPose(Recipe $recipe)
    {
        return $this->render('front/recipe/partial/addition.html.twig', [
            'recipe' => $recipe
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
     * @param Request $request
     * @param Recipe $recipe
     * @param ImageService $imageService
     * @return Response
     */
    public function edit(Request $request, Recipe $recipe, ImageService $imageService): Response
    {
        if ($this->getUser() != $recipe->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
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
            $this->gestionUploadVignette($recipe, $form,$imageService);

            $entityManager->flush();

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/fabrique", name="recipe_fabricate", methods={"GET","POST"})
     * @param Request $request
     * @param Recipe $recipe
     * @param InventoryService $inventoryService
     * @return Response
     */
    public function fabricate(Request $request, Recipe $recipe, InventoryService $inventoryService, EntityManagerInterface $entityManager): Response
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
        foreach ($recipe->getRecipeComponents() AS $component) {
            $recipeCompo = new RecipeFabricationComponent();
            $recipeCompo->setQuantity($component->getQuantity());
            $recipeCompo->setComponent($component->getComponent());
            $recipeCompo->setOptionLabel($component->getOptionLabel());
            $recipeCompo->setUnit($component->getUnit());
            $recipeCompo->setRecipeFabrication($recipeFabrication);
            $entityManager->persist($recipeCompo);
            $recipeFabrication->addRecipeFabricationComponents($recipeCompo);
        }
        $recipeFabrication->setMarge($recipe->getMarge());
        $recipeFabrication->setQuantity(1);

        $form = $this->createForm(RecipeFabricationType::class, $recipeFabrication);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($recipeFabrication->getRecipeFabricationComponents() AS $component) {
                $inventoryService->sub($component, $recipeFabrication->getQuantity()*$component->getQuantity());
            }
            $entityManager->persist($recipeFabrication);
            $entityManager->flush();

            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe/start_recipe.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
            'fabrication'=>$recipeFabrication,
        ]);
    }
    /**
     * @Route("/{id}/fabrique/continue", name="recipe_continue_fabricate", methods={"GET","POST"})
     * @param Request $request
     * @param Recipe $recipe
     * @param InventoryService $inventoryService
     * @return Response
     */
    public function continueFabricate(Request $request, RecipeFabrication $recipeFabrication, InventoryService $inventoryService): Response
    {
        if ($this->getUser() != $recipeFabrication->getRecipe()->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
        $form = $this->createForm(RecipeFabricationType::class, $recipeFabrication);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recipeFabrication);
            $entityManager->flush();
            return $this->redirectToRoute('recipe_index');
        }

        return $this->render('front/recipe/start_recipe.html.twig', [
            'recipe' => $recipeFabrication->getRecipe(),
            'form' => $form->createView(),
            'fabrication'=>$recipeFabrication,
        ]);
    }

    /**
     * @Route("/{id}", name="recipe_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Recipe $recipe): Response
    {
        if ($this->getUser() != $recipe->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
        if ($this->isCsrfTokenValid('delete' . $recipe->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($recipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('recipe_index');
    }

    /**
     * @param Recipe $recipe
     * @param FormInterface $form
     * @param ImageService $imageService
     */
    private function gestionUploadVignette(Recipe $recipe, FormInterface $form, ImageService $imageService)
    {
        /** @var UploadedFile $file */
        $file = $form->get('photoFile')->getData();
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            $shortDir = "/upload/recipe/".$this->getUser()->getId()."/";
            try {
                $dir = $this->getParameter('kernel.project_dir') . "/public" . $shortDir;

                $file->move(
                    $dir,
                    $newFilename
                );
                $imageService->compress($dir . $newFilename);
                $recipe->setPhotoPath($shortDir . $newFilename);
            } catch (FileException $e) {
            }
        }
    }

}
