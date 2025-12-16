# tanstack-start

This repository is a starter kit (Laravel + Inertia + React + Wayfinder).

---

## ✅ CRUD generator (make:crud)

A simple generator was added to scaffold a Filament-like CRUD quickly.

### What it does
- Generates a Model, Migration, Factory
- Creates a Resource Controller (resourceful), and Form Requests (Store/Update)
- Inserts a resource route (`routes/web.php`) into the auth/verified group if present
- Creates basic Inertia React pages at `resources/js/pages/{PluralTable}/{Index,Create,Edit}.tsx`
- Patches the migration and the Form Requests when using `--fields`

### Usage
```bash
# Basic scaffold (no fields)
php artisan make:crud Post

# With fields (generates columns in migration + validation rules in requests)
php artisan make:crud Post --fields="title:string,body:text,views:integer,active:boolean"
```

After generating, run Wayfinder to update TypeScript helpers (if you use Wayfinder):
```bash
php artisan wayfinder:generate
```

### Notes & next steps
- Edit the generated Form Requests to tune validation and the Controller methods to implement persistence logic.
- Frontend stubs include a simple table placeholder and an Inertia `<Form>` using `useForm`.
- Supported field types: `string` (default), `text`, `integer`, `bigint`, `boolean`, `datetime`.
- Future improvements: add nullable/unique modifiers, foreign key support, full list API wiring with Wayfinder.

---

If you'd like, I can extend the generator to wire the frontend to Wayfinder endpoints and generate a working list/create/edit flow. (Ask me to implement this.)
