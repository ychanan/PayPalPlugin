<?php

declare(strict_types=1);

namespace Sylius\PayPalPlugin\Listener;

use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\PayPalPlugin\Exception\PayPalPaymentMethodNotFoundException;
use Sylius\PayPalPlugin\Onboarding\Initiator\OnboardingInitiatorInterface;
use Sylius\PayPalPlugin\Provider\PayPalPaymentMethodProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class PayPalPaymentMethodListener
{
    /** @var OnboardingInitiatorInterface */
    private $onboardingInitiator;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var PayPalPaymentMethodProviderInterface */
    private $payPalPaymentMethodProvider;

    public function __construct(
        OnboardingInitiatorInterface $onboardingInitiator,
        UrlGeneratorInterface $urlGenerator,
        FlashBagInterface $flashBag,
        PayPalPaymentMethodProviderInterface $payPalPaymentMethodProvider
    ) {
        $this->onboardingInitiator = $onboardingInitiator;
        $this->urlGenerator = $urlGenerator;
        $this->flashBag = $flashBag;
        $this->payPalPaymentMethodProvider = $payPalPaymentMethodProvider;
    }

    public function initializeCreate(ResourceControllerEvent $event): void
    {
        $paymentMethod = $event->getSubject();
        /** @var PaymentMethodInterface $paymentMethod */
        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);

        if (!$this->isNewPaymentMethodPayPal($paymentMethod)) {
            return;
        }

        if (!$this->onboardingInitiator->supports($paymentMethod)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->onboardingInitiator->initiate($paymentMethod)));
    }

    private function isNewPaymentMethodPayPal(PaymentMethodInterface $paymentMethod): bool
    {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();

        return $gatewayConfig->getFactoryName() === 'sylius.pay_pal';
    }

    private function isTherePayPalPaymentMethod(): bool
    {
        try {
            $this->payPalPaymentMethodProvider->provide();
        } catch (PayPalPaymentMethodNotFoundException $exception) {
            return false;
        }

        return true;
    }
}
