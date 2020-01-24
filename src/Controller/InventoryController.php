<?php

namespace App\Controller;

use App\Entity\Component;
use App\Entity\Inventory;
use App\Entity\Price;
use App\Form\InventoryType;
use App\Repository\ComponentRepository;
use App\Repository\InventoryRepository;
use Exception;
use Pkshetlie\PaginationBundle\Service\Calcul;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     * @param ComponentRepository $componentRepository
     * @param Calcul $paginationService
     * @return Response
     */
    public function index(Request $request, InventoryRepository $inventoryRepository, ComponentRepository $componentRepository, Calcul $paginationService): Response
    {
        $inventory = new Inventory();
        $form = $this->createForm(InventoryType::class, $inventory);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                $inventory->setUser($this->getUser());
                $em->persist($inventory);

                $component = $componentRepository->findBy(['label' => $inventory->getProductLabel(), 'user' => $this->getUser()]);
                if ($component == null) {
                    $component = new Component();
                    $component->setLabel($inventory->getProductLabel());
                    $component->setUser($this->getUser());
                    $component->addInventory($inventory);
                    $em->persist($component);
                }
                $component->setCommunityEnabled(false);
                if (!$component->hasPrices($inventory->getPrice())) {
                    $price = new Price();
                    $price->setUnitPrice($inventory->getPrice());
                    $price->setTaxToApply(0);
                    $price->setAutoUpdateEnabled(false);
                    $component->addPrice($price);
                    $em->persist($price);
                }

                $em->flush();
            } catch (Exception $e) {

                VarDumper::dump($e->getMessage());
            }
            $this->addFlash('success', 'Composant ajouté à l\'inventaire.');
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
