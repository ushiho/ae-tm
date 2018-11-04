<?php

namespace App\Controller;

use DateTime;
use App\Entity\Mission;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\MissionRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="allProjects")
     */
    public function show(ProjectRepository $repo)
    {
        
        return $this->render('project/projectBase.html.twig', [
           'connectedUser' => $this->getUser(),
           'projects' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/project/new", name="addProject")
     * @Route("project/edit/{id}", name="editProject", requirements={"id"="\d+"})
     */
    public function action(Request $request, ObjectManager $manager, Project $project=null){
        if($project==null){
            $project = new Project();
        }
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $project->setCreatedAt(new \DateTime());
            $manager->persist($project);
            $manager->flush();
            $request->getSession()->getFlashBag()->add('projectSuccess', "Your project is successfully added, link some mission to it!");
            return $this->redirectToRoute('allProjects');
        }
        return $this->render('project/projectForm.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
            'connectedUser' => $this->getUser(),

        ]);
    }

    /**
     * @Route("/project/delete/{id}", name="deleteProject", requirements={"id"="\d+"})
     */
    public function delete(Project $project, ObjectManager $manager, ProjectRepository $repo,
     MissionRepository $missionRepo){
        if($project) {
            foreach ($missionRepo->findByProject($project) as $mission) {
                $manager->remove($mission);
                $manager->flush();
            }
            $manager->remove($project);
            $manager->flush();
        }
        return $this->redirectToRoute('allProjects');
    }

    /**
     * @Route("project/calendar", name="projectCalendar")
     */
    public function calendar(){
        return $this->render('project/calendar.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }

    /**
     * @Route("project/show/{id}", name="showProject", requirements={"id"="\d+"})
     */
    public function showDetails(Project $project=null){
        if($project){
            return $this->render('project/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'project' => $project,
            ]);
        }
        return $this->redirectToRoute('allProjects');
    }

    /**
     * @Route("project/deleteAll", name="deleteAllProjects")
     */
    public function deleteAll(ProjectRepository $repo, ObjectManager $manager, MissionRepository $missionRepo){
        foreach($repo->findAll() as $project){
            foreach ($missionRepo->findByProject($project) as $mission) {
                $manager->remove($mission);
                $manager->flush();
            }
            $manager->remove($project);
            $manager->flush();
        }
        return $this->redirectToRoute('allProjects');
    }

    /**
     * @Route("/project/{id}/mission/add", name="addMissionToProject", requirements={"id"="\d+"})
     */
    public function createMission(SessionInterface $session, Project $project=null){
        if($project){
            $session->set('project', $project);
            return $this->redirectToRoute('stepOne');
        }
        return $this->redirectToRoute('allProjects');
    }

}
