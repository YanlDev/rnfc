import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

type NavGroup = {
    label: string;
    items: NavItem[];
};

const sidebarGroups: NavGroup[] = [
    {
        label: 'Mi cuenta',
        items: [
            { title: 'Perfil', href: edit(), icon: null },
            { title: 'Seguridad', href: editSecurity(), icon: null },
            { title: 'Apariencia', href: editAppearance(), icon: null },
        ],
    },
    {
        label: 'Sitio público',
        items: [
            { title: 'Marca', href: '/settings/branding', icon: null },
            { title: 'Galería del home', href: '/settings/home', icon: null },
        ],
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <div className="px-4 py-6">
            <Heading
                title="Configuración"
                description="Administra tu cuenta y la imagen de la plataforma"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-56">
                    <nav
                        className="flex flex-col gap-6"
                        aria-label="Settings"
                    >
                        {sidebarGroups.map((group) => (
                            <div key={group.label} className="flex flex-col">
                                <h3 className="mb-2 px-3 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                    {group.label}
                                </h3>
                                <div className="flex flex-col space-y-1">
                                    {group.items.map((item, index) => (
                                        <Button
                                            key={`${toUrl(item.href)}-${index}`}
                                            size="sm"
                                            variant="ghost"
                                            asChild
                                            className={cn('w-full justify-start', {
                                                'bg-muted': isCurrentOrParentUrl(item.href),
                                            })}
                                        >
                                            <Link href={item.href}>
                                                {item.icon && (
                                                    <item.icon className="h-4 w-4" />
                                                )}
                                                {item.title}
                                            </Link>
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1 md:max-w-3xl">
                    <section className="max-w-3xl space-y-12">
                        {children}
                    </section>
                </div>
            </div>
        </div>
    );
}
