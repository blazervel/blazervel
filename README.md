# Blazervel
### Opinionated Laravel development workflow for teams...
...who want to build beautiful, user-friendly web apps fast (without compromising on quality or reliability).

`composer require blazervel/blazervel`

## Blazervel Actions
A home for _all_ of your business logic. Actions are more intuitive for writing tests, they make troubleshooting easier, and they keep your "do stuff" code out of your Models. You've probably seen actions if you've worked with [Laravel Jetstream](https://github.com/laravel/jetstream/tree/2.x/src/Actions), or [Laravel Fortify](https://github.com/laravel/fortify/tree/1.x/src/Actions).

Generate a new action class using the make command: `blazervel:make Teams/Update`

```php
// app/Actions/Blazervel/Teams/Update.php

<?php declare (strict_types=1);

namespace App\Actions\Blazervel\Teams;

use Blazervel\Blazervel\Action;

class Update extends Action
{
    public function __invoke()
    {
        //
    }
}
```

### _Anonymous_ Actions (expirimental)
What started out as an expirement, is quickly turning into a core part of the workflow. Using Laravel's `Illuminate\Foundation\AliasLoader` helper, Blazervel is able to alias namespaces to anonymous action classes based on their file path. This was inspired by [Laravel 9's use of anonymous classes for migrations](https://github.com/laravel/laravel/blob/9.x/database/migrations/2014_10_12_000000_create_users_table.php). 

I find that I'm renaming classes quite often - especially for brand new builds. Whether it's because of a typo, new standardization rules, or I just want to duplicate one class as a starting point for another.

With a "proper" class, when this happens, I need to update:
- The class name
- The namespace
- The file name

Now, with an anonymous class (and Blazervel installed), I need to update:
- The file name

That's it! After building a few Laravel apps, I'm sure you realize how much time tools like this can save you over the course of a project. Spend that extra time outside. Breathing in the fresh, clean (possibly), oxygen. To put it another way; anonymous classes are to PHP, what "export default class {}" is to Javascript.

Generate a new _anonymous_ action class using the make command: `blazervel:make:anonymous Teams/Update`

```php
// app/Actions/Blazervel/Teams/Update.php

<?php declare (strict_types=1);

return new class extends BlazervelAction
{
    public function __invoke()
    {
        //
    }
};
```

### Action Helpers
Blazervel's abstract action class gives you access to a few helper methods too!

#### `authorize()`

Blazervel magically gets the user, model, and action from the current request and the action class namespace (e.g. /Teams/Update.php => /{model}s/{action}.php). Then it uses Larave's Gate facade to authorize using the corresponding Policy (e.g. app/Policies/TeamPolicy).

But if you do need to override convention...

```
$this->authorize(
    model: $team,
    user: $team->owner,
    action: 'viewAny'
);
```

#### `validate()`
Blazervel gets the data from the current request and validates it using the corresponding **Contract** (e.g. app/Contracts/TeamContract - _more about Contracts later!_).

But again, just like `authorize()`, if you do need to override with your own parameters...
```php
$this->validate(
    rules: ['name' => 'required|string|max:255']
    data: $request->only('name')
);
```

### Feature Folder Structure
Organize your actions into features and subfeatures (and - if you want - into subsubfeatures, subsubsubfeatures, and so on). Anonymous classes still work, and so do smart routes (will get to those in a second).
```
app/Actions/Blazervel...

├── Teams
│   ├── Index.php
│   └── Update.php
│   ├── Invitations
│   │   ├── Create.php
│   │   └── Approve.php
```

### Automatic Routes
Blazervel will automatically register routes for any actions that are named according to standard CRUD actions (e.g. Create, Show, Update, Destroy/Delete, Edit, Index). You don't need to do anything to configure this. If you want to override or disable route generation for a class, do so with the following protected properties on the class.
```php
return new class extends BlazervelAction
{
    protected string $route = 'teams/{team}';

    protected string $httpMethod = 'put';

    protected string|array $middleware = 'web';

    ...
}
```