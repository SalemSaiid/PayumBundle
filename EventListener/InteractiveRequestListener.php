<?php
namespace Payum\Bundle\PayumBundle\EventListener;

use Payum\Bundle\PayumBundle\Request\ResponseInteractiveRequest;
use Payum\Exception\LogicException;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\RedirectUrlInteractiveRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class InteractiveRequestListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (false == $event->getException() instanceof InteractiveRequestInterface) {
            return;
        }

        $interactiveRequest = $event->getException();
            
        if ($interactiveRequest instanceof ResponseInteractiveRequest) {
            $event->setResponse($interactiveRequest->getResponse());
            $event->stopPropagation();
            
            return;
        }
        
        if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
            $event->setResponse(new RedirectResponse($interactiveRequest->getUrl()));
            $event->stopPropagation();
            
            return;
        }
        
        $ro = new \ReflectionObject($interactiveRequest);
        
        $event->setException(new LogicException(
            sprintf('Cannot convert interactive request %s to symfony response.', $ro->getShortName()), 
            null, 
            $interactiveRequest
        ));
    }
}