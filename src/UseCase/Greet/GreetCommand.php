<?php

namespace App\UseCase\Greet;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class GreetCommand extends Command
{
    private $handler;

    public function __construct(GreetHandler $handler)
    {
        parent::__construct();

        $this->handler = $handler;
    }

    protected function configure(): void
    {
        $this
            ->setName('handler:greet')
            ->setDescription('Greet someone')
            ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        ($this->handler)(
            $input->getArgument('name'),
            new ConsoleLogger($output)
        );
    }
}
