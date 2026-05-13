import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    title,
    description,
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    return (
        <AppLayoutTemplate
            breadcrumbs={breadcrumbs}
            title={title}
            description={description}
        >
            {children}
        </AppLayoutTemplate>
    );
}
