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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RentController extends AbstractController
{
    /**
     * @Route("/rent", name="allRents")
     * @Route("/rent/supplier/{id}", name="showRentsBySupplier", requirements={"id"="\d+"})
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
     * @Route("/rent/edit/{id}", name="editRent", requirements={"id"="\d+"})
     * @Route("/rent/mission/{idMission}", name="addForMission", requirements={"idMission"="\d+"})
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
            // if(compare two date start date of rent and start date of mission )
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
     * @Route("/rent/delete/{id}", name="deleteRent", requirements={"id"="\d+"})
     */
    public function delete($id, AllocateRepository $repo, ObjectManager $manager){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute("allRents");
    }

    /**
     * @Route("/rent/show/{idRent}", name="showRent", requirements={"idRent"="\d+"})
     */
    public function showDetails(AllocateRepository $repo, $idRent=0){
        $rent = $repo->find($idRent);
        if($rent){
            return $this->render('rent/show.html.twig', [
                'rent' => $rent,
                'connectedUser' => $this->getUser(),
            ]);
        }
        return $this->redirectToRoute('allRents');
    }

    /**
     * @Route("/rent/deleteAll", name="deleteAllRents")
     */
    public function deleteAll(AllocateRepository $repo, ObjectManager $manager){
        foreach ($repo->findAll() as $rent) {
            $manager->remove($rent);
            $manager->flush();
        }
        return $this->redirectToRoute('allRents');
    }

    /**
     * @Route("/project/mission/new/add_rent", name="stepTree")    * 
     */
    public function addMissionStepTree(Request $request, SessionInterface $session, ObjectManager $manager){
        if($session->get('vehicle')){
            $rent = $session->get('rent');
            if($rent){
                $rent = $manager->merge($rent);
            }else{
                $rent = new Allocate();
            }
            $form = $this->createForm(RentType::class, $rent);
            $form->handleRequest($request);

            if($form->isSubmitted()&&$form->isValid()){
                $session->set('rent', $rent);
                return $this->redirectToRoute('stepFour');
            }
            return $this->render('mission/rentForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
            ]);
        }else{
            $session->getFlashBag()->add('vehicleError', "You must add the vehicle Information to continue!");
            return $this->redirectToRoute('stepTwo');
        }
    }

    public function merge(Allocate $rent, ObjectManager $manager){
        if($rent){
            $rent->setSupplier($manager->merge($rent->getSupplier()));
            return $rent;
        }else{
            return null;
        }
    }
}
