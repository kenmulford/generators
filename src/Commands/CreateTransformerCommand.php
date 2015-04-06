<?php namespace Mulford\Generators;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateTransformerCommand extends GeneratorCommand {

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:transformer';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate Fractal Transformer scaffolding from an Eloquent Model.';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Transformer';

  /**
   * The model being generated.
   *
   * @var string
   */
  protected $model;

  /**
   * The fillable fields of the model being imported.
   *
   * @var array
   */
  protected $fields;

  /**
   * Parse the name and format according to the root namespace.
   *
   * @param  string  $name
   * @return string
   */
  protected function parseName($name)
  {
    return ucwords(camel_case($name)) . 'Transformer';
  }

  /**
   * Get the stub file for the generator.
   *
   * @return string
   */
  protected function getStub()
  {
    return __DIR__.'/stubs/transformer.stub';
  }

  /**
   * Get the destination class path.
   *
   * @param  string  $name
   * @return string
   */
  protected function getPath($name)
  {
    return './app/Transformers/'.str_replace('\\', '/', $name).'.php';
  }


  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function fire()
  {
    $this->model = $this->option('model');

    $this->fields = $this->getModelFields();


    $name = $this->parseName( $this->model );

    if ($this->files->exists($path = $this->getPath($name)))
    {
      return $this->error($this->type.' already exists!');
    }

    $this->makeDirectory($path);

    $this->files->put($path, $this->buildClass($name));

    $this->info($this->type.' created successfully.');

  }

  /**
   * OVERRIDE: Build the class with the given name.
   *
   * @param  string  $name
   * @return string
   */
  protected function buildClass($name)
  {
    $stub = $this->files->get( $this->getStub() );

    return $this->replaceNamespace($stub, $name)
                ->replaceModel($stub)
                ->populateTransformFields($stub)
                ->replaceClass($stub, $name);
  }

  /**
   * NEW: Replace the model name for the given stub.
   *
   * @param  string  $stub
   * @param  string  $name
   * @return string
   */
  protected function replaceModel(&$stub)
  {
    $model = (string)$this->model;

    $stub = str_replace('{{model}}', $model, $stub);
    
    return $this;

  }



  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return [
      // ['model', InputArgument::REQUIRED, 'Name of the Eloquent model class (--model=User)'],
    ];
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return [
      ['model', null, InputOption::VALUE_REQUIRED, 'Name of the Eloquent model class (--model=User)', null],
    ];
  }



  private function getModelFields() {

    $model = (string)"\App\Models\\" . $this->model;
    $record = new $model();

    return $record->getFillable();

  }

  private function populateTransformFields(&$stub) {

    $rows = [];
    foreach($this->fields as $key=>$name) {

      $rows[] = '"'.$name.'" => $record->'.$name.PHP_EOL;

    }

    $field_string = implode("      ,", $rows);

    $stub = str_replace('{{field_list}}', $field_string, $stub);

    return $this;
  }

}
