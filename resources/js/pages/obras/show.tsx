import { Head, Link, router } from '@inertiajs/react';
import {
    Building2,
    CalendarDays,
    CalendarRange,
    ChevronRight,
    DollarSign,
    FolderTree,
    ImagePlus,
    MapPin,
    NotebookPen,
    Pencil,
    Trash2,
    User,
    Users,
    Wallet,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { useConfirm } from '@/components/confirm-dialog';
import { EstadoObraBadge } from '@/components/estado-obra-badge';
import MapaUbicacion from '@/components/mapa-ubicacion';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import obras from '@/routes/obras';

type ObraData = {
    id: number;
    codigo: string;
    nombre: string;
    ubicacion: string | null;
    latitud: number | null;
    longitud: number | null;
    imagen_url: string | null;
    entidad_contratante: string | null;
    monto_contractual: number | null;
    fecha_inicio: string | null;
    fecha_fin_prevista: string | null;
    fecha_fin_real: string | null;
    estado: string;
    estado_label: string;
    creador: string | null;
};

type Contadores = {
    documentos: number;
    cuaderno: number;
    calendario: number;
    equipo: number;
    caja: number;
};

type ShowProps = {
    obra: ObraData;
    contadores: Contadores;
    puedeAdministrar: boolean;
    puedeVerCaja: boolean;
};

function formatearMonto(monto: number | null): string {
    if (monto === null) {
        return '—';
    }

    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        maximumFractionDigits: 2,
    }).format(monto);
}

/** Progreso 0-100 según los días transcurridos entre inicio y fin previsto. */
function progresoDias(
    inicio: string | null,
    fin: string | null,
): number | null {
    if (!inicio || !fin) {
        return null;
    }

    const i = new Date(inicio).getTime();
    const f = new Date(fin).getTime();

    if (!Number.isFinite(i) || !Number.isFinite(f) || f <= i) {
        return null;
    }

    const pct = ((Date.now() - i) / (f - i)) * 100;

    return Math.round(Math.min(100, Math.max(0, pct)));
}

function Dato({
    label,
    valor,
    icono: Icono,
}: {
    label: string;
    valor: React.ReactNode;
    icono: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="flex items-start gap-3">
            <span className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <Icono className="size-4" />
            </span>
            <div className="min-w-0">
                <div className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </div>
                <div className="truncate text-sm font-semibold text-foreground">
                    {valor}
                </div>
            </div>
        </div>
    );
}

