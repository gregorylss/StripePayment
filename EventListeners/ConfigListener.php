<?php

namespace StripePayment\EventListeners;

use StripePayment\StripePayment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Thelia\Model\ModuleConfigQuery;

class ConfigListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'module.config' => ['onModuleConfig', 128]
        ];
    }

    public function onModuleConfig(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject !== "HealthStatus") {
            throw new \RuntimeException('Event subject does not match expected value');
        }

        $configModule = ModuleConfigQuery::create()
            ->filterByModuleId(StripePayment::getModuleId())
            ->filterByName(['secret_key', 'publishable_key', 'webhooks_key','secure_url'])
            ->find();

        $moduleConfig = [];
        $moduleConfig['module'] = StripePayment::getModuleCode();
        $configsCompleted = true;

        if ($configModule->count() === 0) {
            $configsCompleted = false;
        }

        foreach ($configModule as $config) {
            $moduleConfig[$config->getName()] = $config->getValue();
            if ($config->getValue() === null) {
                $configsCompleted = false;
            }
        }

        $moduleConfig['completed'] = $configsCompleted;

        $event->setArgument('stripe_payment.module.config', $moduleConfig);

    }

}