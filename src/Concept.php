<?php

namespace Blazervel\Blazervel;

use Illuminate\Support\{ Str, Collection };
use Illuminate\Support\Facades\Route;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;

use Blazervel\Blazervel\Exceptions\BlazervelConceptException;

class Concept
{
  public string $name;
  public string $namespace;
  public string $path;
  public array $operations;

  public function __construct(string $name, string $namespace, string $path)
  {
    $this->name = $name;
    $this->namespace = $namespace;
    $this->path = $path;

    $this->retrieveOperations();
  }

  public function retrieveOperations(): array
  {
    $operations = [];
    $files = (new FileSystem)->files(
      "{$this->path}/Operations"
    );

    foreach ($files as $file) :
      $name = Str::remove(
        '.php', 
        basename($file->getFileName())
      );

      $actionClass = "{$this->namespace}\\Operations\\{$name}";

      $operations[$name] = new $actionClass;
    endforeach;

    return $this->operations = $operations;
  }

  public static function list(): array
  {
    $concepts = [];

    $directories = (new FileSystem)->directories(
      base_path() . '/app/Concepts'
    );

    foreach($directories as $path) :
      $name = basename($path);
      $concepts[$name] = new self(
        name: $name,
        namespace: "App\\Concepts\\{$name}",
        path: $path,
      );
    endforeach;

    return $concepts;
  }

  public static function operations()
  {
    return (new Collection(
      Concept::list()
    ))->pluck('operations')->flatten()->all();
  }

  public static function conceptFor(string $conceptItemClass): self
  {
    return (
      new self(
        name: self::conceptName($conceptItemClass),
        namespace: self::conceptNamespace($conceptItemClass),
        path: self::conceptPath($conceptItemClass),
      )
    );
  }

  public static function conceptName(string $conceptItemClass)
  {
    $conceptNamespace = self::conceptNamespace(
      $conceptItemClass
    );

    return (
      array_reverse(
        explode('\\', $conceptNamespace)
      )[0]
    );
  }

  public static function conceptPath(string $conceptItemClass)
  {
    $namespace = self::conceptNamespace($conceptItemClass);

    return base_path(
      Str::replace('App', 'app', 
        Str::replace('\\', '/', $namespace)
      )
    );
  }

  public static function conceptNamespace(string $conceptItemClass)
  {
    $utility = class_basename(
      get_parent_class($conceptItemClass) // contract|operation|policy|component
    );

    // Need smarter way of finding closest Concept namespace
    // May want to support plural and singular for Concept Items
    $utilityNamespaceSlug = in_array($utility, ['Operation', 'Component']) ? "{$utility}s" : $utility;

    $conceptItemClassNamespace = explode('\\', $conceptItemClass);

    $conceptItemClassNamespace = array_slice(
      $conceptItemClassNamespace,
      0,
      array_search($utilityNamespaceSlug, $conceptItemClassNamespace)
    );

    return join('\\', $conceptItemClassNamespace);
  }

  public static function componentFor(string $conceptItemClass)
  {
    $action = class_basename($conceptItemClass);
    $namespace = self::conceptNamespace(
      conceptItemClass: $conceptItemClass
    );

    return "{$namespace}\\Components\\{$action}";
  }

  public static function registerRoutes()
  {
    foreach(Concept::operations() as $name => $operation) :
      $method = Str::lower($operation->method);
      Route::$method(
        $operation->uri, 
        $operation::class
      )->middleware(
        $operation->httpMiddleware
      );
    endforeach;

    //Route::get('blazervel/js/blazervel.js', function(){ return response()->header(); });
    //Route::get('blazervel/css/blazervel.css', function(){ return response()->header(); });

    $endpoint = 'blazervel/concepts/{conceptName}/components/{operationName}';

    Route::get($endpoint, function(string $conceptName, string $operationName){
      $conceptName    = Str::ucfirst(Str::camel($conceptName));
      $operationName  = Str::ucfirst(Str::camel($operationName));
      $componentClass = "\\App\\Concepts\\{$conceptName}\\Components\\{$operationName}";
      $component      = new $componentClass;

      return response()->json(
        $component->stateData(), 
        200
      );
    });

    Route::post("{$endpoint}/actions/{actionName}", function(
      Request $request, 
      string $conceptName, 
      string $operationName, 
      string $actionName
    ){
      $conceptName    = Str::ucfirst(Str::camel($conceptName));
      $operationName  = Str::ucfirst(Str::camel($operationName));
      $componentClass = "\\App\\Concepts\\{$conceptName}\\Components\\{$operationName}";
      $component      = new $componentClass;

      if (method_exists($component, $actionName)) :
        return response()->json([
          'error' => __('blazervel::components.action_doesnt_exist_on_component', [
            'action_name' => $actionName,
            'operation_name' => $operationName,
          ])
        ], 500);
      endif;

      $component->$actionName();

      return response()->json(
        $component->stateData(), 
        200
      );
    });
  }

  public static function scheduleables()
  {
    return (new Collection(
      Concept::operations()
    ))->whereNotNull('scheduleFrequency')->all();
  }

  public static function __callStatic($name, $arguments)
  {
    //
  }

  public function __call($name, $arguments)
  {
    //
  }
  
}