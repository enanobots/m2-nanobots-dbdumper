<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Helper;

use Nanobots\DbDumper\Sql\Connection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class Output
{
    /** @var Connection  */
    protected Connection $connection;

    /**
     * @param \Nanobots\DbDumper\Sql\Connection $connection
     */
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array $tableList
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function printTableList(OutputInterface $output, array $tableList): void
    {
        $outputTable = new Table($output);
        $outputTable->setHeaders([
            __('Database Table'),
            __('Number of rows'),
            __('Table size (in MB)')
        ]);

        foreach ($tableList as $table) {
            $outputTable->addRow(
                [
                    $table,
                    $this->connection->getRowCount($table),
                    $this->connection->getTableSize($table)
                ]
            );
        }

        $outputTable->render();
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $warningMessage
     * @return void
     */
    public function printWarning(OutputInterface $output, string $warningMessage): void
    {
        $output->writeln(sprintf('<info>%s</info>', $warningMessage));
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $errorMessage
     * @return void
     */
    public function printError(OutputInterface $output, string $errorMessage): void
    {
        $output->writeln('<error>' . str_repeat('*', strlen($errorMessage) + 4) . '</error>');
        $output->writeln(sprintf('<error>* %s *</error>', $errorMessage));
        $output->writeln('<error>' . str_repeat('*', strlen($errorMessage) + 4) . '</error>');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $comment
     * @return void
     */
    public function printComment(OutputInterface $output, string $comment): void
    {
        $output->writeln(sprintf('<comment> --> %s</comment>', $comment));
    }
}
