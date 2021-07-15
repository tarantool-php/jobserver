<?php

declare(strict_types=1);

namespace App\Job\Greet;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GreetCommand extends Command
{
    private $handler;
    private $decorator;

    public function __construct(GreetHandler $handler, TextDecorator $decorator)
    {
        parent::__construct();

        $this->handler = $handler;
        $this->decorator = $decorator;
    }

    protected function configure() : void
    {
        $this
            ->setName('handler:greet')
            ->setDescription('Greet someone')
            ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        ($this->handler)(
            $input->getArgument('name'),
            $this->decorator,
            $input->getOption('yell')
        );
    }
}
