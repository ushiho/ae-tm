<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Department;
use App\Entity\VehicleType;
use App\Form\VehicleTypeType;
use App\Repository\VehicleTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleTypeFormController extends AbstractController
{
    /**
     * @Route("/vehicle/type", name="allTypes")
     * @Route("vehicle/type/driver/{vehicleTypes}", name="showVehicleTypeByDriver")
     */
    public function show(VehicleTypeRepository $repo, Driver $driver=null, Request $request,
    array $vehicleTypes=null)
    {
        if ($this->getUser()->getRole() != 3) {
            $types = [];
            if($driver && $request->attributes->get('_route')=="showVehicleTypeByDriver"){
                $types = $vehicleTypes;
            }else{
                $types = $repo->findAll();
            }
            return $this->render('vehicle_type_form/vehicleTypeBase.html.twig', [
                'connectedUser' => $this->getUser(),
                'types' => $types,
            ]);
        }
        return $this->redirectToRoute('error403');
    }

    /**
     * @Route("/vehicle/type/add", name="addType")
     * @Route("/vehicle/type/edit/{id}", name="editType", requirements={"idType"="\d+"})
     */
    public function action(VehicleType $type=null, Request $request, ObjectManager $manager){
        if ($this->getUser()->getRole() != 3) {
            if($type==null){
                $type = new VehicleType();
            }
            $form = $this->createForm(VehicleTypeType::class, $type);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $manager->persist($type);
                $manager->flush();
                $request->getSession()->getFlashBag()->add('typeSuccess', 'The type '.$type->getName().' is added successfully!');
                return $this->redirectToRoute('allTypes');
            }
            return $this->render('vehicle_type_form/vehicleTypeForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
                'type' => $type,
            ]);
        }
        return $this->redirectToRoute('error403');

    }

    /**
     * @Route("/vehicle/type/delete/{id}", name="deleteType", requirements={"idType"="\d+"})
     */
    public function delete($id, VehicleTypeRepository $repo, ObjectManager $manager){
        if($this->getUser()->getRole() != 3){

            $manager->remove($repo->find($id));
            $manager->flush();
            return $this->redirectToRoute('allTypes');
        }
        return $this->redirectToRoute('error403');
    }

    /**
     * @Route("/vehicle/type/show/{id}", name="showVehicleType", requirements={"idType"="\d+"})
     */
    public function showDetails(VehicleType $vehicleType=null){
        if ($this->getUser()->getRole() != 3) {
            if($vehicleType){
                return $this->render('vehicle_type_form/show.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'vehicleType' => $vehicleType,
                ]);
            }
            return $this->redirectToRoute('allTypes');
        }
        return $this->redirectToRoute('error403');

    }

    /**
     * @Route("/vehicle/type/deleteAll/", name="deleteAllTypes")
     */
    public function deleteAll(ObjectManager $manager, VehicleTypeRepository $repo){
        if ($this->getUser()->getRole() != 3) {
            foreach($repo->findAll() as $type){
                $manager->remove($type);
                $manager->flush();
            }
            return $this->redirectToRoute('allTypes');
        }
        return $this->redirectToRoute('error403');
    }
}
