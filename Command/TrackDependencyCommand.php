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

final class TrackDependencyCommand extends Command
{
    public const NAME = 'app-insights:track:dependency';

    protected static $defaultName = self::NAME;

    private $client;

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function configure()
    {
        $this
            ->setDescription('[<info>App Insights</info>] Track Dependency.')
            ->addArgument('name', InputArgument::REQUIRED, 'Dependency name')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Dependency type', '')
            ->addOption('commandName', null, InputOption::VALUE_OPTIONAL, 'Dependency command name', '')
            ->addOption('startTime', null, InputOption::VALUE_OPTIONAL, 'Start time (timestamp) when call to dependency was initialized', time())
            ->addOption('durationTime', null, InputOption::VALUE_OPTIONAL, 'Dependency call duration time in milliseconds', 0)
            ->addOption('isSuccessful', null, InputOption::VALUE_OPTIONAL, 'Was the dependency call successful', true)
            ->addOption('resultCode', null, InputOption::VALUE_OPTIONAL, 'Dependency result code')
            ->addOption('properties', null, InputOption::VALUE_OPTIONAL, 'Dependency additional properties passed as json object')
            ->addOption('dont-flush', null, InputOption::VALUE_OPTIONAL, 'Don\'t flush client directly in the command, wait for the KernelTerminateListener', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->client->trackDependency(
            $input->getArgument('name'),
            $input->getOption('type'),
            $input->getOption('commandName'),
            (int) $input->getOption('startTime'),
            (int) $input->getOption('durationTime'),
            (bool) $input->getOption('isSuccessful'),
            $input->getOption('resultCode'),
            $input->getOption('properties') ? json_decode($input->getOption('properties'), true) : null
        );

        $dontFlush = false !== $input->getOption('dont-flush');

        if ($dontFlush) {
            $io->success('Telemetry sent.');

            return 0;
        }

        $response = $this->client->flush();

        if (200 === $response->getStatusCode()) {
            $io->success('Telemetry successfully sent.');
            $io->note((string) $response->getBody());
        } else {
            $io->success('Something went wrong.');
            $io->note('Status Code: '.$response->getStatusCode());
            $io->note((string) $response->getBody());
        }

        return 0;
    }
}
