<?php

namespace App\Controller;

use App\Entity\Component;
use App\Form\ComponentType;
use App\Repository\ComponentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/component")
 */
class ComponentController extends AbstractController
{
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
        if ($this->isCsrfTokenValid('delete'.$component->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($component);
            $entityManager->flush();
        }

        return $this->redirectToRoute('component_index');
    }
}
