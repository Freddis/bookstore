<?php

namespace App\Console\Commands;

use App\Service\ImportService;
use Illuminate\Console\Command;

class ParseCatalogueXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalogue:parse  {file : Filename in resources folder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parses XML file with name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param ImportService $importService
     * @return int
     * @throws \Exception
     */
    public function handle(ImportService $importService)
    {
        $file = $this->argument("file");
        $this->line("Starting");
        $importService->process($file);

        return 0;
    }
}
