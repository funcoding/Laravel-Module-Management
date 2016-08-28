<?php

namespace Sarav\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Input\InputArgument;

abstract class GeneratorCommand extends Command
{
	/**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Name of the given module
     */
    protected $name;

    /**
     * Path for the module
     */
    protected $path;

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files    = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $this->name = $this->parseName($this->getNameInput());

        $this->path = $this->getPath($this->getNameInput());

        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->getNameInput().' folder already exists!');

            return false;
        }

        $this->buildModule();
    }

    protected function buildModule()
    {
    	$this->buildInterfaceClass()
    		 ->buildModelClass()
    		 ->buildRepositoryClass()
    		 ->buildRequestClass()
    		 ->buildControllerClass()
    		 ->buildRoutes()
    		 ->buildRouteServiceProvider()
    		 ->buildProviderClass()
             ->buildMigrateClass()
             ->clearCache();
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * Parse the name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function parseName($name)
    {
        $rootNamespace = $this->getGivenNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        if (Str::contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->parseName($this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Gets the given namespace.
     *
     * @return     string  The given namespace.
     */
    protected function getGivenNamespace()
    {
    	return ($this->option('namespace')) ? $this->option('namespace') : $this->laravel->getNamespace();
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        $name = $this->parseName($rawName);

        return $this->files->exists($this->getPath($name));
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        return base_path().'/'.str_replace('\\', '/', $name).'/';
    }

    protected function getInterfaceStub()
    {
    	return __DIR__.'/stubs/interface.stub';
    }

    protected function buildClass($path, $content)
    {
    	$path = $this->buildPath($path);

    	$this->makeDirectory($path);

    	$this->files->put($path.'.php', $content);
    }

    protected function buildPath($path)
    {
    	$finalPath = base_path().'/'.str_replace('\\', '/', $path);

    	return $finalPath;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            'DummyNamespace', $this->getNamespace($name), $stub
        );

        $stub = str_replace(
            'DummyRootNamespace', $this->getGivenNamespace(), $stub
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
    	$class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
    }

    protected function replaceInterface($stub, $name)
    {
    	$stub = str_replace(
            'DummyInterfaceNamespace', $this->getInterfaceName(), $stub
        );

        $stub = str_replace(
        	'DummyInterface', $this->getLastElement($this->getInterfaceName()), $stub
        );

        return $stub;
    }


    protected function replaceModel($stub, $name)
    {
    	$stub = str_replace(
            'DummyModelNamespace', $this->getModelName(), $stub
        );

        $stub = str_replace(
        	'DummyModel', $this->getLastElement($this->getModelName()), $stub
        );

        return $stub;
    }

    protected function replaceRequest($stub, $name)
    {
		$stub = str_replace(
            'DummyRequestNamespace', $this->getRequestName(), $stub
        );

        $stub = str_replace(
        	'DummyRequest', $this->getLastElement($this->getRequestName()), $stub
        );

        return $stub;
    }

    protected function replaceController($stub, $name)
    {   
        $stub = str_replace(
            'dummyroutes', $this->getPluralName(), $stub
        );

    	$stub = str_replace(
            'DummyControllerNamespace', $this->getLastElement($this->getControllerName()), $stub
        );

        return $stub;
    }

    protected function replaceControllerFolders($stub, $name)
    {
    	$folders = explode('\\', $this->getControllerName());

    	array_pop($folders);

		$stub = str_replace(
            'DummyControllerFolders', implode('\\', $folders), $stub
        );

        return $stub;
    }

    protected function replaceBindingNames($stub, $name)
    {
    	$stub = str_replace(
            'DummyInterfaceNamespace', $this->getInterfaceName(), $stub
        );

        $stub = str_replace(
            'DummyRepositoryNamespace', $this->getRepositoryName(), $stub
        );

        return $stub;
    }

    protected function replaceRouteServiceProvider($stub, $name)
    {
    	$stub = str_replace(
            'DummyRouteServiceProviderNamespace', $this->getRouteServiceProviderName(), $stub
        );

        return $stub;
    }

    protected function replaceViewFolders($stub, $name)
    {
        $stub = str_replace(
            'dummyview', $this->getPluralName(), $stub
        );

        return $stub;
    }

    /**
     * Get the full namespace name for a given class.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function buildInterfaceClass()
    {
    	$stub    = $this->files->get(__DIR__.'/stubs/interface.stub');

    	$name    = $this->getInterfaceName();

    	$content = $this->replaceNamespace($stub, $name)
    				->replaceClass($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("Interface created successfully!");

    	return $this;
    }

    protected function buildModelClass()
    {
    	$stub    = $this->files->get(__DIR__.'/stubs/model.stub');

    	$name    = $this->getModelName();

    	$content = $this->replaceNamespace($stub, $name)
    				->replaceClass($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("Model created successfully!");

    	return $this;
    }

    protected function buildRepositoryClass()
    {
    	$stub    = $this->files->get(__DIR__.'/stubs/repository.stub');

    	$name    = $this->getRepositoryName();

    	$stub = $this->replaceNamespace($stub, $name)
    				->replaceClass($stub, $name);

    	$stub = $this->replaceInterface($stub, $name);

    	$content = $this->replaceModel($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("Repository created successfully!");

    	return $this;
    }

    protected function buildRequestClass()
    {
    	$stub = $this->files->get(__DIR__.'/stubs/request.stub');

    	$name = $this->getRequestName();

    	$content = $this->replaceNamespace($stub, $name)
    				->replaceClass($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("FormRequest created successfully!");

    	return $this;
    }

    protected function buildControllerClass()
    {
    	$stub = $this->files->get(__DIR__.'/stubs/controller.stub');

    	$name = $this->getControllerName();

    	$stub = $this->replaceNamespace($stub, $name)
    				->replaceClass($stub, $name);

    	$stub = $this->replaceRequest($stub, $name);

        $stub = $this->replaceViewFolders($stub, $name);

    	$content = $this->replaceInterface($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("Controller created successfully!");

    	return $this;
    }

    protected function buildRoutes()
    {
    	$stub = $this->files->get(__DIR__.'/stubs/routes.stub');

    	$name = $this->getRoutesName();

    	$content = $this->replaceController($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("Routes created successfully!");

    	return $this;
    }

    protected function buildRouteServiceProvider()
    {
    	$stub = $this->files->get(__DIR__.'/stubs/routeserviceprovider.stub');

    	$name = $this->getRouteServiceProviderName();

    	$content = $this->replaceNamespace($stub, $name)
    			->replaceControllerFolders($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("RouteServiceProvider created successfully!");

    	return $this;
    }

    protected function buildProviderClass()
    {
    	$stub = $this->files->get(__DIR__.'/stubs/provider.stub');

    	$name = $this->getProviderName();

    	$stub = $this->replaceNamespace($stub, $name)
    				->replaceClass($stub, $name);

    	$stub = $this->replaceBindingNames($stub, $name);

    	$content = $this->replaceRouteServiceProvider($stub, $name);

    	$this->buildClass($name, $content);

        $this->info("Provider created successfully!");

    	return $this;
    }

    public function buildMigrateClass()
    {
        if ( is_null($this->option('migrate')) ) 
        {
            return $this;
        }


        $table = $this->getPluralName();

        \Artisan::call('make:migration', [
            'name'     => 'create_'.$table.'_table',
            '--create' => $table
        ]);

        $this->composer->dumpAutoloads();

        $this->info("Table created successfully!");

        return $this;
    }

    protected function clearCache()
    {
        \Artisan::call('config:cache');
    }

    protected function getInterfaceName()
    {
    	return $this->name.'\\Repository\\'.ucfirst($this->getFileName()).'Interface';
    }

    protected function getModelName()
    {
    	return $this->name.'\\Model\\'.ucfirst($this->getFileName());
    }

    protected function getRepositoryName()
    {
    	return $this->name.'\\Repository\\'.ucfirst($this->getFileName()).'Repository';
    }

    protected function getRequestName()
    {
    	return $this->name.'\\Http\\Requests\\'.ucfirst($this->getFileName()).'Request';
    }

    protected function getControllerName()
    {
		return $this->name.'\\Http\\Controllers\\'.ucfirst($this->getFileName()).'Controller';
    }

    protected function getProviderName()
    {
    	return $this->name.'\\Providers\\'.ucfirst($this->getFileName()).'ServiceProvider';
    }

    protected function getRouteServiceProviderName()
    {
    	return $this->name.'\\Providers\\RouteServiceProvider';
    }

    protected function getRoutesName()
    {
    	return $this->name.'\\Http\\routes';
    }

    protected function getFileName()
    {
    	return $this->getLastElement($this->name);
    }

    protected function getLastElement($element)
    {
		$names = explode('\\', $element);

    	return array_pop($names);
    }

    protected function getPluralName()
    {
        return strtolower(str_plural($this->getLastElement($this->getNameInput())));
    }
}