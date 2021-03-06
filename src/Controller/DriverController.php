<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Form\DriverType;
use App\Entity\VehicleType;
use App\Repository\DriverRepository;
use App\Repository\VehicleTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DriverController extends AbstractController
{
    /**
     * @Route("/driver", name="allDrivers")
     * @Route("/driver/type/{id}", name="showDriverByType")
     */
    public function show(DriverRepository $repo, Request $request, VehicleType $type = null)
    {
        if($this->getUser()->getRole() != 3){
            
            $drivers = [];
            if ($type && $request->attributes->get('_route') == 'showDriverForType') {
                $drivers = $repo->findByType($type);
            } else {
                $drivers = $repo->findAll();
            }
            $searchForm = $this->searchForm();
            $searchForm->handleRequest($request);
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $drivers = $searchForm->getData();
            }
    
            return $this->render('driver/driverBase.html.twig', [
                     'connectedUser' => $this->getUser(),
                     'drivers' => $drivers,
                     'searchForm' => $searchForm->createView(),
                    ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function checkIfBusy(array $drivers)
    {
        $busy = array();
        $notBusy = array();
        foreach ($drivers as $driver) {
            if ($driver->getBusy()) {
                $busy[] = $driver;
            } else {
                $notBusy[] = $driver;
            }
        }

        return array(
            'busy' => $busy,
            'notBusy' => $notBusy,
        );
    }

    /**
     * @Route("/driver/new", name="addDriver")
     * @Route("driver/edit/{id}", name="editDriver")
     */
    public function action(Request $request, ObjectManager $manager, Driver $driver = null,
    VehicleTypeRepository $typeRepo)
    {
        if($this->getUser()->getRole() != 3){

            if ($driver == null) {
                $driver = new Driver();
            }
            $form = $this->createForm(DriverType::class, $driver);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $driver->setSalairePerDay($this->salaryPerDay($driver));
                $driver->setBusy($this->isBusy($driver));
                $manager->persist($driver);
                $manager->flush();
    
                return $this->redirectToRoute('allDrivers');
            }
    
            return $this->render('driver/driverForm.html.twig', [
                'form' => $form->createView(),
                'connectedUser' => $this->getUSer(),
                'driver' => $driver,
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/driver/delete/{id}", name="deleteDriver")
     */
    public function delete($id, ObjectManager $manager, DriverRepository $repo)
    {
        if($this->getUser()->getRole() != 3){

            $manager->remove($repo->find($id));
            $manager->flush();
    
            return $this->redirectToRoute('allDrivers');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("driver/show/{id}", name="showDriver")
     */
    public function showDetails(Driver $driver = null)
    {
        if($this->getUser()->getRole() != 3){

            if ($driver) {
                return $this->render('driver/show.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'driver' => $driver,
                ]);
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("driver/deleteAll", name="deleteAllDrivers")
     */
    public function deleteAll(ObjectManager $manager, DriverRepository $repo)
    {
        if($this->getUser()->getRole() != 3){

            foreach ($repo->findAll() as $driver) {
                $manager->remove($driver);
                $manager->flush();
            }
    
            return $this->redirectToRoute('allDrivers');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function salaryPerDay(Driver $driver)
    {
        $salary = 0;
        if ($driver) {
            if ($driver->getPeriodOfTravel() == 1) {
                $salary = $driver->getSalaire();
            } elseif ($driver->getPeriodOfTravel() == 2) {
                $salary = $driver->getSalaire() / 7;
            } else {
                $salary = $driver->getSalaire() / 30;
            }
        }

        return $salary;
    }

    /**
     * @Route("/project/mission/new/add_driver", name="stepOne")
     * @Route("/project/mission/new/add_driver/cancel", name="cancelStepOne")
     */
    public function addMissionStepOne(Request $request, SessionInterface $session, ObjectManager $manager)
    {
        if($this->getUser()->getRole() != 3){

            if ($request->attributes->get('_route') == 'stepOne') {
                $driver = $session->get('driver');
                if ($driver && $driver->getId()) {
                    $driver = $manager->merge($driver);
                } else {
                    $driver = new Driver();
                }
                $searchForm = $this->searchForm();
                $searchForm->handleRequest($request);
                $form = $this->createForm(DriverType::class, $driver);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    return $this->toStepTwo($request, $driver);
                }else if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                    $driver = $manager->merge($searchForm->getData()['firstName']);
                    if ($driver->getId()) {
                        if (!$driver->getBusy()) {
                            return $this->toStepTwo($request, $driver);
                        } else {
                            $session->getFlashBag()->add('driverErro', 'The Driver selected has a mission not finished yet.');
                        }
                    }
                }
    
                return $this->render('mission/driverForm.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'form' => $form->createView(),
                    'searchForm' => $searchForm->createView(),
                ]);
            } else {
                $session->clear();
                $session->getFlashBag()->add('missionCancel', 'The process has been canceled by the user!');
    
                return $this->redirectToRoute('allMissions');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function toStepTwo(Request $request, $driver)
    {
        $request->getSession()->set('driver', $driver);

        return $this->redirectToRoute('stepTwo');
    }

    public function isBusy($driver)
    {
        if ($driver->getMissions() == null || $driver->getMissions()->isEmpty()) {
            return false;
        }
        foreach ($driver->getMissions() as $mission) {
            if (!$mission->getFinished()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/driver/{id}/mission/new", name="addMissionToDriver")
     */
    public function addMissionToDriver(Driver $driver = null, SessionInterface $session)
    {
        if($this->getUser()->getRole() != 3){

            if ($driver && !$driver->getBusy()) {
                $session->set('driver', $driver);
    
                return $this->redirectToRoute('stepOne');
            } else {
                $session->getFlashBag()->add('driverError', 'The driver '.$driver->getFirstName().' is busy, he has a mission not finished!');
    
                return $this->redirectToRoute('allDrivers');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function merge(Driver $driver, ObjectManager $manager)
    {
        if ($driver) {
            $types = new ArrayCollection();
            foreach ($driver->getVehicleType() as $type) {
                $types[] = $manager->merge($type);
            }

            return $types;
        } else {
            return null;
        }
    }

    public function searchForm()
    {
        return $this->createFormBuilder(null)
                    ->add('firstName', EntityType::class, array(
                        'class' => Driver::class,
                        'required' => true,
                        'choice_label' => function (Driver $driver) {
                            return $driver->getLastName().' - '.$driver->getFirstName().' - '.$driver->getCin().' - '.$this->driverVehicleType($driver->getVehicleType()->toArray());
                        },
                        'placeholder' => 'Select a value',
                        'attr' => array(
                        'class' => 'bootstrap-select',
                        'data-live-search' => 'true',
                        'data-width' => '100%',
                        'style' => 'cursor: pointer;',
                        ),
                    ))
                ->getForm();
    }

    public function driverVehicleType(array $vehcileTypes)
    {
        $types = ' ';
        foreach ($vehcileTypes as $type) {
            $types .= $type->getName().', ';
        }

        return $types;
    }
}
