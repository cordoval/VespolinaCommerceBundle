<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\CommerceBundle\Controller\Process;

use Symfony\Component\HttpFoundation\Request;

class ExecutePaymentController extends AbstractProcessStepController
{
    public function executeAction()
    {
        $processManager = $this->container->get('vespolina.process_manager');
        $request = $this->container->get('request');
        $process = $this->getProcessStep()->getProcess();
        $cart = $process->getContext()->get('cart');

        $this
            ->container->get('payum')
            ->getStorageForClass(
                'Infrastructure\Payum\Document\PaypalPaymentDetails',
                'paypal_express_checkout'
            )
        ;
        // Signal enclosing process step that we are done here
        $process->completeProcessStep($this->processStep);
        $processManager->updateProcess($process);
        $this->container->get('session')->getFlashBag()->add('success', 'The transaction was successful.');

        return $process->execute();
    }
}
