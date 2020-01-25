<?php

namespace App\Controller;

use App\Form\PreferenceType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController
 * @package App\Controller
 * @Route("/settings")
 */
class PreferenceController extends AbstractController
{

    /**
     * @Route("/", name="preference")
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        $form = $this->createForm(PreferenceType::class, $this->getUser());
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success','flash.success.save');
        }
        return $this->render('front/preference/index.html.twig', ['form'=>$form->createView()]);
    }
}
