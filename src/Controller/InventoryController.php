<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\User;
use App\Form\InventoryType;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\InventoryRepository;
use App\Repository\UserRepository;
use App\Security\CustomAuthenticator;
use Exception;
use Pkshetlie\PaginationBundle\Service\Calcul;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class InventoryController
 * @package App\Controller
 * @Route("/inventaire")
 */
class InventoryController extends AbstractController
{
    /**
     * @Route("/", name="inventory_index")
     * @param Request $request
     * @param InventoryRepository $inventoryRepository
     * @param Calcul $paginationService
     * @return Response
     */
    public function index(Request $request, InventoryRepository $inventoryRepository, Calcul $paginationService): Response
    {
        $inventory = new Inventory();
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            try {


                $em = $this->getDoctrine()->getManager();
                $inventory->setUser($this->getUser());
                $em->persist($inventory);
                $em->flush();
            }catch(Exception $e){
                VarDumper::dump($e->getMessage());
            }
            $this->addFlash('success','Composant ajouté à l\'inventaire.');
        }
        $qb = $inventoryRepository->createQueryBuilder('i')->where('i.user  = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('i.productLabel', 'ASC');
        $components = $paginationService->setDefaults(50)->process($qb, $request);


        return $this->render('front/inventory/index.html.twig', [
            'components' => $components,
            'form' => $form->createView()
        ]);
    }

}
