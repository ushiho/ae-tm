<?php

namespace App\Controller;

use DateTime;
use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\Project;
use App\Form\MissionType;
use App\Entity\Department;
use App\Repository\DriverRepository;
use App\Repository\MissionRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
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

     /**
     * @Route("/mission/new", name="addMission")
     * @Route("mission/edit/{id}", name="editMission")
     * @Route("mission/new/{idProject}", name="addMissionForProject")
     */
    public function create(Request $request, ObjectManager $manager, Mission $mission=null, 
    MissionRepository $repo, $idProject=null, ProjectRepository $projectRepo){
        if($mission==null){
            $mission = new Mission();
        }
        if($idProject){
            $mission->setProject($projectRepo->find($idProject));
        }
        $alert = "";
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $rest = $repo->findMissionByStateByDriver($mission->getDriver(), false);
            if(!empty($rest) && $request->attributes->get('_route')=="addMission"){
                $alert = "The driver selected has a mission non finished, please select another one.";
            } else{
                $mission->setCreatedAt(new \DateTime());
                $manager->persist($mission);
                $manager->flush();
                return $this->redirectToRoute('allMissions');
            }
        }
        return $this->render('mission/missionForm.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission,
            'connectedUser' => $this->getUser(),
            'alert' => $alert,
        ]);
    }

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

}
