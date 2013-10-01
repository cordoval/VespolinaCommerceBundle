<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\CommerceBundle\Controller\Process;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ExecutePaymentController extends AbstractProcessStepController
{
    public function executeAction()
    {
        $processManager = $this->container->get('vespolina.process_manager');
        $request = $this->container->get('request');
        $process = $this->getProcessStep()->getProcess();
        $cart = $process->getContext()->get('cart');

        $paymentName = 'paypal_express_checkout';

        $storage = $this
            ->container->get('payum')
            ->getStorageForClass(
                'DecoupledStore\Domain\Model\PaypalPaymentDetails',
                $paymentName
            )
        ;

        $details = $storage->createModel();
        $details->setPaymentrequestCurrencycode(0, 'USD');
        $details->setPaymentrequestAmt(0,  1.00);
        $storage->updateModel($details);

        $captureToken = $this
            ->container
            ->get('payum.security.token_factory')
            ->createCaptureToken(
                $paymentName,
                $details,
                'redirect_after_capture' // the route to redirect after capture;
            )
        ;

        $details->setInvnum($details->getId());
        $details->setReturnurl($captureToken->getTargetUrl());
        $details->setCancelurl($captureToken->getTargetUrl());
        $storage->updateModel($details);

        // Signal enclosing process step that we are done here
        $process->completeProcessStep($this->processStep);
        $processManager->updateProcess($process);

        return new RedirectResponse($captureToken->getTargetUrl());
    }
}
