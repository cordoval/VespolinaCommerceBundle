<?php

/**
 * (c) 2011 - ∞ Vespolina Project http://www.vespolina-project.org
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Vespolina\CommerceBundle\ProcessScenario\Checkout;

use Vespolina\CommerceBundle\Process\AbstractProcess;
use Vespolina\CommerceBundle\Process\ProcessStepInterface;
use Vespolina\CommerceBundle\Process\ProcessDefinition;

/**
 * This process models a commonly used checkout process which consists of following steps:
 *
 * 1) identifying / register the customer
 * 2) pay (redirect to e.g. paypal and are sent back)
 * 3) send a confirmation of the order by mail
 *
 * @author Luis Cordova <cordoval@gmail.com>
 * @author Maksim Kotlar <kotlyar.maksim@gmail.com>
 */
class CheckoutShortProcess extends AbstractProcess
{
    public function build() {

        $definition = new ProcessDefinition();
        $definition->addProcessStep('identify_customer',
                                    'Vespolina\CommerceBundle\ProcessScenario\Checkout\Step\IdentifyCustomer');
        $definition->addProcessStep('execute_payment',
                                    'Vespolina\CommerceBundle\ProcessScenario\Checkout\Step\ExecutePayment');
        $definition->addProcessStep('complete_checkout',
                                    'Vespolina\CommerceBundle\ProcessScenario\Checkout\Step\CompleteCheckout');

        return $definition;
    }

    public function completeProcessStep(ProcessStepInterface $processStep)
    {
        $nextStepConfig = $this->definition->getNextStepConfig($processStep->getName());

        // Detect if this process step is followed by another process step
        if (null != $nextStepConfig) {
            $this->setState($nextStepConfig['name']);
        } else {
            $this->setState('completed');
        }
    }

    public function getCurrentProcessStep()
    {
        // This is a simple case in which a state maps to a process step name, but it could be more dynamic
        if (!$this->isCompleted()) {
            return $this->getProcessStepByName($this->getState());
        }
    }

    public function getInitialState()
    {
        return 'identify_customer';
    }

    public function getName()
    {
        return 'checkout_short';
    }
}
