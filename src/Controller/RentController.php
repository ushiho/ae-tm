<?php

namespace App\Controller;

use DateTime;
use App\Form\RentType;
use App\Entity\Allocate;
use App\Repository\AllocateRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RentController extends AbstractController
{
    /**
     * @Route("/rent", name="allRents")
     */
    public function show(AllocateRepository $repo)
    {
        return $this->render('rent/rentBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'Rents' => $repo->findAll(),
        ]);
    }

    /**
     * @Route("/rent/new", name="addRent")
     * @Route("/rent/edit/{id}", name="editRent")
     */
    public function action(Request $request, ObjectManager $manager, Allocate $rent=null){
        if($rent==null){
            $rent = new Allocate();
        }
        $form = $this->createForm(RentType::class, $rent);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $rent->setCreatedAt(new \DateTime());
            $manager->persist($rent);
            $manager->flush();
            return $this->redirectToRoute('allRents');
        }
        return $this->render('rent/rentForm.html.twig', [
            'connectedUser' => $this->getUser(),
            'form' => $form->createView(),
            'rent' => $rent,
        ]);
    }
}
