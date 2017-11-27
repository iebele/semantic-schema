<?php namespace Iebele\SemanticSchema\Commands;

use Illuminate\Console\Command;

class SchemaUpdate extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'schema:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tables with Schema.org data.';

    /**
     *
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->info('it works!');
    }
    
}