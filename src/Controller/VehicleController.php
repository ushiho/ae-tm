<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\Form\VehicleFormType;
use App\Repository\VehicleRepository;
use App\Repository\VehicleTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleController extends AbstractController
{
    /**
     * @Route("/vehicle", name="allVehicles")
     * @Route("/vehicle/type/{idType}", name="showVehiclesByType")
     */
    public function show(VehicleRepository $repo, VehicleTypeRepository $typeRepo,
    VehicleType $type=null, Request $request)
    {
        $vehicles = [];
        if($type && $request->attributes->get('_route')=="showVehiclesByType"){
            $vehicles = $repo->findByType($type);
        }else{
            $vehicles = $repo->findAll();
        }
        return $this->render('vehicle/vehicleBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'types' => $typeRepo->findAll(),
            'vehicles' => $vehicles,
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

    /**
     * @Route("/vehicle/show/{id}", name="showVehicle")
     */
    public function showDetails(Vehicle $vehicle=null, VehicleTypeRepository $typeRepo){
        if($vehicle){
            return $this->render('vehicle/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'vehicle' => $vehicle,
                'types' => $typeRepo->findAll(),
            ]);
        }
        return $this->redirectToRoute('allVehicles');
    }

    /**
     * @Route("/vehicle/deleteAll", name="deleteAllVehicles")
     */
    public function deleteAll(ObjectManager $manager, VehicleRepository $repo){
        foreach($repo->findAll() as $vehicle ){
            $manager->remove($vehicle);
            $manager->flush();
        }
        return $this->redirectToRoute('allVehicles');
    }

    /**
     * @Route("/project/mission/new/add_vehicle", name="stepTwo")
     */
    public function addMissionStepTwo(Request $request, SessionInterface $session, ObjectManager $manager){
        if($session->get('driver')){
            $vehicle = $session->get('vehicle');
            if($vehicle!=null){
                $vehicle = $manager->merge($vehicle);
            }else{
                $vehicle = new Vehicle();
            }
            $error=$session->get('vehicleError');
            $form = $this->createForm(VehicleFormType::class, $vehicle);
            $form->handleRequest($request);
            if($form->isSubmitted()&&$form->isValid()){
                if($this->typeExistsInArray($vehicle, $session->get('driver')->getVehicleType()->toArray())){
                    $session->set('vehicle', $vehicle);
                    return $this->redirectToRoute('stepTree');
                }else if($vehicle->getType()){
                    $error = "The driver ".$session->get('driver')->getFirstName()." can't driver this type of vehicle (".$vehicle->getType()->getName()."), Please specify
                    another type.";
                }
            }
            return $this->render('mission/vehicleForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
                'error' => $error,
            ]);
        }else{
            $session->set('driverError',"Note: This is the first step for creating a mission!");
            return $this->redirectToRoute('stepOne');
        }
    }

    public function typeExistsInArray(Vehicle $vehicle, array $types){
        foreach ($types as $type) {
            if($type->getId() == $vehicle->getType()->getId()){
                return true;
            }
        }
        return false;
    }
}