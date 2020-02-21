<?php

namespace App\Controller;

use App\Entity\Component;
use App\Entity\Inventory;
use App\Entity\RecipeComponent;
use App\Entity\RecipeFabrication;
use App\Entity\RecipeFabricationComponent;
use App\Form\ComponentType;
use App\Interfaces\IRecipeComponent;
use App\Repository\ComponentRepository;
use App\Repository\InventoryRepository;
use App\Repository\UnitRepository;
use App\Service\ImageService;
use App\Service\InventoryService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/composants")
 */
class ComponentController extends AbstractController
{

    /**
     * @Route("/ajax/options/new/{id}", name="ajax_option_new", methods={"GET"})
     * @param RecipeComponent $recipeFabricationComponent
     * @param InventoryService $inventoryService
     * @param InventoryRepository $inventoryRepository
     * @return Response
     */
    public function getOptionChoicesNew(RecipeComponent $recipeComponent, InventoryService $inventoryService, InventoryRepository $inventoryRepository): Response
    {
        /** @var Inventory[] $inventories */
        $inventories = $inventoryRepository->createQueryBuilder('i')
            ->where('i.component = :component')
            ->setParameter('component', $recipeComponent->getComponent())
            ->groupBy('i.optionLabel')
            ->orderBy('i.optionLabel', 'ASC')
            ->getQuery()->getResult();
        $data = [];
        $data['options'] = [];
        foreach ($inventories AS $i) {
            $selected = $recipeComponent->getOptionLabel() == $i->getOptionLabel();
            $recipeComponent->setOptionLabel($i->getOptionLabel());
            $data['options'][] = [
                "label" => $i->getOptionLabel(),
                "price" => $inventoryService->getCostForRecipeComponent($recipeComponent),
                "selected" => $selected,
                "enougth" => $inventoryService->hasQuantityForRecipeComponent($recipeComponent) ? '<i class="fas fa-check text-success"></i>' : '<i data-toggle="tooltip" title="stock : ' . number_format($inventoryService->getQuantityForRecipeComponent($recipeComponent), 4, ',', ' ') . '" class="fas fa-times text-danger"></i>',
            ];
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax/list", name="ajax_list_component", methods={"GET"})
     * @param RecipeComponent $recipeFabricationComponent
     * @param InventoryService $inventoryService
     * @param InventoryRepository $inventoryRepository
     * @return Response
     */
    public function getAjaxList(Request $request, ComponentRepository $componentRepository): Response
    {
        /** @var Component[] $component */
        $components = $componentRepository->createQueryBuilder('c')
            ->distinct()
            ->where('c.label LIKE :search')
            ->andWhere('c.user = :user')
            ->setParameter('search', $request->get('term') . '%')
            ->setParameter('user', $this->getUser())
            ->getQuery()->getResult();
        $data = ['results' => []];
        /** @var Component $component */
        foreach ($components AS $component) {
            $data['results'][] = [
                "id" => $component->getLabel(),
                "text" => $component->getLabel(),
            ];
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax/options/edit/{id}", name="ajax_option_edit", methods={"GET"})
     * @param RecipeFabricationComponent $recipeFabricationComponent
     * @param InventoryService $inventoryService
     * @param InventoryRepository $inventoryRepository
     * @return Response
     */
    public function getOptionChoicesEdit(RecipeFabricationComponent $recipeFabricationComponent, InventoryService $inventoryService, InventoryRepository $inventoryRepository): Response
    {
        /** @var Inventory[] $inventories */
        $inventories = $inventoryRepository->createQueryBuilder('i')
            ->where('i.component = :component')
            ->setParameter('component', $recipeFabricationComponent->getComponent())
            ->groupBy('i.optionLabel')
            ->orderBy('i.optionLabel', 'ASC')
            ->getQuery()->getResult();
        $data = [];
        $data['options'] = [];
        foreach ($inventories AS $i) {
            $label = $recipeFabricationComponent->getOptionLabel();
            $selected = $recipeFabricationComponent->getOptionLabel() == $i->getOptionLabel();
            $recipeFabricationComponent->setOptionLabel($i->getOptionLabel());
            $data['options'][] = [
                "label" => $i->getOptionLabel(),
                "price" => $inventoryService->getCostForRecipeComponent($recipeFabricationComponent),
                "selected" => $selected,
                "enougth" => $inventoryService->hasQuantityForRecipeComponent($recipeFabricationComponent) ? '<i class="fas fa-check text-success"></i>' : '<i  data-toggle="tooltip" title="stock : ' . number_format($inventoryService->getQuantityForRecipeComponent($recipeFabricationComponent), 4, ',', ' ') . '" class="fas fa-times text-danger"></i>',

            ];
            $recipeFabricationComponent->setOptionLabel($label);

        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax/units/{id}", name="ajax_unit_to_remove", methods={"GET"})
     * @param Component $component
     * @param InventoryRepository $inventoryRepository
     * @return Response
     * @throws NonUniqueResultException
     */
    public function unitToRemove(Component $component, InventoryRepository $inventoryRepository): Response
    {
        /** @var Inventory $inventory */
        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->where('i.component = :component')
            ->setParameter('component', $component)
            ->setFirstResult(0)->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        if ($inventory == null) {
            return new JsonResponse([]);
        }
        $maxUnit = $inventory->getUnit()->getParent() == null ? $inventory->getUnit() : $inventory->getUnit()->getParent();
        $units = [];
        $units[] = [
            "value" => $maxUnit->getId(),
            "name" => $maxUnit->getLibelle()
        ];
        foreach ($maxUnit->getChildren() AS $u) {
            $units[] = [
                "value" => $u->getId(),
                "name" => $u->getLibelle()
            ];
        }


        return new JsonResponse($units);
    }

    /**
     * @Route("/ajax/text/units/{text}", name="ajax_unit_to_select", methods={"GET"})
     * @param Component $component
     * @param InventoryRepository $inventoryRepository
     * @return Response
     * @throws NonUniqueResultException
     */
    public function unitToSelect(string $text, InventoryRepository $inventoryRepository): Response
    {
        /** @var Inventory $inventory */
        $inventory = $inventoryRepository->createQueryBuilder('i')
            ->leftJoin('i.component', 'component')
            ->where('component.label = :text')
            ->setParameter('text', $text)
            ->setFirstResult(0)->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        if ($inventory == null) {
            return new JsonResponse([]);
        }

        return new JsonResponse(['unit' => $inventory->getUnit()->getId()]);
    }

    /**
     * @Route("/ajax-list", name="component_list", methods={"GET"})
     * @param ComponentRepository $componentRepository
     * @return Response
     */
    public function ajaxList(ComponentRepository $componentRepository): Response
    {
        $components = $componentRepository->findBy(['user' => $this->getUser()], ['label' => "ASC"]);
        return new JsonResponse($components);
    }


    /**
     * @Route("/{id}/edit", name="component_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Component $component
     * @param ImageService $imageService
     * @return Response
     */
    public function edit(Request $request, Component $component, ImageService $imageService): Response
    {
        $form = $this->createForm(ComponentType::class, $component);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->gestionUploadVignette($component, $form, $imageService);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('inventory_index');
        }

        return $this->render('front/component/edit.html.twig', [
            'component' => $component,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="component_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Component $component): Response
    {
        if ($this->isCsrfTokenValid('delete' . $component->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($component);
            $entityManager->flush();
        }

        return $this->redirectToRoute('component_index');
    }

    /**
     * @param Component $component
     * @param FormInterface $form
     * @param ImageService $imageService
     */
    private function gestionUploadVignette(Component $component, FormInterface $form, ImageService $imageService)
    {
        /** @var UploadedFile $file */
        $file = $form->get('photoFile')->getData();
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
            $shortDir = "/upload/component/" . $this->getUser()->getId() . "/";
            try {
                $dir = $this->getParameter('kernel.project_dir') . "/public" . $shortDir;

                $file->move(
                    $dir,
                    $newFilename
                );
                $imageService->compress($dir . $newFilename);
                $component->setPhotoPath($shortDir . $newFilename);
            } catch (FileException $e) {
            }
        }
    }

}
