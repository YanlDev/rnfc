import { Link, usePage } from '@inertiajs/react';
import {
    Award,
    Building2,
    LayoutGrid,
    ShieldCheck,
    Settings2,
    UserCog,
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
import type { NavGroup } from '@/types';

/**
 * Construye el menú según el rol global de plataforma:
 *  - Visión global (Admin o Gerente): ve Certificados y las vistas globales.
 *  - Admin: además, el grupo de Administración (usuarios, permisos, panel).
 *  - Usuario: solo Panel y Obras; la diferenciación fina entre los 4 roles de
 *    obra ocurre DENTRO de cada obra (matriz de permisos).
 */
function construirGrupos(
    esAdmin: boolean,
    visionGlobal: boolean,
    esAdminObra: boolean,
): NavGroup[] {
    // Todos: navegan el panel y sus obras.
    const general: NavGroup = {
        label: 'General',
        items: [
            { title: 'Panel', href: dashboard(), icon: LayoutGrid },
            { title: 'Obras', href: obras.index().url, icon: Building2 },
        ],
    };

    // Certificados: los ven Admin y Gerente (visión global) y las
    // administradoras de obra (para emitir a su personal).
    if (visionGlobal || esAdminObra) {
        general.items.push({
            title: 'Certificados',
            href: certificados.index().url,
            icon: Award,
        });
    }

    if (!esAdmin) {
        return [general];
    }

    const administracion: NavGroup = {
        label: 'Administración',
        items: [
            { title: 'Panel admin', href: '/admin', icon: Settings2 },
            { title: 'Usuarios', href: '/admin/usuarios', icon: UserCog },
            { title: 'Permisos', href: '/admin/permisos', icon: ShieldCheck },
        ],
    };

    return [general, administracion];
}

export function AppSidebar() {
    const { auth } = usePage<{
        auth: {
            user: {
                es_admin?: boolean;
                vision_global?: boolean;
                es_admin_obra?: boolean;
            } | null;
        };
    }>().props;
    const esAdmin = auth?.user?.es_admin === true;
    const visionGlobal = auth?.user?.vision_global === true;
    const esAdminObra = auth?.user?.es_admin_obra === true;

    const groups = construirGrupos(esAdmin, visionGlobal, esAdminObra);

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
                <NavMain groups={groups} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
