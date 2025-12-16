import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Edit Post' },
];

export default function Edit() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Post" />
            <div className="p-4">
                <h1 className="text-lg font-semibold">Edit Post</h1>
                <div>Form goes here</div>
            </div>
        </AppLayout>
    );
}