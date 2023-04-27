<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Console\Command;

use Nanobots\DbDumper\Export\ExportModelInterface;
use Nanobots\DbDumper\Helper\Output;
use Nanobots\DbDumper\Export\DumpInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DumpDatabase extends Command
{
    /** @var string  */
    const ALL = 'all';

    /** @var string  */
    const EMPTY = 'empty';

    /** @var string  */
    const QUIT = 'quit';

    /** @var string  */
    const EXPORT_MODE = 'mode';

    /** @var \Nanobots\DbDumper\Helper\Output  */
    protected Output $outputHelper;

    /** @var \Nanobots\DbDumper\Export\DumpInterface  */
    protected DumpInterface $dump;

    /**
     * @param \Nanobots\DbDumper\Helper\Output $outputHelper
     * @param \Nanobots\DbDumper\Export\DumpInterface $dump
     */
    public function __construct(
        Output $outputHelper,
        DumpInterface $dump
    ) {
        $this->outputHelper = $outputHelper;
        $this->dump = $dump;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $options = [
            new InputOption(
                self::EXPORT_MODE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Export Mode'
            )
        ];

        $this->setName('nanobots:dump')
            ->setDescription('Create database dump')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exportModelOption = $input->getOption(self::EXPORT_MODE);
        $output->writeln((string)__('<info>Export mode has not been defined, default mode will be used</info>'));

        $exportModel = $this->dump->getExportModel($exportModelOption ?? 'default');

        if (!$exportModel) {
            $this->showAvailableExportModes($output);
            throw new \Magento\Framework\Exception\LocalizedException(__(
                'Undefined export model: %1. Use one from the list',
                $exportModelOption)
            );
        }

        $tablesWithoutGroups = $this->dump->getTablesWithoutGroups();
        if (!empty($tablesWithoutGroups)) {

            switch ($exportModel->exportTypeForAllTables()) {
                case ExportModelInterface::EXPORT_MODE_DUMP_ALL_DATA: {
                    $this->dump->addTablesToTableGroup('core', $tablesWithoutGroups);
                    break;
                }
                case ExportModelInterface::EXPORT_MODE_ASK_FOR_UNGROUPED_TABLES: {
                    $questionHelper = $this->getHelper('question');
                    $this->outputHelper->printError($output, (string)__('Following tables dump instructions are not defined:'));
                    $this->outputHelper->printTableList($output, $tablesWithoutGroups);
                    $this->outputHelper->printWarning($output, (string)__('Please select one of following options'));
                    $this->outputHelper->printComment($output, (string)__('all - dumps all data from those tables'));
                    $this->outputHelper->printComment($output, (string)__('empty - only table structure will be created'));
                    $this->outputHelper->printComment($output, (string)__('quit - this will stop the dump operation'));

                    $question = new ChoiceQuestion(
                        (string)__('What is your option for these tables?'),
                        [self::ALL, self::EMPTY, self::QUIT],
                        self::EMPTY
                    );

                    $answer = $questionHelper->ask($input, $output, $question);

                    switch ($answer) {
                        case self::ALL: {
                            $this->dump->addTablesToTableGroup('core', $tablesWithoutGroups);
                            break;
                        }
                        case self::EMPTY: {
                            $this->dump->addTablesToTableGroup('empty', $tablesWithoutGroups);
                            break;
                        }
                        case self::QUIT: {
                            $this->outputHelper->printComment($output, (string)__('Good bye...'));
                            return 0;
                        }
                    }
                    break;
                }
                case ExportModelInterface::EXPORT_MODE_SKIP_DATA_FROM_UNGROUPED_TABLES:
                default: {
                    $this->dump->addTablesToTableGroup('empty', $tablesWithoutGroups);
                    break;
                }
            }
        }

        $this->dump->initializeDatabaseDump($exportModel, $output);
        $output->writeln('<info>' . 'Database dump prepared.' . '</info>');
        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function showAvailableExportModes(OutputInterface $output): void
    {
        $outputTable = new Table($output);
        $outputTable->setHeaders([
            __('Key'),
            __('Description'),
            __('Apply Filters'),
            __('Db Dump for Tables without Groups')
        ]);

        foreach ($this->dump->getExportModels() as $code => $exportModel) {
            $outputTable->addRow(
                [
                    $code,
                    $exportModel->getExportModeDescription(),
                    $exportModel->applyFilters() ? 'Y' : 'N',
                    $exportModel->exportTypeForAllTables(),
                ]
            );
        }

        $outputTable->render();
    }
}
