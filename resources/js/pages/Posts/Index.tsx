import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Posts', href: dashboard().url },
];

export default function Index({ posts }: { posts: { data: any[]; meta?: any } }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Posts" />
            <div className="p-4">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-lg font-semibold">Posts</h1>
                    <Link href={'/posts/create'} className="btn">Create</Link>
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
                            {posts?.data?.map((post: any) => (
                                <tr key={post.id}>
                                    <td className="pr-4">{post.id}</td>
                                    <td className="pr-4">{post.title}</td>
                                    <td>
                                        <Link href={`/posts/${post.id}/edit`} className="btn">Edit</Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {posts?.meta && (
                        <div className="mt-4">Page {posts.meta.current_page} of {posts.meta.last_page}</div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}