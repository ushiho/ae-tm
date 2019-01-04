<?php

namespace App\Controller;

use DateTime;
use App\Entity\Mission;
use App\Entity\Project;
use App\Controller\PdfController;
use App\Form\ProjectType;
use App\Form\ExportProjectsType;
use App\Repository\MissionRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;


class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="allProjects")
     */
    public function show(ProjectRepository $repo, Request $request)
    {
        if($this->getUser()->getRole() != 3){
            $searchForm = $this->createForm(ExportProjectsType::class);
            $searchForm->handleRequest($request);
            if($searchForm->isSubmitted() && $searchForm->isValid()){

                // return $this->renderView('exportedFile/project.html.twig', [
                //     'projects' => $repo->findByDates($searchForm->getData()),
                // ]);
                return $this->print($repo->findByDates($searchForm->getData()));
            }

            return $this->render('project/projectBase.html.twig', [
               'connectedUser' => $this->getUser(),
               'projects' => $repo->findAll(),
               'searchForm' => $searchForm->createView(),
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
        
    }

    /**
     * @Route("/project/new", name="addProject")
     * @Route("project/edit/{id}", name="editProject", requirements={"id"="\d+"})
     */
    public function action(Request $request, ObjectManager $manager, Project $project=null){
        if($this->getUser()->getRole() != 3){

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
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/project/delete/{id}", name="deleteProject", requirements={"id"="\d+"})
     */
    public function delete(Project $project, ObjectManager $manager, ProjectRepository $repo,
     MissionRepository $missionRepo){
         if($this->getUser()->getRole() != 3){

             if($project) {
                 foreach ($missionRepo->findByProject($project) as $mission) {
                     $manager->remove($mission);
                     $manager->flush();
                 }
                 $manager->remove($project);
                 $manager->flush();
             }
             return $this->redirectToRoute('allProjects');
         }else{
            return $this->redirectToRoute('error403');
         }
    }


    /**
     * @Route("project/show/{id}", name="showProject", requirements={"id"="\d+"})
     */
    public function showDetails(Project $project=null){
        if($this->getUser()->getRole() != 3){

            if($project){
                return $this->render('project/show.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'project' => $project,
                ]);
            }
            return $this->redirectToRoute('allProjects');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("project/deleteAll", name="deleteAllProjects")
     */
    public function deleteAll(ProjectRepository $repo, ObjectManager $manager, MissionRepository $missionRepo){
        if($this->getUser()->getRole() != 3){

            foreach($repo->findAll() as $project){
                foreach ($missionRepo->findByProject($project) as $mission) {
                    $manager->remove($mission);
                    $manager->flush();
                }
                $manager->remove($project);
                $manager->flush();
            }
            return $this->redirectToRoute('allProjects');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/project/{id}/mission/add", name="addMissionToProject", requirements={"id"="\d+"})
     */
    public function createMission(SessionInterface $session, Project $project=null){
        if($this->getUser()->getRole() != 3){

            if($project){
                $session->set('project', $project);
                return $this->redirectToRoute('stepOne');
            }
            return $this->redirectToRoute('allProjects');
        }else{
            return $this->redirectToRoute('error403');
        }
    }


    /**
     * @Route("/project/{id}/print", name="printMissionOfProject")
     */
    public function print(Project $project=null)
    {
        if($this->getUser()->getRole()!=3 && $project){
            $fileName = (new \DateTime())->format('Hidmy');
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            
            // Instantiate Dompdf with our options
            $dompdf = new Dompdf($pdfOptions);
            
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('exportedFile/project.html.twig', [
                'project' => $project,
            ]);
            
            // Load HTML to Dompdf
            $dompdf->loadHtml($html);
            
            // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
            $dompdf->setPaper('A4', 'landscape');
    
            // Render the HTML as PDF
            $dompdf->render();
    
            // Output the generated PDF to Browser (force download)
            $dompdf->stream($fileName.".pdf", [
                "Attachment" => false,
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }
}
