<?php

namespace App\Command;

use App\Service\ProductImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-products',
    description: 'Import categories and products from JSON files',
)]
class ImportProductsCommand extends Command
{
    public function __construct(
        private ProductImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('categories', InputArgument::REQUIRED, 'Path to categories JSON file')
            ->addArgument('products', InputArgument::REQUIRED, 'Path to products JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $categoriesFile = $input->getArgument('categories');
        $productsFile = $input->getArgument('products');

        if (!file_exists($categoriesFile)) {
            $io->error("Categories file not found: $categoriesFile");
            return Command::FAILURE;
        }

        if (!file_exists($productsFile)) {
            $io->error("Products file not found: $productsFile");
            return Command::FAILURE;
        }

        $io->title('Importing data from JSON files');

        try {
            $result = $this->importer->import($categoriesFile, $productsFile);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->section('Categories');
        $io->table(
            ['Created', 'Updated', 'Skipped'],
            [[$result['categories']['created'], $result['categories']['updated'], $result['categories']['skipped']]]
        );

        $io->section('Products');
        $io->table(
            ['Created', 'Updated', 'Skipped'],
            [[$result['products']['created'], $result['products']['updated'], $result['products']['skipped']]]
        );

        $io->success('Import completed!');

        return Command::SUCCESS;
    }
}