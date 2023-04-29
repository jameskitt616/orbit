<?php

declare(strict_types=1);

namespace App\Kernel\Infrastructure;

use App\Kernel\Application\Command;
use App\Kernel\Application\CommandBus;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class SymfonyMessengerCommandBus implements CommandBus
{
    public function __construct(
        private MessageBusInterface $bus
    )
    {
    }

    public function handle(Command $command): void
    {
        $this->bus->dispatch($command);
    }
}
