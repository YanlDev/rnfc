import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, ArrowLeft, CalendarClock, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import CalendarioMes from '@/components/calendario-mes';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import obras from '@/routes/obras';
import DialogoEvento, {
    type EventoEditable,
    type TipoEventoOpcion,
} from './_dialogo-evento';

type Evento = {
    id: number;
    tipo: string;
    tipo_label: string;
    color: string;
    titulo: string;
    descripcion: string | null;
    fecha_inicio: string;
    fecha_fin: string | null;
    fecha_inicio_iso: string | null;
    fecha_fin_iso: string | null;
    todo_el_dia: boolean;
    vencido: boolean;
    creador: string | null;
};

type Props = {
    obra: { id: number; codigo: string; nombre: string };
    eventos: Evento[];
    tipos: TipoEventoOpcion[];
    puedeEditar: boolean;
};

function ItemListado({
    ev,
    onClick,
}: {
    ev: Evento;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="flex w-full gap-2 rounded-md border border-border p-2 text-left hover:bg-muted/30"
        >
            <span
                className="mt-1 inline-block size-2 shrink-0 rounded-full"
                style={{ backgroundColor: ev.color }}
            />
            <div className="min-w-0 flex-1">
                <div className="truncate text-sm font-medium">{ev.titulo}</div>
                <div className="text-[10px] text-muted-foreground">
                    {ev.fecha_inicio_iso}
                    {ev.fecha_fin_iso &&
                        ev.fecha_fin_iso !== ev.fecha_inicio_iso &&
                        ` → ${ev.fecha_fin_iso}`}
                </div>
            </div>
        </button>
    );
}

export default function CalendarioPorObra({
    obra,
    eventos,
    tipos,
    puedeEditar,
}: Props) {
    const [dialogoAbierto, setDialogoAbierto] = useState(false);
    const [eventoEnEdicion, setEventoEnEdicion] = useState<EventoEditable | null>(null);
    const [fechaPrellenada, setFechaPrellenada] = useState<string | null>(null);

    const hoyIso = new Date().toISOString().slice(0, 10);

    const proximos = useMemo(() => {
        const inicio = new Date();
        const fin = new Date();
        fin.setDate(fin.getDate() + 14);
        return eventos
            .filter((ev) => {
                if (!ev.fecha_inicio_iso) return false;
                const f = new Date(ev.fecha_inicio_iso + 'T00:00:00');
                return f >= inicio && f <= fin;
            })
            .slice(0, 10);
    }, [eventos]);

    const vencidos = useMemo(
        () =>
            eventos.filter((ev) => ev.vencido && ev.tipo === 'vencimiento').slice(0, 10),
        [eventos],
    );

    const abrirNuevo = (fecha: string | null = null) => {
        setEventoEnEdicion(null);
        setFechaPrellenada(fecha);
        setDialogoAbierto(true);
    };

    const abrirEdicion = (ev: Evento) => {
        setEventoEnEdicion({
            id: ev.id,
            tipo: ev.tipo,
            titulo: ev.titulo,
            descripcion: ev.descripcion,
            fecha_inicio_iso: ev.fecha_inicio_iso,
            fecha_fin_iso: ev.fecha_fin_iso,
            todo_el_dia: ev.todo_el_dia,
        });
        setFechaPrellenada(null);
        setDialogoAbierto(true);
    };

    return (
        <>
            <Head title={`Calendario · ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={obras.show(obra.id).url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver a la obra
                    </Link>
                    {puedeEditar && (
                        <Button onClick={() => abrirNuevo(hoyIso)}>
                            <Plus className="size-4" />
                            Nuevo evento
                        </Button>
                    )}
                </div>

                <div className="grid gap-4 lg:grid-cols-[1fr_280px]">
                    <Card className="p-4">
                        <CalendarioMes
                            eventos={eventos}
                            onSeleccionar={(ev) =>
                                puedeEditar ? abrirEdicion(ev as Evento) : undefined
                            }
                            onClickDia={(iso) =>
                                puedeEditar ? abrirNuevo(iso) : undefined
                            }
                        />
                        <div className="mt-4 flex flex-wrap gap-2 border-t border-border pt-3">
                            {tipos.map((t) => (
                                <span
                                    key={t.value}
                                    className="flex items-center gap-1.5 text-[11px] text-muted-foreground"
                                >
                                    <span
                                        className="inline-block size-2.5 rounded-full"
                                        style={{ backgroundColor: t.color }}
                                    />
                                    {t.label}
                                </span>
                            ))}
                        </div>
                    </Card>

                    <div className="space-y-4">
                        <Card>
                            <CardHeader className="pb-3">
                                <CardTitle className="flex items-center gap-2 text-sm">
                                    <CalendarClock className="size-4 text-primary" />
                                    Próximos 14 días
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-1.5 p-3 pt-0">
                                {proximos.length === 0 ? (
                                    <p className="py-3 text-center text-xs text-muted-foreground">
                                        Sin eventos próximos.
                                    </p>
                                ) : (
                                    proximos.map((ev) => (
                                        <ItemListado
                                            key={ev.id}
                                            ev={ev}
                                            onClick={() => abrirEdicion(ev)}
                                        />
                                    ))
                                )}
                            </CardContent>
                        </Card>

                        {vencidos.length > 0 && (
                            <Card className="border-destructive/40">
                                <CardHeader className="pb-3">
                                    <CardTitle className="flex items-center gap-2 text-sm text-destructive">
                                        <AlertTriangle className="size-4" />
                                        Vencimientos pasados
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-1.5 p-3 pt-0">
                                    {vencidos.map((ev) => (
                                        <ItemListado
                                            key={ev.id}
                                            ev={ev}
                                            onClick={() => abrirEdicion(ev)}
                                        />
                                    ))}
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardContent className="p-3 text-xs text-muted-foreground">
                                <Badge variant="secondary" className="mb-1">
                                    {eventos.length}
                                </Badge>{' '}
                                evento(s) registrado(s) en total.
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <DialogoEvento
                open={dialogoAbierto}
                onOpenChange={setDialogoAbierto}
                obraId={obra.id}
                tipos={tipos}
                evento={eventoEnEdicion}
                fechaInicial={fechaPrellenada}
            />
        </>
    );
}

CalendarioPorObra.layout = {
    title: 'Calendario de obra',
    description: 'Hitos, vencimientos, reuniones, inspecciones y paralizaciones.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Calendario', href: '' },
    ],
};
