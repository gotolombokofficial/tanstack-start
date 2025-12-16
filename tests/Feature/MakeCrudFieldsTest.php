<?php

use Illuminate\Support\Str;

it('creates migration columns and request validation for fields', function () {
    $name = 'Widget';

    $fields = 'title:string,body:text,views:integer,active:boolean';

    $this->artisan("make:crud {$name} --fields={$fields}")->assertExitCode(0);

    // Migration should contain columns
    $table = Str::plural(Str::kebab($name));
    $migrations = glob(database_path("migrations/*_create_{$table}_table.php"));
    expect(count($migrations))->toBeGreaterThan(0);

    $migration = end($migrations);
    $content = file_get_contents($migration);

    expect(str_contains($content, "\$table->string('title')") || str_contains($content, "\$table->text('body')") || str_contains($content, "\$table->integer('views')"))->toBeTrue();

    // Requests should contain rules
    $store = app_path("Http/Requests/Store{$name}Request.php");
    $update = app_path("Http/Requests/Update{$name}Request.php");

    $storeContent = file_get_contents($store);
    expect(str_contains($storeContent, "'title' => 'required|string'") || str_contains($storeContent, "'body' => 'required|string'"))->toBeTrue();

    // Cleanup
    @unlink($store);
    @unlink($update);
    @unlink(app_path("Models/{$name}.php"));
    @unlink(app_path("Http/Controllers/{$name}Controller.php"));
    @unlink(resource_path("js/pages/" . ucfirst($table) . "/Index.tsx"));
    @unlink(resource_path("js/pages/" . ucfirst($table) . "/Create.tsx"));
    @unlink(resource_path("js/pages/" . ucfirst($table) . "/Edit.tsx"));
    foreach (glob(database_path("migrations/*_create_{$table}_table.php")) as $m) {
        @unlink($m);
    }
});
