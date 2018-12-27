<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GasStationRepository;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\GasStation;
use App\Form\GasStationType;
use Symfony\Component\HttpFoundation\Request;

class GasStationController extends AbstractController
{
    /**
     * @Route("/gaz/station/all", name="allGasStation")
     * @Method("GET")
     */
    public function indexAction(GasStationRepository $repo)
    {
        if (!$this->testRole()) {
            $this->toProfil();
        }

        return $this->render('gas_station/gasStationBase.html.twig', array(
            'gasStations' => $repo->findAll(),
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("/gaz/station/new", name="addGasStation")
     * @Route("/gaz/station/edit/{id}", name="editGasStation",  requirements={"id" = "\d+"})
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, ObjectManager $manager, GasStation $gasStation = null)
    {
        if (!$gasStation) {
            $gasStation = new Gasstation();
        }
        $form = $this->createForm(GasStationType::class, $gasStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($gasStation);
            $manager->flush();
            $this->customizeMsg($request, $gasStation, $this->guessActionStation($gasStation));

            return $this->redirectToRoute('gasStationShow', array('id' => $gasStation->getId()));
        }

        return $this->render('gas_station/new.html.twig', array(
            'gasStation' => $gasStation,
            'form' => $form->createView(),
            'connectedUser' => $this->getUser(),
        ));
    }

    public function guessActionStation(GasStation $gasStation)
    {
        return $gasStation->getId() ? 2 : 1;
    }

    public function customizeMsg(Request $request, GasStation $gasStation, $actionType)
    {
        switch ($actionType) {
            case '1':
                $msg = 'Your gaz station is created successfully.';
                break;
            case '2':
                $msg = 'Your gaz station is updated successfully.';
                break;
            case '3':
                $msg = 'Your gaz station is deleted successfully.';
                break;
            default:
                $msg = 'Nothing to do.';
                break;
        }
        $request->getSession()->getFlashBag()->add('gasStationMsg', $msg);
    }

    /**
     * @Route("gas/station/show/{id}", name="showGasStation",  requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function showAction(GasStation $gasStation = null)
    {
        return $this->render('gas_station/show.html.twig', array(
            'gasStation' => $gasStation,
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("gaz/station/delete/{id}", name="deleteGasStation", requirements={"id"= "\d+"})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, GasStation $gasStation = null, ObjectManager $manager)
    {
        if ($gasStation) {
            $manager->remove($gasStation);
            $manager->flush();
            $this->customizeMsg($request, $gasStation, 3);
        }

        return $this->redirectToRoute('allGasStation');
    }

    public function testRole()
    {
        return $this->getUser()->getRole() == 2 ? false : true;
    }

    public function toProfil()
    {
        $requst->getSession()->getFlashBag()->add('profilMsg', "You don't have access.");

        return $this->redirectToRoute('profil');
    }
}
