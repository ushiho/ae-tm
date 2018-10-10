<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Form\DriverType;
use App\Repository\DriverRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DriverController extends AbstractController
{
    /**
     * @Route("/driver", name="allDrivers")
     */
    public function show(DriverRepository $repo)
    {
        return $this->render('driver/driverBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'drivers' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/driver/new", name="addDriver")
     * @Route("driver/edit/{id}", name="editDriver")
     */
    public function action(Request $request, ObjectManager $manager, Driver $driver=null)
    {
        if($driver==null){
            $driver = new Driver();
        }
        $form = $this->createForm(DriverType::class, $driver);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($driver);
            $manager->flush();
            return $this->redirectToRoute('allDrivers');
        }
        return $this->render('driver/driverForm.html.twig', [
            'form' => $form->createView(),
            'connectedUser' => $this->getUSer(),
            'driver' => $driver,
        ]);
    }

    /**
     * @Route("/driver/delete/{id}", name="deleteDriver")
     */
    public function delete($id, ObjectManager $manager, DriverRepository $repo){
        $manager->remove($repo->find($id));
        $manager->flush();
        return $this->redirectToRoute('allDrivers');
    }
}
