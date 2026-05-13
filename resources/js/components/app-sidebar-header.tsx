import { Breadcrumbs } from '@/components/breadcrumbs';
import { NotificacionesDropdown } from '@/components/notificaciones-dropdown';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
    title,
    description,
}: {
    breadcrumbs?: BreadcrumbItemType[];
    title?: string;
    description?: string;
}) {
    return (
        <header className="flex shrink-0 flex-col gap-1 border-b border-sidebar-border/50 px-6 py-3 transition-[width,height] ease-linear md:px-4">
            <div className="flex items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                    <SidebarTrigger className="-ml-1" />
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
                <NotificacionesDropdown />
            </div>
            {(title || description) && (
                <div className="flex flex-col gap-0.5 pl-9 md:flex-row md:items-baseline md:gap-3">
                    {title && (
                        <h1 className="text-base font-bold tracking-tight text-foreground">
                            {title}
                        </h1>
                    )}
                    {description && (
                        <p className="text-xs text-muted-foreground md:text-sm">
                            {description}
                        </p>
                    )}
                </div>
            )}
        </header>
    );
}
