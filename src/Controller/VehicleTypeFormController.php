<?php

namespace App\Controller;

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
     */
    public function show(VehicleTypeRepository $repo)
    {
        return $this->render('vehicle_type_form/vehicleTypeBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'types' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/vehicle/type/add", name="addType")
     * @Route("/vehicle/type/edit/{id}", name="editType")
     */
    public function action(VehicleType $type=null, Request $request, ObjectManager $manager){
        if($type==null){
            $type = new VehicleType();
        }
        $form = $this->createForm(VehicleTypeType::class, $type);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($type);
            $manager->flush();
            return $this->redirectToRoute('allTypes');
        }
        return $this->render('vehicle_type_form/vehicleTypeForm.html.twig', [
            'connectedUser' => $this->getUser(),
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }

    /**
     * @Route("/vehicle/type/delete/{id}", name="deleteType")
     */
    public function delete($id, VehicleTypeRepository $repo, ObjectManager $manager){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute('allTypes');
    }
}
