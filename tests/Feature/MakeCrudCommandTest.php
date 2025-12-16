<?php

use Illuminate\Support\Str;

it('creates crud scaffolding', function () {
    $name = 'Gizmo';

    // Run the artisan command
    $this->artisan("make:crud {$name}")->assertExitCode(0);

    // Files we expect
    $model = app_path("Models/{$name}.php");
    $controller = app_path("Http/Controllers/{$name}Controller.php");
    $storeRequest = app_path("Http/Requests/Store{$name}Request.php");
    $updateRequest = app_path("Http/Requests/Update{$name}Request.php");

    expect(file_exists($model))->toBeTrue();
    expect(file_exists($controller))->toBeTrue();
    expect(file_exists($storeRequest))->toBeTrue();
    expect(file_exists($updateRequest))->toBeTrue();

    // JS pages
    $table = Str::plural(Str::kebab($name));
    $index = resource_path("js/pages/" . ucfirst($table) . "/Index.tsx");
    $create = resource_path("js/pages/" . ucfirst($table) . "/Create.tsx");
    $edit = resource_path("js/pages/" . ucfirst($table) . "/Edit.tsx");

    expect(file_exists($index))->toBeTrue();
    expect(file_exists($create))->toBeTrue();
    expect(file_exists($edit))->toBeTrue();

    // Cleanup generated files to avoid polluting repo
    @unlink($model);
    @unlink($controller);
    @unlink($storeRequest);
    @unlink($updateRequest);
    @unlink($index);
    @unlink($create);
    @unlink($edit);

    // Remove generated migration(s)
    $migrations = glob(database_path('migrations/*_create_gizmos_table.php'));
    foreach ($migrations as $m) {
        @unlink($m);
    }
});
