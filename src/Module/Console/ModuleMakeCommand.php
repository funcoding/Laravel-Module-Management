<?php

namespace Sarav\Console;

use Illuminate\Support\Str;
use Sarav\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class ModuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module model class';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        parent::fire();
    }

    /**
     * The array of command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'The namespace attributes.',
                null
            ],
            [
                'migrate',
                null,
                InputOption::VALUE_OPTIONAL,
                'The migrate attributes.',
                null
            ],
        ];
    }
}