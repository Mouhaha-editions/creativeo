<?php

namespace App\Controller;

use App\Entity\Taxe;
use App\Form\TaxeType;
use App\Repository\TaxeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/taxe")
 */
class TaxeController extends AbstractController
{
    /**
     * @Route("/", name="taxe_index", methods={"GET"})
     */
    public function index(TaxeRepository $taxeRepository): Response
    {
        return $this->render('front/taxe/index.html.twig', [
            'taxes' => $taxeRepository->findBy(['user'=>$this->getUser()]),
        ]);
    }

    /**
     * @Route("/new", name="taxe_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $taxe = new Taxe();
        $form = $this->createForm(TaxeType::class, $taxe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taxe->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($taxe);
            $entityManager->flush();

            return $this->redirectToRoute('taxe_index');
        }

        return $this->render('front/taxe/new.html.twig', [
            'taxe' => $taxe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="taxe_show", methods={"GET"})
     */
    public function show(Taxe $taxe): Response
    {
        return $this->render('front/taxe/show.html.twig', [
            'taxe' => $taxe,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="taxe_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Taxe $taxe): Response
    {
        $form = $this->createForm(TaxeType::class, $taxe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('taxe_index');
        }

        return $this->render('front/taxe/edit.html.twig', [
            'taxe' => $taxe,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="taxe_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Taxe $taxe): Response
    {
        if ($this->isCsrfTokenValid('delete'.$taxe->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($taxe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('taxe_index');
    }
}
