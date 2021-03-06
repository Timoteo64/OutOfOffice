<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer;

use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Mailer implements MailerInterface
{
    private $transport;
    private $bus;
    private $dispatcher;

    public function __construct(TransportInterface $transport, MessageBusInterface $bus = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->transport = $transport;
        $this->bus = $bus;
        $this->dispatcher = LegacyEventDispatcherProxy::decorate($dispatcher);
    }

    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        if (null === $this->bus) {
            $this->transport->send($message, $envelope);

            return;
        }

        if (null !== $this->dispatcher) {
            $message = clone $message;
            $envelope = null !== $envelope ? clone $envelope : Envelope::create($message);
            $event = new MessageEvent($message, $envelope, (string) $this->transport, true);
            $this->dispatcher->dispatch($event);
        }

        try {
            $this->bus->dispatch(new SendEmailMessage($message, $envelope));
        } catch (HandlerFailedException $e) {
            foreach ($e->getNestedExceptions() as $nested) {
                if ($nested instanceof TransportExceptionInterface) {
                    throw $nested;
                }
            }
            throw $e;
        }
    }
}
