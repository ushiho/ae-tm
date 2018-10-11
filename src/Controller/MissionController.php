<?php

namespace App\Controller;

use DateTime;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Form\MissionType;
use App\Repository\DriverRepository;
use App\Repository\MissionRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MissionController extends AbstractController
{
    /**
     * @Route("/mission", name="allMissions")
     */
    public function index(MissionRepository $repo)
    {
        return $this->render('mission/missionBase.html.twig', [
            'missions' => $repo->findAll(),
            'connectedUser' => $this->getUser(),
        ]);
    }

     /**
     * @Route("/mission/new", name="addMission")
     * @Route("mission/edit/{id}", name="editMission")
     */
    public function create(Request $request, ObjectManager $manager, Mission $mission=null, MissionRepository $repo){
        if($mission==null){
            $mission = new Mission();
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
}
