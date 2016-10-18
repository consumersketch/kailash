<?php

/**
 * @package     Invoice 
 * @author      Kailash
 */

namespace Transaction\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceController extends Controller {

    /**
     * Obtains list of Invoice
     * @param type $request 
     * @return Response HTML/Ajax
     * @Route("/list")
     * @Template()
     */
    public function listAction(Request $request) {

        $result = $this->get('invoice.service')->invoiceList($request);
        $clients = $this->get('invoice.service')->getClients($request);

        /* For Ajax request  */
        if ($request->isXmlHttpRequest()) {

            $return = array(
                'code' => 200,
                'result' => [
                    'list' => $this->renderView('InvoiceBundle:Invoice:table.html.twig', array('result' => $result)),
                    'products' => $this->get('invoice.service')->getProductsByclient($request),
                ]
            );
            return new JsonResponse($return);
        }

        return array(
            'result' => $result,
            'clients' => $clients
        );
    }

}