function ImagenProyecto({
    obraId,
    imagenUrl,
    nombre,
    puedeEditar,
}: {
    obraId: number;
    imagenUrl: string | null;
    nombre: string;
    puedeEditar: boolean;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [subiendo, setSubiendo] = useState(false);
    const { confirm, dialog } = useConfirm();

    const eliminar = async () => {
        const ok = await confirm({
            titulo: '¿Quitar la imagen del proyecto?',
            destructivo: true,
            confirmar: 'Quitar imagen',
        });

        if (!ok) {
            return;
        }

        router.delete(`/obras/${obraId}/imagen`, { preserveScroll: true });
    };

    const seleccionar = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];

        if (!file) {
            return;
        }

        router.post(
            `/obras/${obraId}/imagen`,
            { imagen: file },
            {
                forceFormData: true,
                preserveScroll: true,
                onStart: () => setSubiendo(true),
                onFinish: () => {
                    setSubiendo(false);

                    if (inputRef.current) {
                        inputRef.current.value = '';
                    }
                },
            },
        );
    };

    return (
        <div className="group relative h-56 overflow-hidden rounded-lg">
            {dialog}
            {imagenUrl ? (
                <img
                    src={imagenUrl}
                    alt={nombre}
                    className="size-full object-cover"
                />
            ) : (
                <div className="flex size-full flex-col items-center justify-center gap-2 bg-muted/40 text-muted-foreground">
                    <ImagePlus className="size-9 text-muted-foreground/50" />
                    <span className="text-xs">Sin imagen del proyecto</span>
                </div>
            )}

            {puedeEditar && (
                <>
                    {imagenUrl ? (
                        <div className="absolute inset-0 flex items-end justify-end gap-2 p-3 transition-opacity pointer-fine:items-center pointer-fine:justify-center pointer-fine:bg-black/45 pointer-fine:p-0 pointer-fine:opacity-0 pointer-fine:backdrop-blur-[1px] pointer-fine:group-hover:opacity-100">
                            <button
                                type="button"
                                onClick={() => inputRef.current?.click()}
                                disabled={subiendo}
                                className="inline-flex items-center gap-1.5 rounded-md bg-white/90 px-2.5 py-1.5 text-xs font-medium text-neutral-900 hover:bg-white"
                            >
                                <ImagePlus className="size-3.5" />
                                {subiendo ? 'Subiendo…' : 'Cambiar'}
                            </button>
                            <button
                                type="button"
                                onClick={eliminar}
                                className="inline-flex items-center gap-1.5 rounded-md bg-white/90 px-2.5 py-1.5 text-xs font-medium text-destructive hover:bg-white"
                            >
                                <Trash2 className="size-3.5" />
                                Eliminar
                            </button>
                        </div>
                    ) : (
                        <button
                            type="button"
                            onClick={() => inputRef.current?.click()}
                            disabled={subiendo}
                            className="absolute inset-0 flex items-center justify-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/60"
                        >
                            <ImagePlus className="size-4" />
                            {subiendo ? 'Subiendo…' : 'Subir imagen'}
                        </button>
                    )}
                    <input
                        ref={inputRef}
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        className="hidden"
                        onChange={seleccionar}
                    />
                </>
            )}
        </div>
    );
}

function AccesoDirecto({
    href,
    icono: Icono,
    label,
    valor,
    unidad,
}: {
    href: string;
    icono: React.ComponentType<{ className?: string }>;
    label: string;
    valor: number;
    unidad: string;
}) {
    return (
        <Link
            href={href}
            className="group flex items-center gap-4 rounded-xl border border-border bg-card p-4 transition-all hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-md"
        >
            <span className="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <Icono className="size-5" />
            </span>
            <div className="min-w-0 flex-1">
                <div className="font-semibold text-foreground">{label}</div>
                <div className="text-sm text-muted-foreground">
                    <span className="font-semibold text-foreground tabular-nums">
                        {valor}
                    </span>{' '}
                    {unidad}
                </div>
            </div>
            <ChevronRight className="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
        </Link>
    );
}

