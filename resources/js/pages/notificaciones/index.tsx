import { Head, router } from '@inertiajs/react';
import {
    Award,
    Bell,
    Building2,
    CalendarClock,
    CheckCheck,
    FolderTree,
    NotebookPen,
    Trash2,
    UserPlus,
    XCircle,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

type Notificacion = {
    id: string;
    tipo: string;
    titulo: string;
    mensaje: string;
    obra_codigo: string | null;
    obra_nombre: string | null;
    url: string | null;
    icono: string;
    color: string;
    leida: boolean;
    created_at: string;
    created_at_relativo: string;
};

type Props = {
    notificaciones: Notificacion[];
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

type Filtro = 'todas' | 'no_leidas';

export default function NotificacionesIndex({ notificaciones }: Props) {
    const [filtro, setFiltro] = useState<Filtro>('todas');

    const filtradas = useMemo(() => {
        if (filtro === 'no_leidas') {
            return notificaciones.filter((n) => !n.leida);
        }
        return notificaciones;
    }, [notificaciones, filtro]);

    const totalNoLeidas = notificaciones.filter((n) => !n.leida).length;

    const irA = (id: string) => {
        router.post(`/notificaciones/${id}/leida`);
    };

    const marcarTodas = () => {
        router.post('/notificaciones/marcar-todas', {}, { preserveScroll: true });
    };

    const eliminar = (id: string, e: React.MouseEvent) => {
        e.stopPropagation();
        if (!confirm('¿Eliminar esta notificación?')) return;
        router.delete(`/notificaciones/${id}`, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Notificaciones" />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="inline-flex overflow-hidden rounded-md border border-border">
                        <button
                            type="button"
                            onClick={() => setFiltro('todas')}
                            className={
                                'px-3 py-1.5 text-sm transition-colors ' +
                                (filtro === 'todas'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted')
                            }
                        >
                            Todas ({notificaciones.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => setFiltro('no_leidas')}
                            className={
                                'border-l border-border px-3 py-1.5 text-sm transition-colors ' +
                                (filtro === 'no_leidas'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted')
                            }
                        >
                            No leídas ({totalNoLeidas})
                        </button>
                    </div>
                    {totalNoLeidas > 0 && (
                        <Button variant="outline" size="sm" onClick={marcarTodas}>
                            <CheckCheck className="size-4" />
                            Marcar todas como leídas
                        </Button>
                    )}
                </div>

                {filtradas.length === 0 ? (
                    <Card className="p-12 text-center">
                        <Bell className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <h3 className="mb-1 text-base font-semibold">
                            {filtro === 'no_leidas'
                                ? 'No tienes notificaciones sin leer'
                                : 'No tienes notificaciones todavía'}
                        </h3>
                        <p className="text-sm text-muted-foreground">
                            Cuando se sube un documento, se registra un asiento o
                            vence un evento del calendario en alguna de tus obras,
                            aparecerá aquí.
                        </p>
                    </Card>
                ) : (
                    <ul className="space-y-2">
                        {filtradas.map((n) => {
                            const Icono = ICONOS[n.icono] ?? Bell;
                            return (
                                <li key={n.id}>
                                    <Card
                                        className={
                                            'cursor-pointer transition-shadow hover:shadow-md ' +
                                            (!n.leida ? 'border-primary/40 bg-primary/5' : '')
                                        }
                                        onClick={() => irA(n.id)}
                                    >
                                        <CardContent className="flex items-start gap-3 p-4">
                                            <div
                                                className="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full"
                                                style={{
                                                    backgroundColor: `${n.color}1a`,
                                                    color: n.color,
                                                }}
                                            >
                                                <Icono className="size-5" />
                                            </div>
                                            <div className="min-w-0 flex-1 space-y-1">
                                                <div className="flex items-center justify-between gap-2">
                                                    <h3 className="truncate text-sm font-semibold">
                                                        {n.titulo}
                                                    </h3>
                                                    <div className="flex shrink-0 items-center gap-2">
                                                        {!n.leida && (
                                                            <Badge className="bg-primary text-primary-foreground text-[10px]">
                                                                Nueva
                                                            </Badge>
                                                        )}
                                                        <span className="text-xs text-muted-foreground">
                                                            {n.created_at_relativo}
                                                        </span>
                                                    </div>
                                                </div>
                                                {n.mensaje && (
                                                    <p className="line-clamp-2 text-sm text-muted-foreground">
                                                        {n.mensaje}
                                                    </p>
                                                )}
                                                <div className="flex items-center justify-between gap-2 pt-1 text-xs">
                                                    {n.obra_codigo && (
                                                        <span className="text-muted-foreground">
                                                            <span className="font-mono text-primary">
                                                                {n.obra_codigo}
                                                            </span>{' '}
                                                            {n.obra_nombre && (
                                                                <span>· {n.obra_nombre}</span>
                                                            )}
                                                        </span>
                                                    )}
                                                    <button
                                                        type="button"
                                                        onClick={(e) => eliminar(n.id, e)}
                                                        className="ml-auto rounded p-1 text-muted-foreground hover:bg-muted hover:text-destructive"
                                                        title="Eliminar"
                                                    >
                                                        <Trash2 className="size-3.5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </div>
        </>
    );
}

NotificacionesIndex.layout = {
    title: 'Notificaciones',
    description: 'Avisos de tu actividad en obras: documentos, asientos, vencimientos y más.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Notificaciones', href: '/notificaciones' },
    ],
};
