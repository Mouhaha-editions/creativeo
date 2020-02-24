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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @Route("/ajax/update/quantity/{id}", name="inventory_update_quantity", methods={"POST"})
     * @param Request $request
     * @param Inventory $inventory
     * @return RedirectResponse|Response
     */
    public function updateQuantityAjax(Request $request, Inventory $inventory)
    {
        if ($this->getUser() != $inventory->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return new Response('', 500);
        }

        $inventory->setQuantity($request->get('value', 0));
        $this->getDoctrine()->getManager()->flush();

        return $this->render('front/inventory/partial/inventory_line.html.twig', [
            'composant' => $inventory,
            'i' => 0,
        ]);
    }

    /**
     * @Route("/ajax/delete/{id}", name="inventory_delete")
     * @param Request $request
     * @param Inventory $inventory
     * @return RedirectResponse|Response
     */
    public function deleteAjax(Request $request, Inventory $inventory)
    {
        if ($this->getUser() != $inventory->getUser()) {
            $this->addFlash('danger', 'text.danger.not_yours');
            return new Response('', 500);
        }


        $this->getDoctrine()->getManager()->remove($inventory);
        $this->getDoctrine()->getManager()->flush();

        return new Response('', 200);
    }

    /**
     * @Route("/detail/{id}", name="inventory_detail")
     * @param Request $request
     * @param InventoryRepository $inventoryRepository
     * @param Component $component
     * @return Response
     */
    public function detail(Request $request, InventoryRepository $inventoryRepository, Component $component): Response
    {
        $inventories = $inventoryRepository->createQueryBuilder('i')
            ->where('i.component = :component')
            ->andWhere('i.user = :user')
            ->setParameter('component', $component)
            ->setParameter('user', $this->getUser())
            ->orderBy('i.optionLabel', 'ASC')
            ->addOrderBy('i.price', $this->getUser()->getUseOrderPreference())
            ->getQuery()->getResult();
        return $this->render('front/inventory/index.html.twig', [
            'inventories' => $inventories,
            'component' => $component,
        ]);
    }

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
        if ($form->isSubmitted()) {
            $quantityCalculated = str_replace(' ', '', str_replace(',', '.', $form->get('quantityCalculated')->getData()));
            if (!preg_match('#[0-9\.\+-/*]#', $quantityCalculated)) {
                $form->get('quantityCalculated')->addError(new FormError("Des caractères non autorisés sont présents."));
            }
            if ($form->isValid()) {
                $productLabel = ucfirst(strtolower($form->get('productLabel')->getData()));
                try {
                    $component = $componentRepository->findOneBy(['label' => $productLabel, 'user' => $this->getUser()]);
                    $em = $this->getDoctrine()->getManager();

                    $quantity = eval("return " . $quantityCalculated . ";");
                    $inventory->setQuantity($quantity);
                    if ($component == null) {
                        $component = new Component();
                        $component->setLabel($productLabel);
                        $component->setUser($this->getUser());
                        $em->persist($component);
                    }

                    $tmpInventory = $inventoryRepository->findOneBy(['user' => $this->getUser(), 'price' => $inventory->getPrice(), 'optionLabel' => $inventory->getOptionLabel(), 'component' => $component]);
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
                    $this->addFlash('success', 'flashes.message.success.added_inventory');

                    $em->flush();
                } catch (Exception $e) {
                    $this->addFlash('danger', 'flashes.message.danger.added_inventory');

                    //VarDumper::dump($e->getMessage());
                }
                unset($inventory);
                unset($form);
                $inventory = new Inventory();
                $form = $this->createForm(InventoryType::class, $inventory);
                $this->addFlash('success', 'Composant ajouté à l\'inventaire.');
            }
        }
        $qb = $componentRepository->createQueryBuilder('c')
//            ->leftJoin('i.component', 'c')
            ->where('c.user  = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('c.label', 'ASC')
//            ->addOrderBy('i.optionLabel', 'ASC')
//        ->groupBy('i.component')
        ;
        $components = $paginationService->setDefaults(50)->process($qb, $request);


        return $this->render('front/inventory/index_widget.html.twig', [
            'components' => $components,
            'form' => $form->createView()
        ]);
    }

}
