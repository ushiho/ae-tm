<?php

namespace App\Controller;

use DateTime;
use App\Form\RentType;
use App\Entity\Allocate;
use App\Entity\Supplier;
use App\Repository\MissionRepository;
use App\Repository\AllocateRepository;
use App\Repository\SupplierRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RentController extends AbstractController
{
    /**
     * @Route("/rent", name="allRents")
     * @Route("/rent/supplier/{id}", name="showRentsBySupplier")
     */
    public function show(AllocateRepository $repo, Supplier $supplier=null, 
    SupplierRepository $supplierRepo)
    {
        $rents = [];
        if($supplier){
            $rents = $repo->findBySupplier($supplier);
        }else{
            $rents = $repo->findAll();
        }
        return $this->render('rent/rentBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'Rents' => $rents,
        ]);
    }

    /**
     * @Route("/rent/new", name="addRent")
     * @Route("/rent/edit/{id}", name="editRent")
     * @Route("/rent/mission/{idMission}", name="addForMission")
     */
    public function action(Request $request, ObjectManager $manager, Allocate $rent=null, $idMission=null,
                    MissionRepository $missionRepo){
        if($rent==null){
            $rent = new Allocate();
        }
        if($idMission!=null){
            $rent->setMission($missionRepo->find($idMission));
        }
        $form = $this->createForm(RentType::class, $rent);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $rent->setCreatedAt(new \DateTime());
            $manager->persist($rent);
            $manager->flush();
            return $this->redirectToRoute('allRents');
        }
        return $this->render('rent/rentForm.html.twig', [
            'connectedUser' => $this->getUser(),
            'form' => $form->createView(),
            'rent' => $rent,
        ]);
    }

    /**
     * @Route("/rent/delete/{id}", name="deleteRent")
     */
    public function delete($id, AllocateRepository $repo, ObjectManager $manager){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute("allRents");
    }

    /**
     * @Route("/rent/show/{idRent}", name="showRent")
     */
    public function showDetails(Allocate $rent=null){
        if($rent){
            return $this->render('rent/show.html.twig', [
                'rent' => $rent,
                'connectedUser' => $this->getUser(),
            ]);
        }
        return $this->redirectToRoute('allRents');
    }
}
