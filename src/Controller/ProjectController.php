<?php

namespace App\Controller;

use DateTime;
use App\Entity\Mission;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
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
     * @Route("project/edit/{id}", name="editProject")
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
            return $this->redirectToRoute('allProjects');
        }
        return $this->render('project/projectForm.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
            'connectedUser' => $this->getUser(),

        ]);
    }

    /**
     * @Route("/project/delete/{id}", name="deleteProject")
     */
    public function delete($id, ObjectManager $manager, ProjectRepository $repo){
        $manager->remove($repo->find($id));
        $manager->flush();
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
     * @Route("project/show/{id}", name="showProject")
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
}
