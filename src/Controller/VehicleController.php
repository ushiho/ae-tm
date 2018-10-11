<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Form\VehicleFormType;
use App\Repository\VehicleRepository;
use App\Repository\VehicleTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleController extends AbstractController
{
    /**
     * @Route("/vehicle", name="allVehicles")
     */
    public function show(VehicleRepository $repo, VehicleTypeRepository $typeRepo)
    {
        return $this->render('vehicle/vehicleBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'vehicles' => $repo->findAll(),
            'types' => $typeRepo->findAll(),
        ]);
    }

    /**
     * @Route("/vehicle/new", name="addVehicle")
     * @Route("/vehicle/edit/{id}", name="editVehicle")
     * @Route("/vehicle/new/{idType}", name="addByType", requirements={"idType"="\d+"})
     */
    public function action($idType=null, Vehicle $vehicle=null, ObjectManager $manager, Request $request,
        VehicleTypeRepository $typeRepo){
        if($vehicle==null){
            $vehicle = new Vehicle();
        }
        if($idType!=null){
            $vehicle->setType($typeRepo->find($idType));
        }
        $form = $this->createForm(VehicleFormType::class, $vehicle);
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
            'types' => $typeRepo->findAll(),
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