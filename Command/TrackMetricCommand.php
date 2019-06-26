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
use ApplicationInsights\Channel\Contracts\Data_Point_Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TrackMetricCommand extends Command
{
    public const NAME = 'app-insights:track:metric';

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
            ->setDescription('[<info>App Insights</info>] Track Metric.')
            ->addArgument('name', InputArgument::REQUIRED, 'Metric name')
            ->addArgument('value', InputArgument::REQUIRED, 'Metric value')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Metric type, 0 = Measurement, 1 = Aggregation', Data_Point_Type::Measurement)
            ->addOption('count', null, InputOption::VALUE_OPTIONAL, 'Metric count')
            ->addOption('min', null, InputOption::VALUE_OPTIONAL, 'Metric max')
            ->addOption('max', null, InputOption::VALUE_OPTIONAL, 'Metric max')
            ->addOption('standardDeviation', null, InputOption::VALUE_OPTIONAL, 'Standard deviation')
            ->addOption('measurements', null, InputOption::VALUE_OPTIONAL, 'Metric additional measurements passed as json object')
            ->addOption('dont-flush', null, InputOption::VALUE_OPTIONAL, 'Flush client directly in the command', false);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!is_numeric($input->getArgument('value'))) {
            throw new \InvalidArgumentException('Argument value must be a valid number');
        }

        if ($input->getOption('type') && !\in_array($input->getOption('type'), [Data_Point_Type::Measurement, Data_Point_Type::Aggregation])) {
            throw new \InvalidArgumentException('Invalid measurement type');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->client->trackMetric(
            $input->getArgument('name'),
            $input->getArgument('value'),
            $input->getOption('type'),
            $input->getOption('count') ? (int) $input->getOption('count') : null,
            $input->getOption('min') ? (int) $input->getOption('min') : null,
            $input->getOption('max') ? (int) $input->getOption('max') : null,
            $input->getOption('standardDeviation') ? (float) $input->getOption('standardDeviation') : null,
            $input->getOption('measurements') ? json_decode($input->getOption('measurements'), true) : null
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
