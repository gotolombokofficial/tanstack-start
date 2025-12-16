<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {name : The model name, singular (e.g. Post)} {--fields= : Comma separated list of fields e.g. title:string,body:text} {--force}';

    protected $description = 'Scaffold a basic CRUD (Model, Migration, Controller, Requests, Routes, and React pages)';

    public function handle(Filesystem $files): int
    {
        $name = trim($this->argument('name'));
        $model = ucfirst($name);
        $table = \Illuminate\Support\Str::plural(\Illuminate\Support\Str::kebab($name));

        $this->info("Generating CRUD for: {$model}");

        // Create model with migration & factory
        $this->callSilent('make:model', ['name' => "{$model}", '--migration' => true, '--factory' => true, '--force' => true]);

        // Parse --fields option into an associative array [name => type]
        $fieldsOption = $this->option('fields') ?: '';
        $fields = $this->parseFields($fieldsOption);

        // Create controller
        $controller = "{$model}Controller";
        $this->callSilent('make:controller', ['name' => "App\\Http\\Controllers\\{$controller}", '--resource' => true, '--model' => "App\\Models\\{$model}", '--force' => true]);

        // Create FormRequest
        $this->callSilent('make:request', ['name' => "Store{$model}Request", '--force' => true]);
        $this->callSilent('make:request', ['name' => "Update{$model}Request", '--force' => true]);

        // If fields were passed, patch migration and form requests
        if (! empty(
            $fields
        )) {
            $this->applyFieldsToMigration($fields, $table, $files);
            $this->applyFieldsToRequests($fields, $model, $files);
        }

        // Append resource route to routes/web.php inside auth middleware group
        $routesPath = base_path('routes/web.php');
        $routeStub = "\n    // CRUD routes for {$model}\n    Route::resource('{$table}', App\\Http\\Controllers\\{$controller}::class);\n";

        $web = $files->get($routesPath);

        if (str_contains($web, "Route::resource('{$table}'")) {
            $this->line('Routes already exist, skipping route insertion.');
        } else {
            // Try to find the auth middleware group and insert before closing bracket
            $pattern = "Route::middleware(['auth', 'verified'])->group(function () {";
            $pos = strpos($web, $pattern);
            if ($pos !== false) {
                $insertPos = strpos($web, "});", $pos);
                if ($insertPos !== false) {
                    $newWeb = substr($web, 0, $insertPos) . $routeStub . substr($web, $insertPos);
                    $files->put($routesPath, $newWeb);
                    $this->info('Added routes to routes/web.php');
                }
            } else {
                // Fallback: append at end
                $files->append($routesPath, "\n<?php\n" . $routeStub);
                $this->info('Appended routes to routes/web.php');
            }
        }

        // Create simple React Inertia pages
        $jsBase = resource_path('js/pages/' . ucfirst($table));
        if (! $files->isDirectory($jsBase)) {
            $files->makeDirectory($jsBase, 0755, true);
        }

        $indexStub = $this->getIndexPageStub($model, $table);
        $createStub = $this->getFormPageStub($model, $table, 'Create');
        $editStub = $this->getFormPageStub($model, $table, 'Edit');

        $files->put($jsBase . '/Index.tsx', $indexStub);
        $files->put($jsBase . '/Create.tsx', $createStub);
        $files->put($jsBase . '/Edit.tsx', $editStub);

        // Inform user to run Wayfinder generate (if using wayfinder)
        $this->line("Run `php artisan wayfinder:generate` to update TypeScript route helpers.");

        $this->info('CRUD scaffolding complete. Remember to add validation rules in the generated FormRequests and implement controller methods as needed.');

        return 0;
    }

    protected function getIndexPageStub(string $model, string $table): string
    {
        $title = \Illuminate\Support\Str::plural($model);
        return <<<TSX
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: '{$title}', href: dashboard().url },
];

export default function Index() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="{$title}" />
            <div className="p-4">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-lg font-semibold">{$title}</h1>
                    <Link href={'/{$table}/create'} className="btn">Create</Link>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full table-fixed">
                        <thead>
                            <tr>
                                <th className="text-left">ID</th>
                                <th className="text-left">Title</th>
                                <th className="text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>—</td>
                                <td>Placeholder</td>
                                <td>
                                    <Link href={'/{$table}/1/edit'} className="btn">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
