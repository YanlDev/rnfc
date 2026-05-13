import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, Building2, CalendarClock, X } from 'lucide-react';
import { useState } from 'react';
import CalendarioMes, {
    type EventoCalendarioBase,
} from '@/components/calendario-mes';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Evento = EventoCalendarioBase & {
    tipo: string;
    descripcion: string | null;
    todo_el_dia: boolean;
};

type Obra = { id: number; codigo: string; nombre: string };

type Props = {
    eventos: Evento[];
    obras: Obra[];
    proximos: Evento[];
    vencidos: Evento[];
};

function ListaItem({ ev, onClick }: { ev: Evento; onClick: () => void }) {
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
                    {ev.obra && ` · ${ev.obra.codigo}`}
                </div>
            </div>
        </button>
    );
}

export default function CalendarioGlobal({
    eventos,
    obras,
    proximos,
    vencidos,
}: Props) {
    const [seleccionado, setSeleccionado] = useState<Evento | null>(null);

    if (obras.length === 0) {
        return (
            <>
                <Head title="Calendario" />
                <div className="flex flex-1 flex-col items-center justify-center gap-4 p-8 text-center">
                    <CalendarClock className="size-12 text-muted-foreground" />
                    <h2 className="text-xl font-semibold">
                        Sin obras todavía
                    </h2>
                    <p className="max-w-md text-sm text-muted-foreground">
                        El calendario se llena con los eventos de cada obra
                        (hitos, vencimientos, reuniones, inspecciones).
                    </p>
                    <Button asChild>
                        <Link href="/obras/create">
                            <Building2 className="size-4" />
                            Crear primera obra
                        </Link>
                    </Button>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Calendario" />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="grid gap-4 lg:grid-cols-[1fr_280px]">
                    <Card className="p-4">
                        <CalendarioMes
                            eventos={eventos}
                            onSeleccionar={setSeleccionado}
                        />
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
                                        <ListaItem
                                            key={ev.id}
                                            ev={ev}
                                            onClick={() => setSeleccionado(ev)}
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
                                        <ListaItem
                                            key={ev.id}
                                            ev={ev}
                                            onClick={() => setSeleccionado(ev)}
                                        />
                                    ))}
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>

            {seleccionado && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
                    onClick={() => setSeleccionado(null)}
                >
                    <Card
                        className="w-full max-w-md"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <CardHeader className="flex flex-row items-start justify-between gap-2 space-y-0">
                            <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                    <span
                                        className="inline-block size-2.5 rounded-full"
                                        style={{
                                            backgroundColor: seleccionado.color,
                                        }}
                                    />
                                    <Badge variant="secondary">
                                        {seleccionado.tipo_label}
                                    </Badge>
                                    {seleccionado.vencido && (
                                        <Badge variant="destructive">
                                            Vencido
                                        </Badge>
                                    )}
                                </div>
                                <CardTitle className="text-base">
                                    {seleccionado.titulo}
                                </CardTitle>
                            </div>
                            <button
                                onClick={() => setSeleccionado(null)}
                                className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                            >
                                <X className="size-4" />
                            </button>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div>
                                <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    Fecha
                                </div>
                                {seleccionado.fecha_inicio_iso}
                                {seleccionado.fecha_fin_iso &&
                                    seleccionado.fecha_fin_iso !==
                                        seleccionado.fecha_inicio_iso &&
                                    ` → ${seleccionado.fecha_fin_iso}`}
                            </div>
                            {seleccionado.obra && (
                                <div>
                                    <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        Obra
                                    </div>
                                    <Link
                                        href={`/obras/${seleccionado.obra.codigo ? '' : ''}`}
                                        className="text-primary hover:underline"
                                    >
                                        <span className="font-mono text-xs">
                                            {seleccionado.obra.codigo}
                                        </span>{' '}
                                        {seleccionado.obra.nombre}
                                    </Link>
                                </div>
                            )}
                            {seleccionado.descripcion && (
                                <div>
                                    <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        Descripción
                                    </div>
                                    <p className="whitespace-pre-line">
                                        {seleccionado.descripcion}
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            )}
        </>
    );
}

CalendarioGlobal.layout = {
    title: 'Calendario',
    description:
        'Hitos, vencimientos y eventos de todas las obras. Los vencidos se detectan automáticamente.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Calendario', href: '/calendario' },
    ],
};
