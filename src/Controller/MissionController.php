<?php

namespace App\Controller;

use DateTime;
use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\Project;
use App\Entity\Allocate;
use App\Form\MissionType;
use App\Entity\Department;
use App\Controller\DriverController;
use App\Repository\DriverRepository;
use App\Repository\MissionRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
    public function index(MissionRepository $repo, Department $department=null, Request $request,
    Project $project=null, Driver $driver=null)
    {
        $missions = [];
        if($department && $request->attributes->get('_route')=="showMissionOfDepartment"){
            $missions = $repo->findByDepartment($department);
        }else if(!$project && $request->attributes->get('_route')=="showMissionOfProject"){
            $missions = $repo->findByProject($project);
        }else if($driver && $request->attributes->get('_route')=="showMissionsByDriver"){
            $missions = $repo->findByDriver($driver);
        }
        else{
            $missions = $repo->findAll();
        }
        return $this->render('mission/missionBase.html.twig', [
            'missions' => $missions,
            'connectedUser' => $this->getUser(),
        ]);
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
    public function delete($id, ObjectManager $manager, MissionRepository $repo){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute('allMissions');
    }

    /**
     * @Route("/mission/deleteAll", name="deleteAll")
     */
    public function deleteAll(MissionRepository $repo, ObjectManager $manager){
        foreach($repo->findAll() as $mission){
            $manager->remove($mission);
            $manager->flush();
        }
        return $this->redirectToRoute('allMissions');
    }

    /**
     * @Route("/mission/show/{id}", name="showMission")
     */
    public function showDetails(Mission $mission=null){
        if($mission){
            return $this->render('/mission/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'mission' => $mission,
            ]);
        }
        return $this->redirectToRoute('allMissions');
    }

    /**
     * @Route("/project/mission/new/add_mission", name="stepFour")
     */
    public function addMissionStepFour(Request $request, SessionInterface $session, ObjectManager $manager){
        if($session->get('rent')){
            $mission = $session->get('mission');
            if($mission){
                $mission = $manager->merge($mission);
            }else{
                $mission = new Mission();
            }
            $form = $this->createForm(MissionType::class, $mission);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                if($this->verifyDates($mission, $session->get('rent'))){
                    $session->set('mission', $mission);
                    return $this->redirectToRoute('verifyDatas');
                }else{
                    $request->getSession()->getFlashBag()->add('missionError', "Attention the  date of the mission should be included inside the date of the rent!");
                }
            }
            return $this->render('/mission/missionForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
            ]);
        }
    }

    private function verifyDates(Mission $mission, Allocate $rent){
        return ($mission->getStartDate()->diff($rent->getStartDate())->days >= 0 && $mission->getEndDate()->diff($rent->getEndDate())->days <= 0);
    }

    public function daysBetween(String $dt1, String $dt2) {
        return date_diff(
            date_create($dt2),  
            date_create($dt1)
        )->format('%a');
    }

    /**
     * @Route("/project/mission/new/verify", name="verifyDatas")
     */
    public function verifyDatas(Request $request, SessionInterface $session, ObjectManager $manager){
        if($session->count() > 0){
            $data = $this->getDatasFromSession($session);
            return $this->render('mission/verifyDatas.html.twig', [
                'connectedUser' => $this->getUser(),
                'mission' => $data['mission'],
                'rent' => $data['rent'],
                'vehicle' => $data['vehicle'],
                'driver' => $data['driver'],
            ]);
        }else{
            $request->getSession()->getFlashBag()->add('driverError', 'This is the first step in creating mission process!');
            return $this->redirectToRoute("stepOne");
        }
    }

    private function getDatasFromSession(SessionInterface $session){
            return array(
            'driver' => $session->get('driver'),
            'vehicle' => $session->get('vehicle'),
            'rent' => $session->get('rent'),
            'mission' => $session->get('mission'),
            'project' => $session->get('project'),
            );
    }

    private function setDatasToSession(SessionInterface $session, Mission $mission){
        $session->set('mission', $mission);
        $session->set('project', $mission->getProject());
        $session->set('driver', $mission->getDriver());
        $session->set('vehicle', $mission->getVehicle());
        $session->set('rent', $mission->getAllocate());
        dd($session);
        return $session;
    }

    private function completeData(SessionInterface $session){
        if($session->count() > 0){
            $data = $this->getDatasFromSession($session);
            $data['mission']->setDriver($data['driver'])
                            ->setAllocate($data['rent'])
                            ->setPayment(new Payment())
                            ->setProject($data['project'])
                            ->setCreatedAt(new \DateTime())
                            ->setPayment(new Payment());
            $data['driver']->getMissions()->add($data['mission']);
            $data['driver']->setSalairePerDay(DriverController::salaryPerDay($data['driver']));
            $data['vehicle']->setAllocate($data['rent']);
            $data['rent']->setCreatedAt(new \DateTime())
                         ->setMission($data['mission'])
                         ->setVehicle($data['vehicle']);
            $data['project']->setCreatedAt(new \DateTime())
                            ->getMission()->add($data['mission']);
            return $data;

        }else{
            return null;
        }
    }

    /**
     * @Route("/project/mission/new/validate", name="createMission")
     */
    public function save(SessionInterface $session, ObjectManager $manager, Request $request){
        $data = $this->completeData($session);
        if(!$data){
            $session->set('driverError', 'This is the first step in creating the mission process!');
            return $this->redirectToRoute('stepOne');
        }
        $data['driver']->setBusy(DriverController::isBusy($data['driver']));
        $manager->persist($data['driver']);
        $manager->persist($data['vehicle']);
        $manager->persist($data['rent']);
        $manager->persist($data['mission']);
        $manager->flush();
        $session->clear();
        $request->getSession()->getFlashBag()->add('missionSuccess', 'Your mission has been created successfully!');
        return $this->redirectToRoute('allMissions');
    }

    /**
     * @Route("/project/mission/{id}/edit", name="editMission")
     */
    public function edit(SessionInterface $session, Mission $mission=null){
        if($mission && !isEmpty($mission)){
            $session = $this->setDatasToSession($session, $mission);
            return $this->redirectToRoute('stepOne');
        }
    }

    /**
     * @Route("/mission/new", name="addMission")
     */
    public function addMission(Request $request){
        $request->getSession()->getFlashBag()->add('projectError', "Please specify the project to link the new mission!");
        return $this->redirectToRoute('allProjects');
    }
}
