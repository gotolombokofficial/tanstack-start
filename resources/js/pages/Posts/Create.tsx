import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Create Post' },
];

export default function Create() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Post" />
            <div className="p-4">
                <h1 className="text-lg font-semibold">Create Post</h1>
                <div>Form goes here</div>
            </div>
        </AppLayout>
    );
}