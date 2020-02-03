<?php

namespace App\Controller;

use App\Entity\Component;
use App\Entity\Inventory;
use App\Form\ComponentType;
use App\Repository\ComponentRepository;
use App\Repository\InventoryRepository;
use App\Repository\UnitRepository;
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
     * @Route("/ajax/units/{id}", name="ajax_unit_to_remove", methods={"GET"})
     * @param Component $component
     * @param InventoryRepository $inventoryRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
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
            "value"=>$maxUnit->getId(),
            "name"=>$maxUnit->getLibelle()
        ];
        foreach ($maxUnit->getChildren() AS $u) {
            $units[] = [
                "value"=>$u->getId(),
                "name"=>$u->getLibelle()
            ];
        }


        return new JsonResponse($units);
    }

    /**
     * @Route("/", name="component_index", methods={"GET"})
     */
    public function index(ComponentRepository $componentRepository): Response
    {
        return $this->render('component/index.html.twig', [
            'components' => $componentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ajax-list", name="component_list", methods={"GET"})
     */
    public function ajaxList(ComponentRepository $componentRepository): Response
    {
        $components = $componentRepository->findBy(['user' => $this->getUser()], ['label' => "ASC"]);
        return new JsonResponse($components);
    }

    /**
     * @Route("/new", name="component_new", methods={"GET","POST"})
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
     */
    public function show(Component $component): Response
    {
        return $this->render('component/show.html.twig', [
            'component' => $component,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="component_edit", methods={"GET","POST"})
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
