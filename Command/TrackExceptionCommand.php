<?php

declare(strict_types=1);

/*
 * This file is part of the App Insights PHP project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Command;

use AppInsightsPHP\Client\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TrackExceptionCommand extends Command
{
    public const NAME = 'app-insights:track:exception';

    protected static $defaultName = self::NAME;

    private $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function configure() : void
    {
        $this
            ->setDescription('[<info>App Insights</info>] Track Exception.')
            ->addArgument('class', InputArgument::OPTIONAL, 'Exception class', '\\Exception')
            ->addArgument('message', InputArgument::OPTIONAL, 'Exception message', '')
            ->addOption('properties', null, InputOption::VALUE_OPTIONAL, 'Exception additional properties passed as json object')
            ->addOption('measurements', null, InputOption::VALUE_OPTIONAL, 'Exception additional measurements passed as json object')
            ->addOption('dont-flush', null, InputOption::VALUE_OPTIONAL, 'Don\'t flush client directly in the command, wait for the KernelTerminateListener', false);
    }

    protected function initialize(InputInterface $input, OutputInterface $output) : void
    {
        if (!\class_exists($input->getArgument('class'))) {
            throw new \InvalidArgumentException('Argument class must be a valid class');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io = new SymfonyStyle($input, $output);

        $class = $input->getArgument('class');

        $this->client->trackException(
            new $class($input->getArgument('message')),
            $input->getOption('properties') ? \json_decode($input->getOption('properties'), true) : null,
            $input->getOption('measurements') ? \json_decode($input->getOption('measurements'), true) : null
        );

        $dontFlush = false !== $input->getOption('dont-flush');

        if ($dontFlush) {
            $io->success('Telemetry sent.');

            return 0;
        }

        $response = $this->client->flush();

        if (null === $response) {
            $io->warning('Telemetry was not sent.');
            $io->note('Configuration is disabled or there was an error. Please check fallback logs.');

            return 0;
        }

        if (200 === $response->getStatusCode()) {
            $io->success('Telemetry successfully sent.');
            $io->note((string) $response->getBody());
        } else {
            $io->success('Something went wrong.');
            $io->note('Status Code: ' . $response->getStatusCode());
            $io->note((string) $response->getBody());
        }

        return 0;
    }
}
