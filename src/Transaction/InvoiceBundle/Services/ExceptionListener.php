<?php

/**
 * @package     Invoice 
 * @author      Kailash
 */

namespace Transaction\InvoiceBundle\Services;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ExceptionListener {

    /**
     * Holds Symfony2 router
     *
     * @var Router
     */
    protected $router;

    /**
     * @param Router
     */
    public function __construct(Router $router) {
        $this->router = $router;
    }

/**
     *  This function called on kerenal request
     * @param GetResponseEvent $event
     * @return Response to front end
     */
    public function onKernelRequest(GetResponseEvent $event) {
        //get Request route name
        $request = $event->getRequest();
        $requestRoute = $request->get('_route');
        if ($requestRoute == "_configurator_final") {
           $url = $this->router->generate("transaction_invoice_invoice_list", array(), true);
            header("location:" . $url);
            exit;
        }
    }
    public function onKernelException(GetResponseForExceptionEvent $event) {
        // You get the exception object from the received event

        $exception = $event->getException();
        /* if exception is database crededential error then redirect to config  */
        if ($exception instanceof \PDOException || $exception->getPrevious() instanceof \PDOException || $exception instanceof \ConnectionException) {
            $url = $this->router->generate("_configurator_step", array("index" => 0), true);
            header("location:" . $url);
            exit;
        }

        $message = sprintf(
                'My Error says: %s with code: %s', $exception->getMessage(), $exception->getCode()
        );


        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Send the modified response object to the event
        $event->setResponse($response);
    }

}
