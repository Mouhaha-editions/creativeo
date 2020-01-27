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
            $productLabel = ucfirst(strtolower($form->get('productLabel')->getData()));
            try {
                $component = $componentRepository->findOneBy(['label' => $productLabel, 'user' => $this->getUser()]);

                $em = $this->getDoctrine()->getManager();
                if ($component == null) {
                    $component = new Component();
                    $component->setLabel($productLabel);
                    $component->setUser($this->getUser());
                    $em->persist($component);
                }

                $tmpInventory = $inventoryRepository->findOneBy(['user' => $this->getUser(), 'price' => $inventory->getPrice(), 'component' => $component]);
                if ($tmpInventory != null) {
                    $tmpInventory->setQuantity($inventory->getQuantity() + $tmpInventory->getQuantity());
                    $inventory = $tmpInventory;
                } else {
                    $inventory->setComponent($component);
                    $inventory->setUser($this->getUser());
                    $em->persist($inventory);
                }

                $component->addInventory($inventory);
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
            unset($inventory);
            unset($form);
            $inventory = new Inventory();
            $form = $this->createForm(InventoryType::class, $inventory);
            $this->addFlash('success', 'Composant ajouté à l\'inventaire.');
        }
        $qb = $inventoryRepository->createQueryBuilder('i')
            ->leftJoin('i.component','c')
            ->where('i.user  = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('c.label', 'ASC');
        $components = $paginationService->setDefaults(50)->process($qb, $request);


        return $this->render('front/inventory/index.html.twig', [
            'components' => $components,
            'form' => $form->createView()
        ]);
    }

}
