import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posts', href: dashboard().url },
];

export default function Index() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Posts" />
            <div className="p-4">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-lg font-semibold">Posts</h1>
                    <Link href={{ url: '/posts/create', method: 'get' }} className="btn">Create</Link>
                </div>
                <div>List will go here</div>
            </div>
        </AppLayout>
    );
}