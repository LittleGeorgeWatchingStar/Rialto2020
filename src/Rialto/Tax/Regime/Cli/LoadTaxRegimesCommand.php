<?php

namespace Rialto\Tax\Regime\Cli;

use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Database\Orm\DbManager;
use Rialto\Filesystem\FilesystemException;
use Rialto\Tax\Regime\TaxRegime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Load tax regimes from a .csv file.
 */
class LoadTaxRegimesCommand extends Command
{
    /**
     * @var InputInterface
     */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        parent::__construct();
        $this->dbm = $dbm;
    }

    protected function configure()
    {
        $this->setName('tax:load-regimes')
            ->setDescription('Load tax regimes from a csv file.')
            ->addArgument('ratesfile', InputArgument::REQUIRED, "the .csv file from which to load tax rates")
            ->addArgument('codesfile', InputArgument::REQUIRED, "the .csv file from which to load regime codes");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->dbm->beginTransaction();
        try {
            $this->createStateRates();
            $this->loadRates();
            $this->updateCodes();
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    private function createStateRates()
    {
        /* From http://www.boe.ca.gov/sutax/taxrateshist.htm */
        $dates = [
            '2013-01-01' => .075,
            '2011-07-01' => .0725,
            '2009-04-01' => .0825,
            '2004-07-01' => .0725,
        ];
        $endDate = null;
        foreach ($dates as $datestring => $taxRate) {
            $startDate = new \DateTime($datestring);
            $r = new TaxRegime();
            $r->setDescription('California base rate');
            $r->setStartDate($startDate);
            $r->setEndDate($endDate);
            $r->setTaxRate($taxRate);
            $this->dbm->persist($r);

            $endDate = clone $startDate;
            $endDate->modify('-1 day');
        }
    }

    private function loadRates()
    {
        $csv = $this->loadCsv('ratesfile');

        foreach ($csv as $line) {
            $regime = new TaxRegime();
            foreach ($line as $field => $value) {
                $value = $this->prepValue($field, $value);
                $setter = "set" . ucfirst($field);
                $regime->$setter($value);
            }
            $regime->setRegimeCode('');

            $this->dbm->persist($regime);
            $this->output->writeln("Added \"$regime\".");
        }

        $this->dbm->flush();
    }

    private function loadCsv($argname): CsvFileWithHeadings
    {
        $filename = $this->input->getArgument($argname);
        if (!is_readable($filename)) {
            throw new FilesystemException($filename, 'not readable');
        }

        $csv = new CsvFileWithHeadings();
        $csv->parseFile($filename);
        return $csv;
    }

    private function prepValue($field, $value)
    {
        $value = trim($value);
        switch ($field) {
            case "taxRate":
                return (double) $value / 100;
            case "startDate":
            case "endDate":
                if ($value === '') return null;
                return \DateTime::createFromFormat("m-d-y", $value);
            default:
                return $value;
        }
    }

    private function updateCodes()
    {
        $csv = $this->loadCsv('codesfile');

        foreach ($csv as $line) {
            $regimes = $this->findMatches($line);
            foreach ($regimes as $regime) {
                $regime->setRegimeCode($line['regimeCode']);
                $this->output->writeln("Code for $regime is " . $regime->getRegimeCode());
            }
        }
        $this->dbm->flush();
    }

    /** @return TaxRegime[] */
    private function findMatches(array $line)
    {
        $repo = $this->dbm->getRepository(TaxRegime::class);

        $county = $line['county'];
        $city = $line['city'];
        $date = null;
//        if (! empty($line['startDate']) ) {
//            $date = \DateTime::createFromFormat("n-j-y", $line['startDate']);
//            if (! $date ) throw new \Exception("Unable to parse {$line['startDate']}");
//        }

        return $repo->findMatches($county, $city, $date);
    }
}
