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
            $request->getSession()->getFlashBag()->add('supplierSuccess', 'The supplier '.$supplier->getFirstName().' is added successfully!');
            return $this->redirectToRoute('allSuppliers');
        }
        return $this->render('supplier/supplierForm.html.twig', [
            'connectedUser' => $this->getUser(),
            'form' => $form->createView(),
            'supplier' => $supplier,
        ]);
    }

    public function customizeMsg(Request $request){
        if($request->attributes->get('_route')=="addSupplier"){
            return "added";
        }else if($request->attributes->get('_route')=="editSupplier"){
            return "updated";
        }else{
            return "deleted";
        }
    }
    /**
     * @Route("supplier/delete/{id}", name="deleteSupplier")
     */
    public function delete($id, SupplierRepository $repo, ObjectManager $manager){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute('allSuppliers');
    }

    /**
     * @Route("/supplier/show/{id}", name="showSupplier")
     */
    public function showDetails(Supplier $supplier=null){
        if($supplier)
        {
            return $this->render('/supplier/show.html.twig', [
            'connectedUser' => $this->getUser(),
            'supplier' => $supplier,
        ]);
    }
    }

    /**
     * @Route("/supplier/deleteAll", name="deleteAllSuppliers")
     */
    public function deleteAll(ObjectManager $manager, SupplierRepository $repo){
        foreach ($repo->findAll() as $supplier) {
            $manager->remove($supplier);
            $manager->flush();
        }
        return $this->redirectToRoute('allSuppliers');
    }
}
