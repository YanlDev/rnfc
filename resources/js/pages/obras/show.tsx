import { Head, Link, router, setLayoutProps } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    Calendar,
    CalendarDays,
    DollarSign,
    FolderTree,
    Hash,
    MapPin,
    Pencil,
    Trash2,
    User,
} from 'lucide-react';
import MapaUbicacion from '@/components/mapa-ubicacion';
import MiniCalendario, { type MiniEvento } from '@/components/mini-calendario';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import obras from '@/routes/obras';
import EquipoObra, {
    type InvitacionPendiente,
    type Miembro,
} from './_equipo';

type ObraData = {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string | null;
    ubicacion: string | null;
    latitud: number | null;
    longitud: number | null;
    entidad_contratante: string | null;
    monto_contractual: number | null;
    fecha_inicio: string | null;
    fecha_fin_prevista: string | null;
    fecha_fin_real: string | null;
    estado: string;
    estado_label: string;
    creador: string | null;
    created_at: string | null;
    updated_at: string | null;
};

const ESTADO_BADGE: Record<string, string> = {
    planificacion: 'bg-slate-200 text-slate-800',
    en_ejecucion: 'bg-[var(--color-brand-verde)] text-white',
    paralizada: 'bg-[var(--color-brand-amarillo)] text-[#3e4142]',
    finalizada: 'bg-[var(--color-brand-azul-oscuro)] text-white',
    archivada: 'bg-slate-500 text-white',
};

function formatearMonto(monto: number | null): string {
    if (monto === null) return '—';
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        maximumFractionDigits: 2,
    }).format(monto);
}

function Dato({
    label,
    valor,
    icono: Icono,
}: {
    label: string;
    valor: React.ReactNode;
    icono?: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="flex items-start gap-3">
            {Icono && <Icono className="mt-0.5 size-4 text-primary" />}
            <div className="flex-1">
                <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </div>
                <div className="text-sm font-medium text-foreground">{valor}</div>
            </div>
        </div>
    );
}

type RolOpcion = { value: string; label: string };

type ShowProps = {
    obra: ObraData;
    equipo: Miembro[];
    invitacionesPendientes: InvitacionPendiente[];
    rolesObra: RolOpcion[];
    puedeAdministrar: boolean;
    eventosCalendario: MiniEvento[];
};

export default function ObraShow({
    obra,
    equipo,
    invitacionesPendientes,
    rolesObra,
    puedeAdministrar,
    eventosCalendario,
}: ShowProps) {
    setLayoutProps({
        title: obra.nombre,
        description: `${obra.codigo} · ${obra.estado_label}${obra.entidad_contratante ? ' · ' + obra.entidad_contratante : ''}`,
    });

    const eliminar = () => {
        const ok = confirm(
            `¿Eliminar definitivamente la obra ${obra.codigo}?\n\n` +
                'Esta acción también elimina todos los certificados asociados y no se puede deshacer.',
        );
        if (!ok) return;
        router.delete(obras.destroy(obra.id).url);
    };

    return (
        <>
            <Head title={`${obra.codigo} — ${obra.nombre}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={obras.index().url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver al listado
                    </Link>
                    <div className="flex items-center gap-2">
                        <Badge className={ESTADO_BADGE[obra.estado] ?? ''}>
                            {obra.estado_label}
                        </Badge>
                        <Button asChild>
                            <Link href={`/obras/${obra.id}/documentos`}>
                                <FolderTree className="size-4" />
                                Documentos
                            </Link>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href={obras.edit(obra.id).url}>
                                <Pencil className="size-4" />
                                Editar
                            </Link>
                        </Button>
                        <Button variant="ghost" onClick={eliminar}>
                            <Trash2 className="size-4 text-destructive" />
                        </Button>
                    </div>
                </div>

                {/* Top: Información general + Cronograma a la izquierda · Mini calendario a la derecha */}
                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Información general</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-2">
                                <Dato label="Código" valor={obra.codigo} icono={Hash} />
                                <Dato
                                    label="Entidad contratante"
                                    valor={obra.entidad_contratante ?? '—'}
                                    icono={Building2}
                                />
                                <Dato
                                    label="Ubicación"
                                    valor={obra.ubicacion ?? '—'}
                                    icono={MapPin}
                                />
                                <Dato
                                    label="Monto contractual"
                                    valor={formatearMonto(obra.monto_contractual)}
                                    icono={DollarSign}
                                />
                                {obra.descripcion && (
                                    <div className="sm:col-span-2">
                                        <div className="mb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                            Descripción
                                        </div>
                                        <p className="text-sm whitespace-pre-line text-foreground">
                                            {obra.descripcion}
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Cronograma</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-3">
                                <Dato
                                    label="Inicio"
                                    valor={obra.fecha_inicio ?? '—'}
                                    icono={Calendar}
                                />
                                <Dato
                                    label="Fin previsto"
                                    valor={obra.fecha_fin_prevista ?? '—'}
                                    icono={Calendar}
                                />
                                <Dato
                                    label="Fin real"
                                    valor={obra.fecha_fin_real ?? '—'}
                                    icono={Calendar}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <CalendarDays className="size-5 text-primary" />
                                Calendario
                            </CardTitle>
                            <Button asChild size="sm" variant="outline">
                                <Link href={`/obras/${obra.id}/calendario`}>
                                    Ver completo
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <Link
                                href={`/obras/${obra.id}/calendario`}
                                className="block rounded-md p-1 transition-colors hover:bg-muted/30"
                                title="Abrir calendario completo"
                            >
                                <MiniCalendario eventos={eventosCalendario} />
                            </Link>
                            <div className="mt-3 text-center text-xs text-muted-foreground">
                                {eventosCalendario.length}{' '}
                                {eventosCalendario.length === 1
                                    ? 'evento registrado'
                                    : 'eventos registrados'}{' '}
                                · haz clic para abrir el calendario completo
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Mapa (si aplica) */}
                {obra.latitud !== null && obra.longitud !== null && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Ubicación en mapa</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <MapaUbicacion
                                latitud={obra.latitud}
                                longitud={obra.longitud}
                                soloLectura
                                altura="280px"
                            />
                        </CardContent>
                    </Card>
                )}

                {/* Equipo full width */}
                <EquipoObra
                    obraId={obra.id}
                    equipo={equipo}
                    invitacionesPendientes={invitacionesPendientes}
                    rolesObra={rolesObra}
                    puedeAdministrar={puedeAdministrar}
                />

                {/* Auditoría al final */}
                <Card>
                    <CardHeader>
                        <CardTitle>Auditoría</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 sm:grid-cols-3">
                        <Dato
                            label="Creado por"
                            valor={obra.creador ?? '—'}
                            icono={User}
                        />
                        <Dato
                            label="Creada el"
                            valor={
                                obra.created_at
                                    ? new Date(obra.created_at).toLocaleString('es-PE')
                                    : '—'
                            }
                        />
                        <Dato
                            label="Última actualización"
                            valor={
                                obra.updated_at
                                    ? new Date(obra.updated_at).toLocaleString('es-PE')
                                    : '—'
                            }
                        />
                    </CardContent>
                </Card>
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
