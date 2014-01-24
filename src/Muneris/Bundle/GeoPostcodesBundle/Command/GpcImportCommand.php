<?php

namespace Muneris\Bundle\GeoPostcodesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class GpcImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('muneris:gpc:import')
            ->setDescription('Import GeoPostcode data from csv file.')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the data file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        if (!is_file($file) || ('.csv' !== substr($file, -4))) {
            $output->writeln('<error>Data file not found.</error>');
            return;
        }

        $fp = fopen($file, 'rb');
        if (!$fp) {
            $output->writeln('<error>Data file not found.</error>');
            return;
        }

        $total_size = filesize($file);

        if ('prod' !== $this->getContainer()->get('kernel')->getEnvironment()) {
            $output->writeln('<error>Please use --env=prod to run this. In dev or test you will run out of memory ...</error>');
            return;
        }

        $conn = $this->getContainer()->get('database_connection');

        $output->writeln('Truncating GeoPostcode to make room for the new data set.');
        $conn->exec("TRUNCATE TABLE GeoPostcode");
        $output->writeln('<info>- Done</info>');

        $progress = $this->getHelperSet()->get('progress');
        $progress->setRedrawFrequency(1000);
        $progress->setBarWidth(100);

        $output->writeln('Starting import, this might take a while..');

        $headers = ['iso', 'country', 'language', 'id', 'region1', 'region2', 'region3', 'region4', 'locality', 'postcode', 'suburb', 'street', 'range', 'latitude', 'longitude', 'elevation', 'iso2', 'fips', 'nuts', 'hasc', 'stat', 'timezone', 'utc', 'dst'];

        $start = time();
        $i = -1;
        $sequence = 0;
        $country = '';
        while (($data = fgetcsv($fp, 2048, ';')) !== false) {
            if (24 !== count($data)) {
                $output->writeln('<error>Wrong column count for data file.</error>');
                return;
            }
            $i++;

            // skip header line
            if ($i == 0) {
                $progress->start($output, $total_size);
                continue;
            }

            // remap using headers, easier to read..
            $mapped = [];
            foreach ($data as $k => $v) {
                $mapped[$headers[$k]] = trim($v);
            }

            if ($mapped['iso'] != $country) {
                $country = $mapped['iso'];
                $sequence = 0;
            }

            $conn->insert('GeoPostcode', [
                'country'     => $mapped['iso'],
                'language'    => $mapped['language'],
                'sequence'    => $sequence,
                'region_code' => $mapped['id'],
                'region_1'    => $mapped['region1'],
                'region_2'    => $mapped['region2'],
                'region_3'    => $mapped['region3'],
                'region_4'    => $mapped['region4'],
                'zip_code'    => $mapped['postcode'],
                'city'        => $mapped['locality'],
                'area_1'      => $mapped['suburb'],
                'area_2'      => $mapped['street'],
                'lat'         => $mapped['latitude'],
                'lng'         => $mapped['longitude'],
                'tz'          => $mapped['timezone'],
                'utc'         => $mapped['utc'],
                'dst'         => $mapped['dst'],
                'created_at'  => 'now()',
                'updated_at'  => 'now()',
            ]);

            $sequence++;
            $progress->advance(strlen(implode('', $data))+71);
        }
        $progress->finish();
        $output->writeln('<info>Done! Imported '.$i.' records in '.(time() - $start).' seconds.</info>');
    }
}