export default function ObraShow({
    obra,
    contadores,
    puedeAdministrar,
    puedeVerCaja,
}: ShowProps) {
    const eliminar = () => {
        const ok = confirm(
            `¿Eliminar definitivamente la obra ${obra.codigo}?\n\n` +
                'Esta acción también elimina todos los certificados asociados y no se puede deshacer.',
        );

        if (!ok) {
            return;
        }

        router.delete(obras.destroy(obra.id).url);
    };

    const progreso = progresoDias(obra.fecha_inicio, obra.fecha_fin_prevista);

    return (
        <>
            <Head title={`${obra.codigo} — ${obra.nombre}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header de página */}
                <div className="flex items-start justify-between gap-4">
                    <div className="min-w-0 flex-1">
                        <h1
                            className="truncate text-lg font-semibold text-foreground"
                            title={obra.nombre}
                        >
                            {obra.nombre}
                        </h1>
                        <div className="mt-1 flex items-center gap-3 text-sm text-muted-foreground">
                            <span className="inline-flex min-w-0 items-center gap-1.5">
                                <MapPin className="size-3.5 shrink-0" />
                                <span className="truncate">
                                    {obra.ubicacion ?? 'Sin ubicación'}
                                </span>
                            </span>
                            <EstadoObraBadge
                                estado={obra.estado}
                                label={obra.estado_label}
                            />
                        </div>
                    </div>
                    {puedeAdministrar && (
                        <div className="flex shrink-0 items-center gap-1">
                            <Button
                                asChild
                                variant="outline"
                                size="icon"
                                title="Editar obra"
                            >
                                <Link href={obras.edit(obra.id).url}>
                                    <Pencil className="size-4" />
                                </Link>
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={eliminar}
                                title="Eliminar obra"
                            >
                                <Trash2 className="size-4 text-destructive" />
                            </Button>
                        </div>
                    )}
                </div>

                {/* Hero: imagen · datos · mapa  (progreso a todo el ancho debajo) */}
                <Card className="p-5">
                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        <ImagenProyecto
                            obraId={obra.id}
                            imagenUrl={obra.imagen_url}
                            nombre={obra.nombre}
                            puedeEditar={puedeAdministrar}
                        />

                        {/* Datos mínimos */}
                        <div className="flex flex-col justify-center gap-5">
                            <Dato
                                label="Entidad contratante"
                                valor={obra.entidad_contratante ?? '—'}
                                icono={Building2}
                            />
                            <Dato
                                label="Responsable"
                                valor={obra.creador ?? '—'}
                                icono={User}
                            />
                            <Dato
                                label="Monto contractual"
                                valor={formatearMonto(obra.monto_contractual)}
                                icono={DollarSign}
                            />
                            <Dato
                                label="Inicio · término previsto"
                                valor={`${obra.fecha_inicio ?? '—'} → ${obra.fecha_fin_prevista ?? '—'}`}
                                icono={CalendarRange}
                            />
                        </div>

                        {/* Mapa */}
                        <div className="h-56 overflow-hidden rounded-lg border border-border md:col-span-2 lg:col-span-1">
                            {obra.latitud !== null && obra.longitud !== null ? (
                                <MapaUbicacion
                                    latitud={obra.latitud}
                                    longitud={obra.longitud}
                                    soloLectura
                                    altura="224px"
                                />
                            ) : (
                                <div className="flex size-full flex-col items-center justify-center gap-2 bg-muted/30 px-6 text-center text-sm text-muted-foreground">
                                    <MapPin className="size-7 text-muted-foreground/50" />
                                    Sin ubicación geográfica
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Progreso por días — a todo el ancho */}
                    <div className="mt-5 space-y-2 border-t border-border pt-4">
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium text-foreground">
                                Progreso general
                            </span>
                            <span className="font-semibold text-muted-foreground tabular-nums">
                                {progreso !== null ? `${progreso}%` : '—'}
                            </span>
                        </div>
                        <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                className="h-full rounded-full bg-primary transition-all"
                                style={{ width: `${progreso ?? 0}%` }}
                            />
                        </div>
                        <div className="text-xs text-muted-foreground">
                            Etapa actual:{' '}
                            <span className="font-medium text-foreground">
                                {obra.estado_label}
                            </span>
                        </div>
                    </div>
                </Card>

                {/* Accesos directos a los módulos de la obra */}
                <div className="grid gap-4 sm:grid-cols-2">
                    <AccesoDirecto
                        href={`/obras/${obra.id}/documentos`}
                        icono={FolderTree}
                        label="Documentos"
                        valor={contadores.documentos}
                        unidad="documentos"
                    />
                    <AccesoDirecto
                        href={`/obras/${obra.id}/cuaderno`}
                        icono={NotebookPen}
                        label="Cuaderno de obra"
                        valor={contadores.cuaderno}
                        unidad="asientos"
                    />
                    <AccesoDirecto
                        href={`/obras/${obra.id}/calendario`}
                        icono={CalendarDays}
                        label="Calendario"
                        valor={contadores.calendario}
                        unidad="eventos"
                    />
                    <AccesoDirecto
                        href={`/obras/${obra.id}/equipo`}
                        icono={Users}
                        label="Equipo"
                        valor={contadores.equipo}
                        unidad="integrantes"
                    />
                    {puedeVerCaja && (
                        <AccesoDirecto
                            href={`/obras/${obra.id}/caja`}
                            icono={Wallet}
                            label="Caja chica"
                            valor={contadores.caja}
                            unidad="movimientos"
                        />
                    )}
                </div>
            </div>
        </>
    );
}

ObraShow.layout = {
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Detalle', href: '' },
    ],
};
