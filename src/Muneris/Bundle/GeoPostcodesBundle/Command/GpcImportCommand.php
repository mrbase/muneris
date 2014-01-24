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

        $progress = $this->getHelperSet()->get('progress');
        $progress->setRedrawFrequency(1000);
        $progress->setBarWidth(100);

        $output->writeln('<info>Starting import, this might take a while..</info>');

        $start = time();
        $i = -1;
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

            iso;country;language;id;region1;region2;region3;region4;locality;postcode;suburb;street;range;latitude;longitude;elevation;iso2;fips;nuts;hasc;stat;timezone;utc;dst


            $conn->insert('GeoPostcode', [
                'country'     => $data[1],
                'language'    => $data[2],
                'sequence'    => $data[3],
                'region_code' => $data[4],
                'region_1'    => $data[5],
                'region_2'    => $data[6],
                'region_3'    => $data[7],
                'region_4'    => $data[8],
                'zip_code'    => $data[9],
                'city'        => $data[10],
                'area_1'      => $data[11],
                'area_2'      => $data[12],
                'lat'         => $data[13],
                'lng'         => $data[14],
                'tz'          => $data[15],
                'utc'         => $data[16],
                'dst'         => $data[17],
                'created_at'  => 'now()',
                'updated_at'  => 'now()',
            ]);

            $progress->advance(strlen(implode('', $data))+45);
        }
        $progress->finish();
        $output->writeln('<info>Done! Imported '.$i.' records in '.(time() - $start).' seconds.</info>');
    }
}
