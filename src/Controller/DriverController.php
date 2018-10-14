<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Form\DriverType;
use App\Entity\VehicleType;
use App\Repository\DriverRepository;
use App\Repository\VehicleRepository;
use App\Repository\VehicleTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DriverController extends AbstractController
{
    /**
     * @Route("/driver", name="allDrivers")
     * @Route("/driver/type/{id}", name="showDriverByType")
     */
    public function show(DriverRepository $repo, Request $request, VehicleType $type=null)
    {
        $drivers = [];
        if($type && $request->attributes->get('_route')=="showDriverForType"){
            $drivers = $repo->findByType($type);
        }
        else{
            $drivers = $repo->findAll();
        }
        return $this->render('driver/driverBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'drivers' => $drivers,
        ]);
    }

    /**
     * @Route("/driver/new", name="addDriver")
     * @Route("driver/edit/{id}", name="editDriver")
     */
    public function action(Request $request, ObjectManager $manager, Driver $driver=null,
    VehicleTypeRepository $typeRepo)
    {
        if($driver==null){
            $driver = new Driver();
        }
        $form = $this->createForm(DriverType::class, $driver);
        dd($request->attributes->get('driver')->getVehicleType());
        // $driver->addVehicleType(VehicleType ());
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $driver->setSalairePerDay(salaryPerDay($driver));
            $manager->persist($driver);
            $manager->flush();
            return $this->redirectToRoute('allDrivers');
        }
        return $this->render('driver/driverForm.html.twig', [
            'form' => $form->createView(),
            'connectedUser' => $this->getUSer(),
            'driver' => $driver,
        ]);
    }

    /**
     * @Route("/driver/delete/{id}", name="deleteDriver")
     */
    public function delete($id, ObjectManager $manager, DriverRepository $repo){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute('allDrivers');
    }

    /**
     * @Route("driver/show/{id}", name="showDriver")
     */
    public function showDetails(Driver $driver=null){
        if($driver){
            return $this->render('driver/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'driver' => $driver,
            ]);
        }
    }

    /**
     * @Route("driver/deleteAll", name="deleteAllDrivers")
     */
    public function deleteAll(ObjectManager $manager, DriverRepository $repo){
        foreach ($repo->findAll() as $driver) {
            $manager->remove($driver);
            $manager->flush();
        }
        return $this->redirectToRoute('allDrivers');
    }

    public function salaryPerDay(Driver $driver){
        if($driver){
            $salary = 0;
            if($driver->getPeriodOfTravel==1){
                $salary = $driver->getSalaire();
            }else if($driver->getPeriodOfTravel==20){
                $salary = $driver->getSalaire()/7;                
            }else{
                $salary = $driver->getSalaire()/30;
            }
            return $salary;
        }
    }
}
