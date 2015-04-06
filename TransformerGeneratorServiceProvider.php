<?php

namespace Mulford\Generators;

use Illuminate\Support\ServiceProvider;

class TransformerGeneratorServiceProvider extends ServiceProvider {

  /**
   * Register the generator(s)
   *
   * @return void
   */
  public function register()
  {
    $this->registerMakeTransformer();
  }

  /**
   * Register the make:transformer generator.
   */
  private function registerMakeTransformer()
  {
    $this->app->singleton('command.mulford.transformer', function ($app) {
      
      return $app['Mulford\Generators\Commands\CreateTransformerCommand'];

    });

    $this->commands('command.mulford.transformer');
  }

}
