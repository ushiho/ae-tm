<?php

namespace App\Controller;

use DateTime;
use App\Entity\Mission;
use App\Form\MissionType;
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
    public function create(Request $request, ObjectManager $manager, Mission $mission=null){
        if($mission==null){
            $mission = new Mission();
        }
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $mission->setCreatedAt(new \DateTime());
            $manager->persist($mission);
            $manager->flush();
            return $this->redirectToRoute('allMissions');
        }
        return $this->render('mission/missionForm.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission,
            'connectedUser' => $this->getUser(),

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
}
