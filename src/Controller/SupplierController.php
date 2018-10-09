<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SupplierController extends AbstractController
{
    /**
     * @Route("/supplier", name="allSuppliers")
     */
    public function show()
    {
        return $this->render('supplier/supplierBase.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }
}
