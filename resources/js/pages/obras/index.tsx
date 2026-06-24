import { Head, Link } from '@inertiajs/react';
import {
    Building2,
    Calendar,
    FolderTree,
    MapPin,
    NotebookPen,
    Plus,
    Search,
} from 'lucide-react';
import { EstadoObraBadge } from '@/components/estado-obra-badge';
import Paginacion from '@/components/paginacion';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import obras from '@/routes/obras';
import type { Paginado } from '@/types';

type Obra = {
    id: number;
    codigo: string;
    nombre: string;
    ubicacion: string | null;
    entidad_contratante: string | null;
    fecha_inicio: string | null;
    fecha_fin_prevista: string | null;
    estado: string;
    estado_label: string;
    imagen_url: string | null;
    documentos_count: number;
    cuaderno_count: number;
};

type EstadoOpcion = { value: string; label: string };

type Props = {
    obras: Paginado<Obra>;
    filtros: { q?: string; estado?: string };
    estados: EstadoOpcion[];
};

const MESES = [
    'ene',
    'feb',
    'mar',
    'abr',
    'may',
    'jun',
    'jul',
    'ago',
    'sep',
    'oct',
    'nov',
    'dic',
];

function formatearFecha(iso: string | null): string | null {
    if (!iso) {
        return null;
    }

    const [y, m, d] = iso.split('-').map(Number);

    if (!y || !m || !d) {
        return iso;
    }

    return `${d} ${MESES[m - 1]} ${y}`;
}

function ChipMetrica({
    icono: Icono,
    valor,
    etiqueta,
}: {
    icono: typeof FolderTree;
    valor: number;
    etiqueta: string;
}) {
    return (
        <div className="flex items-center gap-2 rounded-lg bg-black/55 px-2.5 py-1.5 text-white backdrop-blur-sm">
            <Icono className="size-4 shrink-0" />
            <div className="leading-none">
                <div className="text-sm font-bold tabular-nums">{valor}</div>
                <div className="mt-0.5 text-[10px] text-white/80">
                    {etiqueta}
                </div>
            </div>
        </div>
    );
}

function ObraCard({ obra }: { obra: Obra }) {
    const inicio = formatearFecha(obra.fecha_inicio);
    const fin = formatearFecha(obra.fecha_fin_prevista);

    return (
        <Link
            href={obras.show(obra.id).url}
            className="group block overflow-hidden rounded-xl border border-border bg-card transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5"
        >
            {/* Imagen con overlays */}
            <div className="relative h-44 w-full overflow-hidden">
                {obra.imagen_url ? (
                    <img
                        src={obra.imagen_url}
                        alt={obra.nombre}
                        className="size-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                    />
                ) : (
                    <div className="flex size-full items-center justify-center bg-muted">
                        <Building2 className="size-10 text-muted-foreground/40" />
                    </div>
                )}

                <span className="absolute top-3 left-3 rounded-md bg-white/90 px-2 py-1 font-mono text-[11px] font-semibold tracking-[0.12em] text-neutral-900 uppercase shadow-sm backdrop-blur">
                    {obra.codigo}
                </span>
                <EstadoObraBadge
                    estado={obra.estado}
                    label={obra.estado_label}
                    className="absolute top-3 right-3 shadow-sm"
                />

                <div className="absolute bottom-3 left-3 flex gap-2">
                    <ChipMetrica
                        icono={FolderTree}
                        valor={obra.documentos_count}
                        etiqueta="Docs"
                    />
                    <ChipMetrica
                        icono={NotebookPen}
                        valor={obra.cuaderno_count}
                        etiqueta="Cuaderno"
                    />
                </div>
            </div>

            {/* Contenido */}
            <div className="space-y-3 p-5">
                <h3 className="line-clamp-2 text-lg leading-snug font-bold text-foreground transition-colors group-hover:text-primary">
                    {obra.nombre}
                </h3>

                <div className="space-y-2 text-sm text-muted-foreground">
                    {obra.entidad_contratante && (
                        <div className="flex items-center gap-2">
                            <Building2 className="size-4 shrink-0" />
                            <span className="truncate">
                                {obra.entidad_contratante}
                            </span>
                        </div>
                    )}
                    {obra.ubicacion && (
                        <div className="flex items-center gap-2">
                            <MapPin className="size-4 shrink-0" />
                            <span className="truncate">{obra.ubicacion}</span>
                        </div>
                    )}
                    {(inicio || fin) && (
                        <div className="flex items-center gap-2">
                            <Calendar className="size-4 shrink-0" />
                            <span className="tabular-nums">
                                {inicio ?? '—'}
                                {fin && ` – ${fin}`}
                            </span>
                        </div>
                    )}
                </div>
            </div>
        </Link>
    );
}

export default function ObrasIndex({ obras: paginado, filtros, estados }: Props) {
    const { filtros: f, set } = useFiltrosUrl(obras.index().url, {
        q: filtros.q ?? '',
        estado: filtros.estado ?? 'todos',
    });

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
                                value={f.q}
                                onChange={(e) => set('q', e.target.value)}
                                className="pl-9"
                            />
                        </div>
                        <Select
                            value={f.estado}
                            onValueChange={(v) => set('estado', v)}
                        >
                            <SelectTrigger className="sm:w-56">
                                <SelectValue placeholder="Filtrar por estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="todos">
                                    Todos los estados
                                </SelectItem>
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
                            <ObraCard key={o.id} obra={o} />
                        ))}
                    </div>
                )}

                <Paginacion meta={paginado.meta} links={paginado.links} />
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
