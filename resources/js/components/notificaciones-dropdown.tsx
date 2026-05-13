import { Link, router, usePage } from '@inertiajs/react';
import {
    Award,
    Bell,
    Building2,
    CalendarClock,
    CheckCheck,
    FolderTree,
    NotebookPen,
    UserPlus,
    XCircle,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

type NotificacionReciente = {
    id: string;
    titulo: string;
    mensaje: string;
    obra_codigo: string | null;
    url: string | null;
    icono: string;
    color: string;
    leida: boolean;
    created_at_relativo: string;
};

type NotificacionesHeaderShared = {
    unreadCount: number;
    recientes: NotificacionReciente[];
};

const ICONOS: Record<string, React.ComponentType<{ className?: string }>> = {
    Bell,
    Award,
    Building2,
    CalendarClock,
    FolderTree,
    NotebookPen,
    UserPlus,
    XCircle,
};

export function NotificacionesDropdown() {
    const shared = usePage<{ notificacionesHeader?: NotificacionesHeaderShared }>().props
        .notificacionesHeader;

    if (!shared) return null;

    const unreadCount = shared.unreadCount ?? 0;
    const recientes = shared.recientes ?? [];

    const irA = (id: string) => {
        router.post(
            `/notificaciones/${id}/leida`,
            {},
            { preserveScroll: false },
        );
    };

    const marcarTodas = () => {
        router.post('/notificaciones/marcar-todas', {}, { preserveScroll: true });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button
                    type="button"
                    aria-label="Notificaciones"
                    className="relative rounded-md p-2 text-muted-foreground hover:bg-muted hover:text-foreground"
                >
                    <Bell className="size-5" />
                    {unreadCount > 0 && (
                        <span className="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-destructive text-[9px] font-bold text-white">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    )}
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-[360px] p-0">
                <div className="flex items-center justify-between border-b border-border px-3 py-2">
                    <div className="flex items-center gap-2 text-sm font-semibold">
                        <Bell className="size-4 text-primary" />
                        Notificaciones
                        {unreadCount > 0 && (
                            <Badge variant="destructive" className="text-[10px]">
                                {unreadCount}
                            </Badge>
                        )}
                    </div>
                    {unreadCount > 0 && (
                        <Button
                            size="sm"
                            variant="ghost"
                            onClick={marcarTodas}
                            className="text-xs"
                        >
                            <CheckCheck className="size-3.5" />
                            Marcar todas
                        </Button>
                    )}
                </div>

                <div className="max-h-[420px] overflow-y-auto">
                    {recientes.length === 0 ? (
                        <div className="p-6 text-center text-sm text-muted-foreground">
                            No tienes notificaciones todavía.
                        </div>
                    ) : (
                        <ul>
                            {recientes.map((n) => {
                                const Icono = ICONOS[n.icono] ?? Bell;
                                return (
                                    <li
                                        key={n.id}
                                        className={
                                            'border-b border-border last:border-b-0 ' +
                                            (!n.leida ? 'bg-primary/5' : '')
                                        }
                                    >
                                        <button
                                            type="button"
                                            onClick={() => irA(n.id)}
                                            className="flex w-full items-start gap-2.5 px-3 py-2.5 text-left transition-colors hover:bg-muted/40"
                                        >
                                            <div
                                                className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full"
                                                style={{
                                                    backgroundColor: `${n.color}1a`,
                                                    color: n.color,
                                                }}
                                            >
                                                <Icono className="size-4" />
                                            </div>
                                            <div className="min-w-0 flex-1 space-y-0.5">
                                                <div className="flex items-center justify-between gap-2">
                                                    <span className="truncate text-sm font-medium">
                                                        {n.titulo}
                                                    </span>
                                                    {!n.leida && (
                                                        <span className="size-1.5 shrink-0 rounded-full bg-primary" />
                                                    )}
                                                </div>
                                                {n.mensaje && (
                                                    <div className="line-clamp-2 text-xs text-muted-foreground">
                                                        {n.mensaje}
                                                    </div>
                                                )}
                                                <div className="flex items-center gap-2 text-[10px] text-muted-foreground">
                                                    {n.obra_codigo && (
                                                        <span className="font-mono text-primary">
                                                            {n.obra_codigo}
                                                        </span>
                                                    )}
                                                    <span>{n.created_at_relativo}</span>
                                                </div>
                                            </div>
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>

                <div className="border-t border-border p-2">
                    <Link
                        href="/notificaciones"
                        className="block rounded-md px-3 py-1.5 text-center text-xs font-medium text-primary hover:bg-muted"
                    >
                        Ver todas las notificaciones
                    </Link>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
