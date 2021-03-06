<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Project;
use App\Entity\Allocate;
use App\Form\MissionType;
use App\Entity\Department;
use App\Repository\DriverRepository;
use App\Repository\MissionRepository;
use App\Repository\ProjectRepository;
use App\Repository\DepartmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MissionController extends AbstractController
{
    /**
     * @Route("/mission", name="allMissions")
     * @Route("/mission/department/{idDepartment}", name="showMissionOfDepartment")
     * @Route("/mission/project/{idProject}", name="showMissionOfProject")
     * @Route("/mission/driver/{idDriver}", name="showMissionsByDriver")
     */
    public function index(MissionRepository $repo, ProjectRepository $projectRepo, DepartmentRepository $depaRepo, DriverRepository $driverRepo, $idDepartment = null, Request $request, $idProject = null, $idDriver = null)
    {
        if($this->getUser()->getRole() != 3){

            $missions = [];
            if ($idDepartment && $request->attributes->get('_route') == 'showMissionOfDepartment') {
                $missions = $repo->findByDepartment($depaRepo->find($idDepartment));
            } elseif ($idProject && $request->attributes->get('_route') == 'showMissionOfProject') {
                $missions = $repo->findByProject($projectRepo->find($idProject));
            } elseif ($idDriver && $request->attributes->get('_route') == 'showMissionsByDriver') {
                $missions = $repo->findByDriver($driverRepo->find($idDriver));
            } else {
                $missions = $repo->findAll();
            }
    
            return $this->render('mission/missionBase.html.twig', [
                'missions' => $missions,
                'connectedUser' => $this->getUser(),
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    //  /**
    //  * @Route("/mission/new", name="addMission")
    //  * @Route("mission/edit/{id}", name="editMission")
    //  */
    // public function action(Request $request, ObjectManager $manager, Mission $mission=null,
    // MissionRepository $repo, $idProject=null, ProjectRepository $projectRepo){
    //     if($mission==null){
    //         $mission = new Mission();
    //     }
    //     if($idProject){
    //         $mission->setProject($projectRepo->find($idProject));
    //     }
    //     $alert = "";
    //     $missionForm = $this->createForm(MissionType::class, $mission);
    //     $missionForm->handleRequest($request);
    //     dump($missionForm);
    //     if($missionForm->isSubmitted() && $missionForm->isValid()){
    //         $rest = $repo->findMissionByStateByDriver($mission->getDriver(), false);
    //         if(!empty($rest) && $request->attributes->get('_route')=="addMission"){
    //             $alert = "The driver selected has a mission non finished, please select another one.";
    //         } else{
    //             $mission->setCreatedAt(new \DateTime());
    //             $manager->persist($mission);
    //             $manager->flush();
    //             return $this->redirectToRoute('allMissions');
    //         }
    //     }
    //     return $this->render('mission/editMissionForm.html.twig', [
    //         'missionForm' => $missionForm->createView(),
    //         'mission' => $mission,
    //         'connectedUser' => $this->getUser(),
    //         'alert' => $alert,
    //     ]);
    // }

    /**
     * @Route("/mission/delete/{id}", name="deleteMission")
     */
    public function delete($id, ObjectManager $manager, MissionRepository $repo, Request $request)
    {
        if($this->getUser()->getRole() != 3){
            
            $mission = $repo->find($id);
            if ($mission) {
                $manager->remove($mission);
                $manager->flush();
    
                return $this->redirectToRoute('allMissions');
            } else {
                $request->getSession()->getFlashBag()->add('missionError', 'There is no selected mission to delete!');
    
                return $this->redirectToRoute('allMissions');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/mission/deleteAll", name="deleteAll")
     */
    public function deleteAll(MissionRepository $repo, ObjectManager $manager)
    {
        if($this->getUser()->getRole() != 3){

            foreach ($repo->findAll() as $mission) {
                $manager->remove($mission);
                $manager->flush();
            }
    
            return $this->redirectToRoute('allMissions');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/mission/show/{id}", name="showMission")
     */
    public function showDetails(Mission $mission = null)
    {
        if($this->getUser()->getRole() != 3){

            if ($mission) {
                $salary = $mission->getSalaire() * $this->periodFromToNumber($mission->getPeriodOfWork());
                return $this->render('/mission/show.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'mission' => $mission,
                    'driverSalaire' => $salary,
                ]);
            }
    
            return $this->redirectToRoute('allMissions');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/project/mission/new/add_mission", name="stepTree")
     */
    public function addMissionStepThree(Request $request, SessionInterface $session, ObjectManager $manager)
    {
        if($this->getUser()->getRole() != 3){

            if ($request->getSession()->get('vehicle') && $request->get('_route') == 'stepTree') {
                $mission = $session->get('mission');
                if ($mission) {
                    $mission = $manager->merge($mission);
                } else {
                    $mission = new Mission();
                }
                $form = $this->createForm(MissionType::class, $mission);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    // if ($this->verifyDates($mission, $session->get('rent'))) {
                        $mission->setFinished($mission->getEndDate() <= new \DateTime())
                                ->setSalaire($this->salaryPerDay($mission));
                        $session->set('mission', $mission);
                        return $this->redirectToRoute('stepFour');
                    // } else {
                    //     $request->getSession()->getFlashBag()->add('missionError', 'Attention the  date of the mission should be included inside the date of the rent!');
                    // }
                }
    
                return $this->render('/mission/missionForm.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'form' => $form->createView(),
                ]);
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function verifyDates(Mission $mission, Allocate $rent)
    {
        return $mission->getStartDate()->format('U') >= $rent->getStartDate()->format('U') && $mission->getEndDate()->format('U') <= $rent->getEndDate()->format('U');
    }

    public function verifyDateWithNewDate(\DateTime $date)
    {
        return $date->format('U') >= (new \DateTime())->format('U');
    }

    public function daysBetween(String $dt1, String $dt2)
    {
        return date_diff(
            date_create($dt2),
            date_create($dt1)
        )->format('%a');
    }

    /**
     * @Route("/project/mission/new/verify", name="verifyDatas")
     */
    public function verifyDatas(Request $request)
    {
        if($this->getUser()->getRole() != 3){

            $session = $request->getSession();
            if ($session->count() > 0) {
                $data = $this->getDatasFromSession($session);
    
                return $this->render('mission/verifyDatas.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'mission' => $data['mission'],
                    'rent' => $data['rent'],
                    'vehicle' => $data['vehicle'],
                    'driver' => $data['driver'],
                    'driverSalary' => $data['mission']->getSalaire() * $this->periodFromToNumber($data['mission']->getPeriodOfWork())
                ]);
            } else {
                $request->getSession()->getFlashBag()->add('driverError', 'This is the first step in creating mission process!');
    
                return $this->redirectToRoute('stepOne');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    private function getDatasFromSession(SessionInterface $session)
    {
        return array(
            'driver' => $session->get('driver'),
            'vehicle' => $session->get('vehicle'),
            'rent' => $session->get('rent'),
            'mission' => $session->get('mission'),
            'project' => $session->get('project'),
            );
    }

    private function setDatasToSession(SessionInterface $session, Mission $mission)
    {
        $session->set('mission', $mission);
        $session->set('project', $mission->getProject());
        $session->set('driver', $mission->getDriver());
        $session->set('vehicle', $mission->getAllocate()->getVehicle());
        $session->set('rent', $mission->getAllocate());

        return $session;
    }

    private function completeData(SessionInterface $session, ObjectManager $manager)
    {
        if ($session->count() > 0) {
            $data = $this->getDatasFromSession($session);
            $data['driver']->getMissions()->add($data['mission']);
            $data['driver']->setSalairePerDay(DriverController::salaryPerDay($data['driver']));
            $data['driver']->setBusy(DriverController::isBusy($data['driver']))
                           ->setVehicleType(DriverController::merge($data['driver'], $manager));
            $data['vehicle']->setAllocate($data['rent'])
                            ->setType($manager->merge($data['vehicle']->getType()));
            $data['mission']->setDriver($manager->merge($data['driver']))
                            ->setAllocate($data['rent'])
                            ->setCreatedAt(new \DateTime())
                            ->setDepartment($manager->merge($data['mission']->getDepartment()))
                            ->setProject($manager->merge($data['project']))
                            ->setPayment($manager->merge(PaymentController::init($data['mission'])));
            $data['rent']->setCreatedAt(new \DateTime())
                         ->setMission($data['mission'])
                         ->setVehicle($manager->merge($data['vehicle']))
                         ->setSupplier($manager->merge($data['rent']->getSupplier()));
            $data['rent']->setPricePerDay(RentController::pricePerDay($data['rent']));
            $data['project']->getMission()->add($data['mission']);
            return $data;
        } else {
            return null;
        }
    }

    /**
     * @Route("/project/mission/new/validate", name="createMission")
     * @Route("/project/mission/{id}/edit", name="modifyMission")     *
     */
    public function save(SessionInterface $session, ObjectManager $manager, Request $request)
    {
        if($this->getUser()->getRole() != 3){

            $data = $this->completeData($session, $manager);
            if (!$data) {
                $request->getSession()->getFlashBag()->add('driverError', 'This is the first step in creating mission process!');
    
                return $this->redirectToRoute('stepOne');
            }
            $manager->persist($data['mission']);
            $manager->flush();
            $session->clear();
            $request->getSession()->getFlashBag()->add('missionSuccess', 'Your mission has been created successfully!');
    
            return $this->redirectToRoute('allMissions');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/mission/{id}/edit", name="editMission")
     */
    public function edit(SessionInterface $session, Mission $mission = null)
    {
        if($this->getUser()->getRole() != 3){

            if ($mission && $mission->getId()) {
                $session = $this->setDatasToSession($session, $mission);
    
                return $this->redirectToRoute('stepOne');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/mission/new", name="addMission")
     */
    public function addMission(Request $request)
    {
        if($this->getUser()->getRole() != 3){

            $request->getSession()->getFlashBag()->add('projectError', 'Please specify the project to link the new mission!');
    
            return $this->redirectToRoute('allProjects');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function salaryPerDay(Mission $mission)
    {
        $salary = 0;
        if ($mission) {
            $salary = $mission->getSalaire() / $this->periodFromToNumber($mission->getPeriodOfWork());
        }

        return $salary;
    }

    public function periodFromToNumber($period){
        switch ($period) {
            case '1':
                return 1;
            case '2':
                return 7;
            case '3':
                return 30;
        }
    }
}
