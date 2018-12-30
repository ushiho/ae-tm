<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Entity\VehicleType;
use App\Form\VehicleFormType;
use App\Repository\VehicleRepository;
use App\Repository\VehicleTypeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Allocate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\DriverRepository;

class VehicleController extends AbstractController
{
    /**
     * @Route("/vehicle", name="allVehicles")
     * @Route("/vehicle/type/{idType}", name="showVehiclesByType", requirements={"idType"="\d+"})
     */
    public function show(VehicleRepository $repo, VehicleTypeRepository $typeRepo,
        VehicleType $type = null, Request $request)
    {
        $vehicles = [];
        if ($type && $request->attributes->get('_route') == 'showVehiclesByType') {
            $vehicles = $this->setTheState($repo->findByType($type));
        } else {
            $vehicles = $this->setTheState($repo->findAll());
        }
        $searchForm = $this->searchForm();
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $vehicles = $searchForm->getData();
        }

        return $this->render('vehicle/vehicleBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'types' => $typeRepo->findAll(),
            'vehicles' => $vehicles,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    /**
     * @Route("/vehicle/new", name="addVehicle")
     * @Route("/vehicle/edit/{id}", name="editVehicle", requirements={"id"="\d+"})
     * @Route("/vehicle/new/{idType}", name="addByType", requirements={"idType"="\d+"})
     */
    public function action($idType = null, Vehicle $vehicle = null, ObjectManager $manager, Request $request,
        VehicleTypeRepository $typeRepo)
    {
        if ($vehicle == null) {
            $vehicle = new Vehicle();
        }
        if ($idType != null) {
            $vehicle->setType($typeRepo->find($idType));
        }
        $form = $this->createForm(VehicleFormType::class, $vehicle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $imageName = $this->generateUniqueFileName().'.'.$image->guessExtension();
                try {
                    $image = $image->move($this->getParameter('Vehicle_Images'), $imageName);
                } catch (FileException $e) {
                    $request->getSession()->getFlashBag()->add('uploadError', 'Sorry, There were a problem with uploading please try again.');

                    return $this->redirectToRoute('addVehicle');
                }
                $vehicle->setImage($imageName);
            }
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
     * @Route("/vehicle/delete/{id}", name="deleteVehicle", requirements={"id"="\d+"})
     */
    public function delete($id, VehicleRepository $repo, ObjectManager $manager)
    {
        $manager->remove($repo->find($id));
        $manager->flush();

        return $this->redirectToRoute('allVehicles');
    }

    /**
     * @Route("/vehicle/show/{id}", name="showVehicle", requirements={"id"="\d+"})
     */
    public function showDetails(Vehicle $vehicle = null, VehicleTypeRepository $typeRepo)
    {
        if ($vehicle) {
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
    public function deleteAll(ObjectManager $manager, VehicleRepository $repo)
    {
        foreach ($repo->findAll() as $vehicle) {
            $manager->remove($vehicle);
            $manager->flush();
        }

        return $this->redirectToRoute('allVehicles');
    }

    /**
     * @Route("/project/mission/new/add_vehicle", name="stepTwo")
     */
    public function addMissionStepTwo(Request $request, SessionInterface $session, ObjectManager $manager, DriverRepository $driverRepo)
    {
        if ($session->get('driver')) {
            $vehicle = $session->get('vehicle');
            if ($vehicle && !empty($vehicle)) {
                $vehicle = $manager->merge($vehicle);
            } else {
                $vehicle = new Vehicle();
            }
            $searchForm = $this->searchForm();
            $searchForm->handleRequest($request);
            $form = $this->createForm(VehicleFormType::class, $vehicle);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                return $this->toStepThree($vehicle, $request, $driverRepo);
            }
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $vehicle = $manager->merge($this->checkVehicleState($searchForm->getData()['matricule']));
                if ($vehicle->getState() != 'Busy') {
                    return $this->toStepThree($vehicle, $request, $driverRepo);
                } else {
                    $request->getSession()->getFlashBag()->add('vehicleError', 'The vehicle selected is busy.');
                }
            }

            return $this->render('mission/vehicleForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
                'searchForm' => $searchForm->createView(),
            ]);
        } else {
            $session->getFlashBag()->add('driverError', 'This is the first step in creating the mission process!');

            return $this->redirectToRoute('stepOne');
        }
    }

    public function toStepThree(Vehicle $vehicle, Request $request, DriverRepository $driverRepo)
    {
        $driver = $request->getSession()->get('driver');
        if ($driver->getId()) {
            $driver = $driverRepo->find($request->getSession()->get('driver')->getId());
        }
        // dd($driver);
        $resTest = $this->typeExistsInArray($vehicle, $driver->getVehicleType()->toArray());
        if ($resTest) {
            $request->getSession()->set('vehicle', $vehicle);

            return $this->redirectToRoute('stepTree');
        } else {
            $request->getSession()->getFlashBag()->add('vehicleError', 'The driver '.$driver->getFirstName()." can't drive this type of vehicle (".$vehicle->getType()->getName().'), Please specify
            another type.');

            return $this->redirectToRoute('stepTwo');
        }
    }

    public function typeExistsInArray(Vehicle $vehicle, array $types)
    {
        if ($vehicle->getType()) {
            foreach ($types as $value) {
                if ($value->getId() == $vehicle->getType()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function merge(Vehicle $vehicle, ObjectManager $manager)
    {
        if ($vehicle) {
            $vehicle->setType($manager->merge($vehicle->getType()));

            return $vehicle;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return (new \DateTime())->format('dmyHs');
    }

    public function checkVehicleState(Vehicle $vehicle = null)
    {
        if ($vehicle) {
            $rent = $this->getDoctrine()->getManager()->getRepository(Allocate::class)->findByVehicle($vehicle);
            //rent = null => No mission exist so no rent exist => create it!
            if ($rent == null) {
                //No rent exist for this vehicle
                $vehicle->setState('Available, No rent exist');
            } else {
                //rent exist, check the state of its mission
                $mission = $this->getDoctrine()->getManager()->getRepository(Mission::class)->findByRent($rent);
                if ($mission->getFinished()) {
                    // mission is finished
                    // you can start a new mission

                    $vehicle->setState('Available for '.$this->daysBetween($rent->getEndDate(), new \Date()).' days.');
                } else {
                    //its mission is not finished yet! the vehicle is busy
                    $vehicle->setState('Busy');
                }
            }

            return $vehicle;
        }
    }

    public function daysBetween(String $dt1, String $dt2)
    {
        return date_diff(
            date_create($dt2),
            date_create($dt1)
        )->format('%a');
    }

    public function setTheState($vehicles)
    {
        $vehiclesWithState = [];
        foreach ($vehicles as $vehicle) {
            $vehicle = $this->checkVehicleState($vehicle);
            $vehiclesWithState[] = $vehicle;
        }

        return $vehiclesWithState;
    }

    /**
     * @Route("/project/mission/new/by_vehicle/{id}", name="addMissionToVehicle", requirements={"id"="\d+"})
     */
    public function addMissionToVehicle(Request $request, Vehicle $vehicle = null)
    {
        if ($vehicle) {
            $request->getSession()->set('vehicle', $vehicle);

            return $this->redirectToRoute('addMission');
        }
    }

    public function searchForm()
    {
        return $this->createFormBuilder(null)
                            ->add('matricule', EntityType::class, array(
                            'class' => Vehicle::class,
                            'required' => true,
                            'choice_label' => function (Vehicle $vehicle) {
                                return $vehicle->getMatricule().' - '.$vehicle->getType()->getName().' - '.$vehicle->getBrand();
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
}
