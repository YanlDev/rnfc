import { Head, Link, router } from '@inertiajs/react';
import {
    Building2,
    Calendar,
    CalendarDays,
    DollarSign,
    FolderTree,
    MapPin,
    NotebookPen,
    Plus,
    Search,
    Trash2,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import obras from '@/routes/obras';

type Obra = {
    id: number;
    codigo: string;
    nombre: string;
    ubicacion: string | null;
    entidad_contratante: string | null;
    monto_contractual: string | number | null;
    fecha_inicio: string | null;
    fecha_fin_prevista: string | null;
    estado: string;
    estado_label: string;
    creador: string | null;
};

type EstadoOpcion = { value: string; label: string };

type Paginado<T> = {
    data: T[];
    links?: { url: string | null; label: string; active: boolean }[];
    meta?: { current_page: number; last_page: number; total: number; from?: number; to?: number };
};

type Props = {
    obras: Paginado<Obra>;
    filtros: { q?: string; estado?: string };
    estados: EstadoOpcion[];
};

const ESTADO_BADGE: Record<string, string> = {
    planificacion: 'bg-slate-200 text-slate-800 hover:bg-slate-200',
    en_ejecucion: 'bg-[var(--color-brand-verde)] text-white hover:bg-[var(--color-brand-verde)]',
    paralizada: 'bg-[var(--color-brand-amarillo)] text-[#3e4142] hover:bg-[var(--color-brand-amarillo)]',
    finalizada: 'bg-[var(--color-brand-azul-oscuro)] text-white hover:bg-[var(--color-brand-azul-oscuro)]',
    archivada: 'bg-slate-500 text-white hover:bg-slate-500',
};

const ESTADO_ACENTO: Record<string, string> = {
    planificacion: 'bg-slate-300',
    en_ejecucion: 'bg-[var(--color-brand-verde)]',
    paralizada: 'bg-[var(--color-brand-amarillo)]',
    finalizada: 'bg-[var(--color-brand-azul-oscuro)]',
    archivada: 'bg-slate-500',
};

function formatearMonto(monto: string | number | null): string {
    if (monto === null || monto === undefined || monto === '') return '—';
    const n = typeof monto === 'string' ? parseFloat(monto) : monto;
    if (Number.isNaN(n)) return '—';
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        maximumFractionDigits: 2,
    }).format(n);
}