TSX;
    }

    protected function getFormPageStub(string $model, string $table, string $mode): string
    {
        return <<<TSX
import AppLayout from '@/layouts/app-layout';
import { Head, Form, useForm } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: '{$mode} {$model}' },
];

export default function {$mode}() {
    const form = useForm({ title: '' });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="{$mode} {$model}" />
            <div className="p-4">
                <h1 className="text-lg font-semibold">{$mode} {$model}</h1>
                <Form onSubmit={(e) => { e.preventDefault(); form.post('/{$table}'); }}>
                    <div className="space-y-4">
                        <input name="title" placeholder="Title" className="input" onChange={(e) => form.setData('title', (e.target as HTMLInputElement).value)} value={form.data.title} />
                        <div className="flex gap-2">
                            <button type="submit" className="btn">Save</button>
                        </div>
                    </div>
                </Form>
            </div>
        </AppLayout>
    );
}
TSX;
    }

    protected function parseFields(string $input): array
    {
        $fields = [];
        $pairs = array_filter(array_map('trim', explode(',', $input)));
        foreach ($pairs as $p) {
            [$name, $type] = array_pad(explode(':', $p, 2), 2, 'string');
            $fields[$name] = $type ?: 'string';
        }

        return $fields;
    }

    protected function applyFieldsToMigration(array $fields, string $table, Filesystem $files): void
    {
        $pattern = database_path('migrations/*_create_' . $table . '_table.php');
        $migrations = glob($pattern);
        if (empty($migrations)) {
            $this->warn("No migration found for table {$table} to modify.");
            return;
        }

        // Use the latest migration
        $migration = end($migrations);
        $content = $files->get($migration);

        // Build columns stub with proper indentation
        $columns = "";
        foreach ($fields as $name => $type) {
            $columns .= "            " . $this->migrationColumnStub($name, $type) . "\n";
        }

        // Insert before timestamps(); if present
        if (str_contains($content, "\$table->timestamps();")) {
            $content = str_replace("\$table->timestamps();", $columns . "            \$table->timestamps();", $content);
        } else {
            // Fallback: insert before the end of the closure
            $content = str_replace("});", $columns . "        });", $content);
        }

        $files->put($migration, $content);
        $this->info("Patched migration: {$migration}");
    }

    protected function migrationColumnStub(string $name, string $type): string
    {
        return match ($type) {
            'text' => "\$table->text('{$name}');",
            'integer' => "\$table->integer('{$name}')->nullable();",
            'bigint' => "\$table->bigInteger('{$name}')->nullable();",
            'boolean' => "\$table->boolean('{$name}')->default(false);",
            'datetime' => "\$table->dateTime('{$name}')->nullable();",
            default => "\$table->string('{$name}')->nullable();",
        };
    }

    protected function applyFieldsToRequests(array $fields, string $model, Filesystem $files): void
    {
        $storePath = app_path("Http/Requests/Store{$model}Request.php");
        $updatePath = app_path("Http/Requests/Update{$model}Request.php");

        $rules = [];
        foreach ($fields as $name => $type) {
            $rules[$name] = match ($type) {
                'text' => "required|string",
                'integer' => "required|integer",
                'boolean' => "sometimes|boolean",
                'datetime' => "required|date",
                default => "required|string",
            };
        }

        $rulesCode = "return [\n";
        foreach ($rules as $k => $r) {
            $rulesCode .= "            '{$k}' => '{$r}',\n";
        }
        $rulesCode .= "        ];";

        if ($files->exists($storePath)) {
            $content = $files->get($storePath);
            $content = preg_replace("/return \[.*?\];/s", $rulesCode, $content, 1);
            $files->put($storePath, $content);
            $this->info("Patched {$storePath}");
        }

        if ($files->exists($updatePath)) {
            $content = $files->get($updatePath);
            $content = preg_replace("/return \[.*?\];/s", $rulesCode, $content, 1);
            $files->put($updatePath, $content);
            $this->info("Patched {$updatePath}");
        }
    }
}

