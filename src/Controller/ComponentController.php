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
use App\Service\InventoryService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                "price" => number_format($inventoryService->getCostForRecipeComponent($recipeComponent), 5, ',', ' ') . " &euro;",
                "selected" => $selected,
                "enougth"=>$inventoryService->hasQuantityForRecipeComponent($recipeComponent) ? '<i class="fas fa-check text-success"></i>' : '<i data-toggle="tooltip" title="stock : '.number_format($inventoryService->getQuantityForRecipeComponent($recipeComponent),4,',',' ' ).'" class="fas fa-times text-danger"></i>',
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
            $label = $recipeFabricationComponent->getOptionLabel() ;
            $selected = $recipeFabricationComponent->getOptionLabel() == $i->getOptionLabel();
            $recipeFabricationComponent->setOptionLabel($i->getOptionLabel());
            $data['options'][] = [
                "label" => $i->getOptionLabel(),
                "price" => number_format($inventoryService->getCostForRecipeComponent($recipeFabricationComponent), 5, ',', ' ') . " &euro;",
                "selected" => $selected,
                "enougth"=>$inventoryService->hasQuantityForRecipeComponent($recipeFabricationComponent) ?'<i class="fas fa-check text-success"></i>' : '<i  data-toggle="tooltip" title="stock : '.number_format($inventoryService->getQuantityForRecipeComponent($recipeFabricationComponent),4,',',' ' ).'" class="fas fa-times text-danger"></i>',

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
            ->leftJoin('i.component','component')
            ->where('component.label = :text')
            ->setParameter('text', $text)
            ->setFirstResult(0)->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
        if ($inventory == null) {
            return new JsonResponse([]);
        }

        return new JsonResponse(['unit'=>$inventory->getUnit()->getId()]);
    }

    /**
     * @Route("/", name="component_index", methods={"GET"})
     * @param ComponentRepository $componentRepository
     * @return Response
     */
    public function index(ComponentRepository $componentRepository): Response
    {
        return $this->render('component/index.html.twig', [
            'components' => $componentRepository->findAll(),
        ]);
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
     * @Route("/new", name="component_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $component = new Component();
        $form = $this->createForm(ComponentType::class, $component);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($component);
            $entityManager->flush();

            return $this->redirectToRoute('component_index');
        }

        return $this->render('component/new.html.twig', [
            'component' => $component,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="component_show", methods={"GET"})
     * @param Component $component
     * @return Response
     */
    public function show(Component $component): Response
    {
        return $this->render('component/show.html.twig', [
            'component' => $component,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="component_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Component $component
     * @return Response
     */
    public function edit(Request $request, Component $component): Response
    {
        $form = $this->createForm(ComponentType::class, $component);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('component_index');
        }

        return $this->render('component/edit.html.twig', [
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
}
