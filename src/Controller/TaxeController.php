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
            'taxes' => $taxeRepository->findBy(['user' => $this->getUser()]),
            'community_taxes' => $taxeRepository->findBy(['isEnabledForCommunity' => $this->getUser(), 'enabled' => true]),
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
        if ($this->getUser() != $taxe->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
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
     * @Route("/{id}/copy", name="taxe_copy_to_mine", methods={"GET"})
     */
    public function copy(Request $request, Taxe $taxe, TaxeRepository $taxeRepository): Response
    {
        if (!$taxe->getIsEnabledForCommunity()) {
            $this->addFlash('danger', 'text.danger.not_community_elt');
            return $this->redirectToRoute('taxe_index');
        }
        if ($taxeRepository->findOneby(['user' => $this->getUser(), 'parent' => $taxe])) {
            $this->addFlash('danger', 'text.danger.already_copied');
            return $this->redirectToRoute('taxe_index');
        }
        $em = $this->getDoctrine()->getManager();
        $taxeCopied = new Taxe();
        $taxeCopied->setUser($this->getUser());
        $taxeCopied->setLibelle($taxe->getLibelle());
        $taxeCopied->setParent($taxe);
        $taxeCopied->setDescription($taxe->getDescription());
        $taxeCopied->setType($taxe->getType());
        $taxeCopied->setValue($taxe->getValue());
        $taxeCopied->setEnabled(true);
        $taxeCopied->setIsDefault(false);
        $taxeCopied->setIsEnabledForCommunity(false);
        $em->persist($taxeCopied);
        $em->flush();
        $this->addFlash("success", 'text.success.copied_elt');

        return $this->redirectToRoute('taxe_index');

    }

    /**
     * @Route("/{id}", name="taxe_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Taxe $taxe): Response
    {
        if ($this->getUser() != $taxe->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return $this->redirectToRoute('taxe_index');
        }
        if ($this->isCsrfTokenValid('delete' . $taxe->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($taxe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('taxe_index');
    }
}
