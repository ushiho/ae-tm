<?php

namespace App\Controller;

use App\Entity\Department;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DepartmentController extends AbstractController
{
    /**
     * @Route("/department", name="allDepartments")
     */
    public function show(DepartmentRepository $repo)
    {
        return $this->render('department/departmentBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'departments' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/department/new", name="addDepartment")
     * @Route("/department/edit/{id}", name="editDepartment")
     */
    public function action(Department $department=null, Request $request, ObjectManager $manager)
    {
        if($department==null){
            $department = new Department();
        }
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($department);
            $manager->flush();
            return $this->redirectToRoute('allDepartments');
        }
        return $this->render('department/departmentForm.html.twig', [
            'form' => $form->createView(),
            'connectedUser' => $this->getUser(),
            'department' => $department,
        ]);
    }

    /**
     * @Route("/department/delete/{id}", name="deleteDepartment")
     */
    public function delete(Department $department, ObjectManager $manager){
        foreach ($department->getMissions() as $mission) {
        $manager->remove($mission);
        }
        $manager->remove($department);
        $manager->flush();
        return $this->redirectToRoute('allDepartments');
    }

    /**
     * @Route("/department/show/{id}", name="showDepartment")
     */
    public function showDetails(Department $department=null){
        if($department){
            return $this->render('department/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'department' => $department,
            ]);
        }
        return $this->redirectToRoute('allDepartments');
    }

    /**
     * @Route("/department/deleteAll", name="deleteAllDepartments")
     */
    public function deleteAll(ObjectManager $manager, DepartmentRepository $repo){
        foreach ($repo->findAll() as $department) {
            $manager->remove($department);
            $manager->flush();
        }
        return $this->redirectToRoute('allDepartments');
    }

    public function clone(Department $department){
        
    }
}
