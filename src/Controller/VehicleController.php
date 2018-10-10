<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Repository\VehicleRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleController extends AbstractController
{
    /**
     * @Route("/vehicle", name="allVehicles")
     */
    public function show(VehicleRepository $repo)
    {
        return $this->render('vehicle/vehicleBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'vehicles' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/vehicle/new", name="addVehicle")
     * @Route("/vehicle/edit/{id}", name="editVehicle")
     */
    public function action(Vehicle $vehicle=null, ObjectManager $manager, Request $request){
        if($vehicle==null){
            $vehicle = new Vehicle();
        }
        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($vehicle);
            $manager->flush();
            return $this->redirectToRoute('allVehicles');
        }
        return $this->render('/vehicle/vehicleForm.html.twig', [
            'form' => $form->createView(),
            'connectedUser' => $this->getUser(),
            'vehicle' => $vehicle,
        ]);
    }

    /**
     * @Route("/vehicle/delete/{id}", name="deleteVehicle")
     */
    public function delete($id, VehicleRepository $repo, ObjectManager $manager){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute('allVehicles');
    }
}