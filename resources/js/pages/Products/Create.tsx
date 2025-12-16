import AppLayout from '@/layouts/app-layout';
import { Head, Form, useForm } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Create Product' },
];

export default function Create() {
    const form = useForm({ title: '' });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Product" />
            <div className="p-4">
                <h1 className="text-lg font-semibold">Create Product</h1>
                <Form onSubmit={(e) => { e.preventDefault(); form.post('/products'); }}>
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