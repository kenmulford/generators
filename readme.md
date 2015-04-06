# Laravel 5/Fractal Transformer Generator

This extends the php artisan command list to include a command for generating a basic transformer based on your existing Eloquent model.

- `make:transformer`

## Usage

### Step 1: Install Via Composer

```
composer require 'mulford/generators' --dev
```

### Step 2: Register the Service Provider

Open `config/app.php` and add the line to the bottom of your `providers` array:

```
"Mulford\Generators\TransformerGeneratorServiceProvider"
```

### Step 3: Run Artisan

Run `php artisan list` from the console and you will see `make:transformer' in the "make" section.

## Defaults

By default, this library references models in the App\Models namespace (not the Laravel 5 default of App). It creates new Transformer classes in App\Transformers. In a future version I will try to make this a config option.

## Example

`php artisan make:transformer --model=User`

This command will create a basic Fractal Transformer class with some placeholders, as well as a basic transform() method. By default the transform() object array is populated based on the Eloquent model's `$fillable` property. Obviously you can tweak the list as desired, but it serves as a decent starting point.

## Future

Eventually I hope to work out a way to create includes based on Eloquent relationships. If you are interested in helping feel free to send a pull request!