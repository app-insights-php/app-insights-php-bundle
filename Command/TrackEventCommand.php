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

final class TrackEventCommand extends Command
{
    public const NAME = 'app-insights:track:event';

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
            ->setDescription('[<info>App Insights</info>] Track Event.')
            ->addArgument('name', InputArgument::REQUIRED, 'Event name')
            ->addOption('properties', null, InputOption::VALUE_OPTIONAL, 'Event additional properties passed as json object')
            ->addOption('measurements', null, InputOption::VALUE_OPTIONAL, 'Event additional measurements passed as json object')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->client->trackEvent(
            $input->getArgument('name'),
            $input->getOption('properties') ? json_decode($input->getOption('properties'), true) : null,
            $input->getOption('measurements') ? json_decode($input->getOption('measurements'), true) : null
        );

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
