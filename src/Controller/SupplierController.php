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
        if($this->getUser()->getRole() != 3){

            return $this->render('supplier/supplierBase.html.twig', [
                'connectedUser' => $this->getUser(),
                'suppliers' => $repo->findAll(),
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("supplier/new", name="addSupplier")
     * @Route("supplier/edit/{id}", name="editSupplier")
     */
    public function action(Supplier $supplier=null, ObjectManager $manager, Request $request){
        if($this->getUser()->getRole() != 3){

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
        }else{
            return $this->redirectToRoute('error403');
        }
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
    public function delete(Supplier $supplier, ObjectManager $manager, Request $request){
        if($this->getUser()->getRole() != 3){

            if($supplier){
                foreach ($supplier->getAllocates() as $rent) {
                    $manager->remove($rent);
                }
                $manager->remove($supplier);
                $manager->flush();
                $request->getSession()->getFlashBag()->add('supplierSuccess', 'The Supplier is deleted successfully!');
            }else{
                $request->getSession()->getFlashBag()->add('supplierSuccess', 'No selected Supplier to delete!');
            }
            return $this->redirectToRoute('allSuppliers');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/supplier/show/{id}", name="showSupplier")
     */
    public function showDetails(Supplier $supplier=null){
        if($this->getUser()->getRole() != 3){
            if($supplier){
                return $this->render('/supplier/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'supplier' => $supplier,
            ]);
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/supplier/deleteAll", name="deleteAllSuppliers")
     */
    public function deleteAll(ObjectManager $manager, SupplierRepository $repo){
        if($this->getUser()->getRole() != 3){

            foreach ($repo->findAll() as $supplier) {
                $manager->remove($supplier);
                $manager->flush();
            }
            return $this->redirectToRoute('allSuppliers');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

}
