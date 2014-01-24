<?php
/**
 * Created by JetBrains PhpStorm.
 * User: un
 * Date: 27/06/13
 * Time: 20.27
 * To change this template use File | Settings | File Templates.
 */

namespace Muneris\Bundle\GeoPostcodesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class ImportCommand extends ContainerAwareCommand
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
            if (17 !== count($data)) {
                $output->writeln('<error>Wrong column count for data file.</error>');
                return;
            }
            $i++;

            // skip header line
            if ($i == 0) {
                $progress->start($output, $total_size);
                continue;
            }

            $conn->insert('GeoPostcode', [
                'country'     => $data[0],
                'language'    => $data[1],
                'sequence'    => $data[2],
                'region_code' => $data[3],
                'region_1'    => $data[4],
                'region_2'    => $data[5],
                'region_3'    => $data[6],
                'region_4'    => $data[7],
                'zip_code'    => $data[8],
                'city'        => $data[9],
                'area_1'      => $data[10],
                'area_2'      => $data[11],
                'lat'         => $data[12],
                'lng'         => $data[13],
                'tz'          => $data[14],
                'utc'         => $data[15],
                'dst'         => $data[16],
                'created_at'  => 'now()',
                'updated_at'  => 'now()',
            ]);

            $progress->advance(strlen(implode('', $data))+45);
        }
        $progress->finish();
        $output->writeln('<info>Done! Imported '.$i.' records in '.(time() - $start).' seconds.</info>');
    }
}
