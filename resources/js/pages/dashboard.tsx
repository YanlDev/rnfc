import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    ArrowRight,
    Building2,
    CheckCircle2,
    Clock,
    FileText,
    NotebookPen,
    PauseCircle,
} from 'lucide-react';
import type { ComponentType } from 'react';
import { EstadoObraBadge } from '@/components/estado-obra-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';

type Kpis = {
    totalObras: number;
    enEjecucion: number;
    finalizadas: number;
    paralizadas: number;
};

type ObraResumen = {
    id: number;
    codigo: string;
    nombre: string;
    ubicacion: string | null;
    estado: string;
    estado_label: string;
    rol: string | null;
};

type ActividadRow = {
    icono: string;
    titulo: string;
    subtitulo: string;
    enlace: string;
    created_at_relativo: string;
};

type SharedProps = {
    auth: { user: { name: string } | null };
};

type DashboardPageProps = {
    esAdmin: boolean;
    kpis: Kpis;
    misObras: ObraResumen[];
    actividadReciente: ActividadRow[];
};

const ICONOS_ACTIVIDAD: Record<
    string,
    ComponentType<{ className?: string }>
> = {
    NotebookPen,
    FileText,
};

function Kpi({
    label,
    value,
    Icono,
    alerta = false,
}: {
    label: string;
    value: number;
    Icono: ComponentType<{ className?: string }>;
    alerta?: boolean;
}) {
    return (
        <div className="flex flex-col gap-2 p-4">
            <div className="flex items-center justify-between">
                <span className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </span>
                <Icono
                    className={
                        alerta && value > 0
                            ? 'size-4 text-amber-500'
                            : 'size-4 text-muted-foreground/70'
                    }
                />
            </div>
            <div className="text-2xl font-semibold text-foreground tabular-nums">
                {value}
            </div>
        </div>
    );
}

export default function Dashboard() {
    const { esAdmin, kpis, misObras, actividadReciente } =
        usePage<DashboardPageProps>().props;
    const { auth } = usePage<SharedProps>().props;
    const nombre = auth.user?.name?.split(' ')[0] ?? '';

    return (
        <>
            <Head title="Panel" />
            <div className="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
                <div>
                    <h1 className="text-xl font-semibold text-foreground">
                        Hola{nombre ? `, ${nombre}` : ''}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {esAdmin
                            ? 'Resumen general de la plataforma.'
                            : 'Resumen de las obras donde participas.'}
                    </p>
                </div>

                {/* KPIs — panel sobrio con divisores */}
                <section className="grid grid-cols-2 divide-x divide-y divide-border overflow-hidden rounded-xl border border-border bg-card lg:grid-cols-4">
                    <Kpi
                        label={esAdmin ? 'Total de obras' : 'Mis obras'}
                        value={kpis.totalObras}
                        Icono={Building2}
                    />
                    <Kpi
                        label="En ejecución"
                        value={kpis.enEjecucion}
                        Icono={Activity}
                    />
                    <Kpi
                        label="Finalizadas"
                        value={kpis.finalizadas}
                        Icono={CheckCircle2}
                    />
                    <Kpi
                        label="Paralizadas"
                        value={kpis.paralizadas}
                        Icono={PauseCircle}
                        alerta
                    />
                </section>

                <section className="grid gap-4 lg:grid-cols-3">
                    {/* Mis obras */}
                    <Card className="min-w-0 lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0">
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <Building2 className="size-4" />
                                {esAdmin ? 'Obras recientes' : 'Mis obras'}
                            </CardTitle>
                            <Link
                                href="/obras"
                                className="flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                            >
                                Ver todas
                                <ArrowRight className="size-3" />
                            </Link>
                        </CardHeader>
                        <CardContent className="space-y-1.5">
                            {misObras.length === 0 ? (
                                <div className="flex h-40 flex-col items-center justify-center gap-2 rounded-md border border-dashed border-border bg-muted/30 text-center text-sm text-muted-foreground">
                                    <Building2 className="size-6 opacity-50" />
                                    Aún no tienes obras asignadas.
                                </div>
                            ) : (
                                misObras.map((o) => (
                                    <Link
                                        key={o.id}
                                        href={`/obras/${o.id}`}
                                        className="flex items-center justify-between gap-3 rounded-lg border border-transparent px-2 py-2 hover:border-border hover:bg-muted/50"
                                    >
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <span className="truncate font-mono text-[11px] font-semibold text-muted-foreground">
                                                    {o.codigo}
                                                </span>
                                                {o.rol && (
                                                    <span className="shrink-0 text-[11px] text-muted-foreground">
                                                        · {o.rol}
                                                    </span>
                                                )}
                                            </div>
                                            <div className="truncate text-sm font-medium">
                                                {o.nombre}
                                            </div>
                                            {o.ubicacion && (
                                                <div className="truncate text-xs text-muted-foreground">
                                                    {o.ubicacion}
                                                </div>
                                            )}
                                        </div>
                                        <EstadoObraBadge
                                            estado={o.estado}
                                            label={o.estado_label}
                                            className="shrink-0"
                                        />
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    {/* Actividad reciente */}
                    <Card className="min-w-0">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <Activity className="size-4" />
                                Actividad reciente
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {actividadReciente.length === 0 ? (
                                <div className="flex h-40 items-center justify-center rounded-md border border-dashed border-border bg-muted/30 text-center text-sm text-muted-foreground">
                                    Sin actividad todavía.
                                </div>
                            ) : (
                                <ul className="space-y-0.5">
                                    {actividadReciente.map((ev, i) => {
                                        const Icono =
                                            ICONOS_ACTIVIDAD[ev.icono] ??
                                            Activity;

                                        return (
                                            <li key={`${ev.enlace}-${i}`}>
                                                <Link
                                                    href={ev.enlace}
                                                    className="flex items-start gap-3 rounded-lg p-2 transition-colors hover:bg-muted/50"
                                                >
                                                    <div className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                                        <Icono className="size-4" />
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <div className="truncate text-sm font-medium">
                                                            {ev.titulo}
                                                        </div>
                                                        <div className="flex items-center gap-1.5 text-xs text-muted-foreground">
                                                            <span className="truncate">
                                                                {ev.subtitulo}
                                                            </span>
                                                            <span aria-hidden>
                                                                ·
                                                            </span>
                                                            <span className="flex shrink-0 items-center gap-0.5">
                                                                <Clock className="size-3" />
                                                                {
                                                                    ev.created_at_relativo
                                                                }
                                                            </span>
                                                        </div>
                                                    </div>
                                                </Link>
                                            </li>
                                        );
                                    })}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    title: 'Panel de control',
    description: 'Resumen general de obras y actividad en la plataforma RNFC.',
    breadcrumbs: [
        {
            title: 'Panel',
            href: dashboard(),
        },
    ],
};