function ObraCard({ obra, onEliminar }: { obra: Obra; onEliminar: () => void }) {
    return (
        <Card className="group relative flex flex-col overflow-hidden p-0 transition-all hover:-translate-y-0.5 hover:shadow-xl">
            {/* Banda lateral de estado */}
            <span
                aria-hidden
                className={`absolute inset-y-0 left-0 w-1.5 ${ESTADO_ACENTO[obra.estado] ?? 'bg-slate-300'}`}
            />

            <CardContent className="flex flex-1 flex-col gap-5 p-7 pl-9">
                {/* Encabezado: código + estado */}
                <div className="flex items-center justify-between gap-2">
                    <Link
                        href={obras.show(obra.id).url}
                        className="font-mono text-[11px] font-semibold tracking-[0.18em] text-muted-foreground uppercase hover:text-primary"
                    >
                        {obra.codigo}
                    </Link>
                    <Badge className={`${ESTADO_BADGE[obra.estado] ?? ''} px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase`}>
                        {obra.estado_label}
                    </Badge>
                </div>

                {/* Nombre de la obra */}
                <Link href={obras.show(obra.id).url} className="block">
                    <h3 className="font-display line-clamp-2 text-xl leading-tight font-bold text-foreground transition-colors group-hover:text-primary">
                        {obra.nombre}
                    </h3>
                </Link>

                {/* Info principal */}
                <div className="flex flex-1 flex-col gap-2.5 text-sm">
                    {obra.entidad_contratante && (
                        <div className="flex items-start gap-2.5">
                            <Building2 className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                            <span className="line-clamp-2 text-foreground/80">
                                {obra.entidad_contratante}
                            </span>
                        </div>
                    )}
                    {obra.ubicacion && (
                        <div className="flex items-start gap-2.5">
                            <MapPin className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                            <span className="line-clamp-2 text-foreground/80">
                                {obra.ubicacion}
                            </span>
                        </div>
                    )}
                    {(obra.fecha_inicio || obra.fecha_fin_prevista) && (
                        <div className="flex items-start gap-2.5">
                            <Calendar className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                            <span className="tabular-nums text-foreground/80">
                                {obra.fecha_inicio ?? '—'}
                                {obra.fecha_fin_prevista && (
                                    <> → {obra.fecha_fin_prevista}</>
                                )}
                            </span>
                        </div>
                    )}
                </div>

                {/* Monto destacado */}
                <div className="rounded-lg bg-muted/50 px-4 py-3">
                    <div className="text-[10px] font-bold tracking-[0.18em] text-muted-foreground uppercase">
                        Monto contractual
                    </div>
                    <div className="font-display mt-1 flex items-center gap-1 text-xl font-bold text-foreground tabular-nums">
                        <DollarSign className="size-4 text-muted-foreground" />
                        {formatearMonto(obra.monto_contractual)}
                    </div>
                </div>

                {/* Acciones */}
                <div className="flex items-center justify-between gap-1 border-t border-border pt-3">
                    <div className="flex items-center gap-0.5">
                        <Button
                            asChild
                            size="sm"
                            variant="ghost"
                            title="Documentos"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <Link href={`/obras/${obra.id}/documentos`}>
                                <FolderTree className="size-4 text-primary" />
                            </Link>
                        </Button>
                        <Button
                            asChild
                            size="sm"
                            variant="ghost"
                            title="Cuaderno de obra"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <Link href={`/obras/${obra.id}/cuaderno`}>
                                <NotebookPen className="size-4 text-primary" />
                            </Link>
                        </Button>
                        <Button
                            asChild
                            size="sm"
                            variant="ghost"
                            title="Calendario"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <Link href={`/obras/${obra.id}/calendario`}>
                                <CalendarDays className="size-4 text-primary" />
                            </Link>
                        </Button>
                    </div>
                    <Button
                        size="sm"
                        variant="ghost"
                        onClick={onEliminar}
                        title="Eliminar"
                    >
                        <Trash2 className="size-4 text-destructive" />
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}

export default function ObrasIndex({ obras: paginado, filtros, estados }: Props) {
    const [busqueda, setBusqueda] = useState(filtros.q ?? '');
    const [estado, setEstado] = useState(filtros.estado ?? 'todos');

    useEffect(() => {
        const t = setTimeout(() => {
            router.get(
                obras.index().url,
                {
                    q: busqueda || undefined,
                    estado: estado === 'todos' ? undefined : estado,
                },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 250);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [busqueda, estado]);

    const eliminar = (obra: Obra) => {
        const ok = confirm(
            `¿Eliminar definitivamente la obra ${obra.codigo}?\n\n` +
                'Esta acción también elimina todos los certificados asociados y no se puede deshacer.',
        );
        if (!ok) return;
        router.delete(obras.destroy(obra.id).url);
    };

    return (
        <>
            <Head title="Obras" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                        <div className="relative flex-1 sm:max-w-md">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                placeholder="Buscar por código, nombre o entidad…"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                className="pl-9"
                            />
                        </div>
                        <Select value={estado} onValueChange={setEstado}>
                            <SelectTrigger className="sm:w-56">
                                <SelectValue placeholder="Filtrar por estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="todos">Todos los estados</SelectItem>
                                {estados.map((e) => (
                                    <SelectItem key={e.value} value={e.value}>
                                        {e.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <Button asChild>
                        <Link href={obras.create().url}>
                            <Plus className="size-4" />
                            Nueva obra
                        </Link>
                    </Button>
                </div>

                {paginado.data.length === 0 ? (
                    <Card className="p-12 text-center">
                        <Building2 className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No hay obras que coincidan con los filtros.
                        </p>
                    </Card>
                ) : (
                    <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        {paginado.data.map((o) => (
                            <ObraCard
                                key={o.id}
                                obra={o}
                                onEliminar={() => eliminar(o)}
                            />
                        ))}
                    </div>
                )}

                {paginado.meta && paginado.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <div>
                            {paginado.meta.from ?? 0}–{paginado.meta.to ?? 0} de{' '}
                            {paginado.meta.total}
                        </div>
                        <div className="flex gap-1">
                            {paginado.links?.map((l, i) => (
                                <Button
                                    key={i}
                                    size="sm"
                                    variant={l.active ? 'default' : 'outline'}
                                    disabled={!l.url}
                                    onClick={() =>
                                        l.url && router.visit(l.url, { preserveScroll: true })
                                    }
                                >
                                    <span dangerouslySetInnerHTML={{ __html: l.label }} />
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

ObrasIndex.layout = {
    title: 'Obras',
    description: 'Registra y administra las obras supervisadas por RNFC.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
    ],
};
