import { Link, usePage } from '@inertiajs/react';
import {
    Award,
    Building2,
    CalendarDays,
    FolderTree,
    LayoutGrid,
    NotebookPen,
    Settings2,
    UserCog,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import certificados from '@/routes/certificados';
import obras from '@/routes/obras';
import type { NavItem } from '@/types';

const baseNavItems: NavItem[] = [
    { title: 'Panel', href: dashboard(), icon: LayoutGrid },
    { title: 'Obras', href: obras.index().url, icon: Building2 },
    { title: 'Certificados', href: certificados.index().url, icon: Award },
    { title: 'Equipo', href: '/equipo', icon: Users },
    { title: 'Documentos', href: dashboard(), icon: FolderTree },
    { title: 'Cuaderno de Obra', href: '/cuaderno', icon: NotebookPen },
    { title: 'Calendario', href: '/calendario', icon: CalendarDays },
];

const adminNavItems: NavItem[] = [
    { title: 'Administración', href: '/admin', icon: Settings2 },
    { title: 'Usuarios', href: '/admin/usuarios', icon: UserCog },
];

export function AppSidebar() {
    const { auth } = usePage<{ auth: { user: { es_admin?: boolean } | null } }>().props;
    const esAdmin = auth?.user?.es_admin === true;

    const mainNavItems: NavItem[] = esAdmin
        ? [...baseNavItems, ...adminNavItems]
        : baseNavItems;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
