<?php

namespace App\Controller;

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
    public function show(AllocateRepository $repo, Supplier $supplier = null,
    SupplierRepository $supplierRepo)
    {
        if($this->getUser()->getRole() != 3){

            $rents = [];
            if ($supplier) {
                $rents = $repo->findBySupplier($supplier);
            } else {
                $rents = $repo->findAll();
            }
    
            return $this->render('rent/rentBase.html.twig', [
                'connectedUser' => $this->getUser(),
                'Rents' => $rents,
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/rent/new", name="addRent")
     * @Route("/rent/edit/{id}", name="editRent", requirements={"id"="\d+"})
     * @Route("/rent/mission/{idMission}", name="addForMission", requirements={"idMission"="\d+"})
     */
    public function action(Request $request, ObjectManager $manager, Allocate $rent = null, $idMission = null,
                    MissionRepository $missionRepo)
    {
        if($this->getUser()->getRole() != 3){

            if ($rent == null) {
                $rent = new Allocate();
            }
            if ($idMission != null) {
                $rent->setMission($missionRepo->find($idMission));
            }
            $form = $this->createForm(RentType::class, $rent);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // if(compare two date start date of rent and start date of mission )
                $rent = $this->checkIfFinished($rent);
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
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/rent/delete/{id}", name="deleteRent", requirements={"id"="\d+"})
     */
    public function delete($id, AllocateRepository $repo, ObjectManager $manager)
    {
        if($this->getUser()->getRole() != 3){

            $manager->remove($repo->find($id));
            $manager->flush();
    
            return $this->redirectToRoute('allRents');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/rent/show/{idRent}", name="showRent", requirements={"idRent"="\d+"})
     */
    public function showDetails(AllocateRepository $repo, $idRent = 0)
    {
        if($this->getUser()->getRole() != 3){

            $rent = $repo->find($idRent);
            if ($rent) {
                return $this->render('rent/show.html.twig', [
                    'rent' => $rent,
                    'connectedUser' => $this->getUser(),
                ]);
            }
    
            return $this->redirectToRoute('allRents');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/rent/deleteAll", name="deleteAllRents")
     */
    public function deleteAll(AllocateRepository $repo, ObjectManager $manager)
    {
        if($this->getUser()->getRole() != 3){

            foreach ($repo->findAll() as $rent) {
                $manager->remove($rent);
                $manager->flush();
            }
    
            return $this->redirectToRoute('allRents');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/project/mission/new/add_rent", name="stepFour")
     */
    public function addMissionStepTree(Request $request, ObjectManager $manager)
    {
        if($this->getUser()->getRole() != 3){

            $session = $request->getSession();
            if ($session->get('mission'))  {
                $rent = $request->getSession()->get('rent');
                if ($rent && !empty((array) $rent)) {
                    $rent = $this->merge($rent);
                } else {
                    $rent = new Allocate();
                }
                $form = $this->createForm(RentType::class, $rent);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    if (MissionController::verifyDates($session->get('mission'), $rent)) {
                    if ($this->generateMsg($request->getSession(), $rent)) {
                        $rent->setFinished($this->verifyDateWithNewDate($rent->getEndDate()))
                            ->setPricePerDay($this->pricePerDay($rent));
                        $request->getSession()->set('rent', $rent);
    
                        return $this->redirectToRoute('verifyDatas');
                    }
                } else {
                        $request->getSession()->getFlashBag()->add('rentMsg', 'Attention the  date of the mission should be included inside the date of the rent!');
                    }
                }
    
                return $this->render('mission/rentForm.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'form' => $form->createView(),
                ]);
            } else {
                $request->getSession()->getFlashBag()->add('vehicleError', 'You must add the mission Information to continue!');
    
                return $this->redirectToRoute('stepTree');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function merge(Allocate $rent, ObjectManager $manager)
    {
        if ($rent) {
            $rent->setSupplier($manager->merge($rent->getSupplier()))
                ->setVehicle($manager->merge($rent->getVehicle()));

            return $rent;
        } else {
            return null;
        }
    }

    public function pricePerDay(Allocate $rent)
    {
        $salary = 0;
        if ($rent) {
            if ($rent->getPeriod() == 1) {
                $salary = $rent->getPrice();
            } elseif ($rent->getPeriod() == 2) {
                $salary = $rent->getPrice() / 7;
            } else {
                $salary = $rent->getPrice() / 30;
            }
        }

        return $salary;
    }

    public function generateMsg(SessionInterface $session, Allocate $rent)
    {
        $mission = $session->get('mission');
        if ($mission && $rent) {
            if ($rent->getWithDriver() && ($mission->getSalaire() == 0 && $rent->getPeriod() == $mission->getPeriodOfWork())) {
                return true;
            }
            if (!$rent->getWithDriver() && $mission->getSalaire() != 0) {
                return true;
            }
            if ($rent->getWithDriver() && ($mission->getSalaire() != 0 || $mission->getPeriodOfTravel() != $rent->getPeriod())) {
                $session->getFlashBag()->add('rentMsg', "The vehicle was rented with the driver but the driver's salary was set or the period of work the driver and the period of rent are not matching, you must change the driver's salary or the period of work or change the rent's info to continue the process!");

                return false;
            }
            if (!$rent->getWithDriver() && $mission->getSalaire() == 0) {
                $session->getFlashBag()->add('rentMsg', "The vehicle was not rented with driver but the driver's salary was not set, you must change the driver's salary or change the state of the rent!");

                return false;
            }
        }
    }

    public function checkIfFinished(Allocate $rent)
    {
        if ($rent) {
            if ($rent->getEndDate()->format('U') >= (new \Date())->format('U')) {
                $rent->setFinished(true);
            } else {
                $rent->setFinished(false);
            }
        }

        return $rent;
    }

    private function verifyDateWithNewDate(\DateTime $date)
    {
        return $date->format('U') >= (new \DateTime())->format('U');
    }
}
