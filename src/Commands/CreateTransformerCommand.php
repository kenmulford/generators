<?php namespace Mulford\Generators\Commands;

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
   * The model namespace.
   *
   * @var string
   */
  protected $model_namespace = 'App/Models';

  /**
   * The model namespace.
   *
   * @var string
   */
  protected $transformer_namespace = 'app/Transformers';

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
    return __DIR__.'/../stubs/transformer.stub';
  }

  /**
   * Get the destination class path.
   *
   * @param  string  $name
   * @return string
   */
  protected function getPath($name)
  {
    return './'.$this->option('transformerNamespace').'/'.str_replace('\\', '/', $name).'.php';
  }


  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function fire()
  {
    $this->model = $this->option('model');
    $this->model_namespace = $this->option('modelNamespace');
    $this->transformer_namespace = ucwords( $this->option('transformerPath') );

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
      ['modelNamespace', null, InputOption::VALUE_OPTIONAL, 'Namespace for your models (default: --modelNamespace=App\Models)', null],
      ['transformerPath', null, InputOption::VALUE_OPTIONAL, 'Name of the Eloquent model class (default: --transformerPath=app\Transformers)', null],
    ];
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
   * OVERRIDE: Replace the namespace for the given stub.
   *
   * @param  string  $stub
   * @param  string  $name
   * @return $this
   */
  protected function replaceNamespace(&$stub, $name)
  {
    $stub = str_replace(
      '{{transformer_namespace}}', ucwords($this->transformer_namespace), $stub
    );

    $stub = str_replace(
      '{{model_namespace}}', ucwords($this->model_namespace), $stub
    );

    return $this;
  }

  /**
   * Retrieves all fillable fields from the specified model
   * 
   * @return array
   */ 

  private function getModelFields() {

    $model = (string)"\App\Models\\" . $this->model;
    $record = new $model();

    return $record->getFillable();

  }

  /**
   * Retrieves all fillable fields from the specified model
   * 
   * @return array
   */ 

  private function getModelRelations() {

    $model = (string)"\App\Models\\" . $this->model;
    $record = new $model();

    return $record->getRelations();

  }

  /**
   * Retrieves all fillable fields and turns them into an array in
   * the transform() method of the Transformer file. 
   * 
   * @param string $stub 
   * 
   * @return object $this
   */ 

  private function populateTransformFields(&$stub) {

    $rows[] = '"id" => $record->id'.PHP_EOL;

    foreach($this->fields as $key=>$name) {
      if($name != 'id') {
        // don't re-insert the PK
        $rows[] = '"'.$name.'" => $record->'.$name.PHP_EOL;
      }
    }

    if( !isset($rows['created_at']) ) {
      $rows[] = '",created_at" => $record->created_at'.PHP_EOL;
    }
    if( !isset($rows['updated_at']) ) {
      $rows[] = '",updated_at" => $record->updated_at'.PHP_EOL;
    }

    $field_string = implode("      ,", $rows);

    $stub = str_replace('{{field_list}}', $field_string, $stub);

    return $this;
  }

}
