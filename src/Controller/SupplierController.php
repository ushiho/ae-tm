<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Form\SupplierType;
use App\Repository\SupplierRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SupplierController extends AbstractController
{
    /**
     * @Route("/supplier", name="allSuppliers")
     */
    public function show(SupplierRepository $repo)
    {
        return $this->render('supplier/supplierBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'suppliers' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("supplier/new", name="addSupplier")
     * @Route("supplier/edit/{id}", name="editSupplier")
     */
    public function action(Supplier $supplier=null, ObjectManager $manager, Request $request){
        if($supplier==null){
            $supplier = new Supplier();
        }
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid()){
            $manager->persist($supplier);
            $manager->flush();
            return $this->redirectToRoute('allSuppliers');
        }
        return $this->render('supplier/supplierForm.html.twig', [
            'connectedUser' => $this->getUser(),
            'form' => $form->createView(),
            'supplier' => $supplier,
        ]);
    }

}
