import { Head, Link } from '@inertiajs/react';
import {
    Activity,
    Award,
    Bell,
    Building2,
    Clock,
    FileText,
    FolderTree,
    HardDrive,
    Mail,
    NotebookPen,
    Trophy,
    Users,
} from 'lucide-react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Pie,
    PieChart,
    XAxis,
    YAxis,
} from 'recharts';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from '@/components/ui/chart';
import type { ChartConfig } from '@/components/ui/chart';
import { cn } from '@/lib/utils';

type Kpis = {
    obras_total: number;
    obras_en_ejecucion: number;
    obras_finalizadas: number;
    obras_paralizadas: number;
    certificados_total: number;
    certificados_revocados: number;
    documentos_total: number;
    documentos_con_versiones: number;
    carpetas_total: number;
    asientos_total: number;
    eventos_total: number;
    usuarios_total: number;
    usuarios_activos: number;
    invitaciones_pendientes: number;
    almacenamiento_total_bytes: number;
};

type EstadoObraRow = { value: string; label: string; total: number };
type CertificadoTipoRow = { value: string; label: string; total: number };
type AlmacenamientoRow = {
    obra_id: number;
    codigo: string;
    nombre: string;
    bytes: number;
    tamano_humano: string;
    documentos: number;
};
type DocumentosObraRow = {
    obra_id: number;
    codigo: string;
    nombre: string;
    carpetas: number;
};
type UsuarioActivoRow = {
    id: number;
    name: string;
    email: string;
    obras: number;
};
type ActividadRow = {
    tipo: string;
    icono: string;
    color: string;
    titulo: string;
    subtitulo: string;
    enlace: string;
    created_at_iso: string;
    created_at_relativo: string;
};

type Props = {
    kpis: Kpis;
    estadosObras: EstadoObraRow[];
    certificadosPorTipo: CertificadoTipoRow[];
    almacenamiento: AlmacenamientoRow[];
    documentosPorObra: DocumentosObraRow[];
    actividadReciente: ActividadRow[];
    usuariosActivos: UsuarioActivoRow[];
};

/**
 * Paleta sobria: escala de azules tomada de los tokens --chart-1..5 de
 * app.css. Se adapta sola al modo oscuro y mantiene un look ejecutivo.
 */
const PALETA = [
    'var(--chart-1)',
    'var(--chart-2)',
    'var(--chart-3)',
    'var(--chart-4)',
    'var(--chart-5)',
];

const ORDEN_ESTADO = [
    'en_ejecucion',
    'planificacion',
    'finalizada',
    'paralizada',
    'archivada',
];

const ICONOS_ACTIVIDAD: Record<
    string,
    React.ComponentType<{ className?: string }>
> = {
    Award,
    FolderTree,
    NotebookPen,
    Building2,
};

function bytesHumano(bytes: number) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 ** 2) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    if (bytes < 1024 ** 3) {
        return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
    }

    return `${(bytes / 1024 ** 3).toFixed(2)} GB`;
}

function colorEstado(value: string) {
    const i = ORDEN_ESTADO.indexOf(value);

    return PALETA[(i < 0 ? 0 : i) % PALETA.length];
}

function Kpi({
    label,
    value,
    sub,
    Icono,
    alerta = false,
}: {
    label: string;
    value: string | number;
    sub?: string;
    Icono: React.ComponentType<{ className?: string }>;
    alerta?: boolean;
}) {
    return (
        <div className="flex flex-col gap-2 p-4">
            <div className="flex items-center justify-between">
                <span className="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </span>
                <Icono
                    className={cn(
                        'size-4',
                        alerta ? 'text-amber-500' : 'text-muted-foreground/70',
                    )}
                />
            </div>
            <div className="text-2xl font-semibold text-foreground tabular-nums">
                {value}
            </div>
            {sub && (
                <div className="truncate text-xs text-muted-foreground">
                    {sub}
                </div>
            )}
        </div>
    );
}

function PanelTitulo({
    Icono,
    children,
}: {
    Icono: React.ComponentType<{ className?: string }>;
    children: React.ReactNode;
}) {
    return (
        <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
            <Icono className="size-4" />
            {children}
        </CardTitle>
    );
}

function RankingNumero({ n }: { n: number }) {
    return (
        <span className="flex size-6 shrink-0 items-center justify-center rounded-md bg-muted text-xs font-semibold text-muted-foreground tabular-nums">
            {n}
        </span>
    );
}

export default function AdminPanel({
    kpis,
    estadosObras,
    certificadosPorTipo,
    almacenamiento,
    documentosPorObra,
    actividadReciente,
    usuariosActivos,
}: Props) {
    const datosEstadoChart = estadosObras
        .filter((e) => e.total > 0)
        .map((e) => ({ ...e, fill: colorEstado(e.value) }));

    const configEstado: ChartConfig = Object.fromEntries(
        estadosObras.map((e) => [
            e.value,
            { label: e.label, color: colorEstado(e.value) },
        ]),
    );

    const datosCertChart = certificadosPorTipo.map((t, i) => ({
        ...t,
        fill: PALETA[i % PALETA.length],
    }));

    const configCert: ChartConfig = Object.fromEntries(
        certificadosPorTipo.map((t, i) => [
            t.value,
            { label: t.label, color: PALETA[i % PALETA.length] },
        ]),
    );

    const datosAlmacenamiento = almacenamiento.map((a) => ({
        nombre: a.codigo,
        nombreCompleto: a.nombre,
        mb: +(a.bytes / 1024 / 1024).toFixed(2),
        documentos: a.documentos,
    }));

    return (
        <>
            <Head title="Administración" />
            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                {/* KPIs — panel único con divisores, estética sobria */}
                <section className="grid grid-cols-2 divide-x divide-y divide-border overflow-hidden rounded-xl border border-border bg-card lg:grid-cols-4">
                    <Kpi
                        label="Obras"
                        value={kpis.obras_total}
                        sub={`${kpis.obras_en_ejecucion} en ejecución · ${kpis.obras_finalizadas} finalizadas`}
                        Icono={Building2}
                    />
                    <Kpi
                        label="Certificados"
                        value={kpis.certificados_total}
                        sub={
                            kpis.certificados_revocados > 0
                                ? `${kpis.certificados_revocados} revocado(s)`
                                : 'Todos vigentes'
                        }
                        Icono={Award}
                    />
                    <Kpi
                        label="Documentos"
                        value={kpis.documentos_total}
                        sub={`${kpis.documentos_con_versiones} con versiones · ${kpis.carpetas_total} carpetas`}
                        Icono={FolderTree}
                    />
                    <Kpi
                        label="Asientos cuaderno"
                        value={kpis.asientos_total}
                        sub={`${kpis.eventos_total} eventos de calendario`}
                        Icono={NotebookPen}
                    />
                    <Kpi
                        label="Usuarios"
                        value={kpis.usuarios_total}
                        sub={`${kpis.usuarios_activos} con obras asignadas`}
                        Icono={Users}
                    />
                    <Kpi
                        label="Invitaciones"
                        value={kpis.invitaciones_pendientes}
                        sub="pendientes de aceptación"
                        Icono={Mail}
                    />
                    <Kpi
                        label="Almacenamiento"
                        value={bytesHumano(kpis.almacenamiento_total_bytes)}
                        sub="ocupado por documentos"
                        Icono={HardDrive}
                    />
                    <Kpi
                        label="Obras paralizadas"
                        value={kpis.obras_paralizadas}
                        sub={
                            kpis.obras_paralizadas > 0
                                ? 'Requieren atención'
                                : 'Ninguna paralizada'
                        }
                        Icono={Bell}
                        alerta={kpis.obras_paralizadas > 0}
                    />
                </section>

                {/* Charts de distribución */}
                <section className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <PanelTitulo Icono={Building2}>
                                Obras por estado
                            </PanelTitulo>
                        </CardHeader>
                        <CardContent>
                            {datosEstadoChart.length === 0 ? (
                                <p className="py-12 text-center text-sm text-muted-foreground">
                                    Aún no hay obras registradas.
                                </p>
                            ) : (
                                <ChartContainer
                                    config={configEstado}
                                    className="mx-auto aspect-square max-h-[260px]"
                                >
                                    <PieChart>
                                        <ChartTooltip
                                            cursor={false}
                                            content={
                                                <ChartTooltipContent
                                                    hideLabel
                                                />
                                            }
                                        />
                                        <Pie
                                            data={datosEstadoChart}
                                            dataKey="total"
                                            nameKey="value"
                                            innerRadius={62}
                                            strokeWidth={2}
                                        >
                                            {datosEstadoChart.map((entry) => (
                                                <Cell
                                                    key={entry.value}
                                                    fill={entry.fill}
                                                />
                                            ))}
                                        </Pie>
                                        <ChartLegend
                                            content={
                                                <ChartLegendContent nameKey="value" />
                                            }
                                        />
                                    </PieChart>
                                </ChartContainer>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <PanelTitulo Icono={Award}>
                                Certificados por tipo
                            </PanelTitulo>
                        </CardHeader>
                        <CardContent>
                            {datosCertChart.length === 0 ? (
                                <p className="py-12 text-center text-sm text-muted-foreground">
                                    Aún no hay certificados emitidos.
                                </p>
                            ) : (
                                <ChartContainer
                                    config={configCert}
                                    className="mx-auto aspect-square max-h-[260px]"
                                >
                                    <PieChart>
                                        <ChartTooltip
                                            cursor={false}
                                            content={
                                                <ChartTooltipContent
                                                    hideLabel
                                                />
                                            }
                                        />
                                        <Pie
                                            data={datosCertChart}
                                            dataKey="total"
                                            nameKey="value"
                                            innerRadius={62}
                                            strokeWidth={2}
                                        >
                                            {datosCertChart.map((entry) => (
                                                <Cell
                                                    key={entry.value}
                                                    fill={entry.fill}
                                                />
                                            ))}
                                        </Pie>
                                        <ChartLegend
                                            content={
                                                <ChartLegendContent nameKey="value" />
                                            }
                                        />
                                    </PieChart>
                                </ChartContainer>
                            )}
                        </CardContent>
                    </Card>
                </section>

                {/* Almacenamiento + usuarios activos */}
                <section className="grid gap-4 lg:grid-cols-[2fr_1fr]">
                    <Card>
                        <CardHeader>
                            <PanelTitulo Icono={HardDrive}>
                                Top obras por almacenamiento
                            </PanelTitulo>
                        </CardHeader>
                        <CardContent>
                            {datosAlmacenamiento.length === 0 ? (
                                <p className="py-12 text-center text-sm text-muted-foreground">
                                    Aún no hay documentos subidos.
                                </p>
                            ) : (
                                <ChartContainer
                                    config={{
                                        mb: {
                                            label: 'MB',
                                            color: 'var(--chart-1)',
                                        },
                                    }}
                                    className="aspect-[16/7] max-h-[260px] w-full"
                                >
                                    <BarChart
                                        data={datosAlmacenamiento}
                                        margin={{
                                            top: 10,
                                            right: 10,
                                            left: -10,
                                            bottom: 0,
                                        }}
                                    >
                                        <CartesianGrid vertical={false} />
                                        <XAxis
                                            dataKey="nombre"
                                            tickLine={false}
                                            axisLine={false}
                                            tickMargin={8}
                                        />
                                        <YAxis
                                            tickLine={false}
                                            axisLine={false}
                                        />
                                        <ChartTooltip
                                            cursor={{
                                                fill: 'var(--muted)',
                                                fillOpacity: 0.5,
                                            }}
                                            content={
                                                <ChartTooltipContent
                                                    labelFormatter={(
                                                        _v: unknown,
                                                        p: {
                                                            payload?: {
                                                                nombreCompleto?: string;
                                                            };
                                                        }[],
                                                    ) =>
                                                        p?.[0]?.payload
                                                            ?.nombreCompleto ??
                                                        ''
                                                    }
                                                />
                                            }
                                        />
                                        <Bar
                                            dataKey="mb"
                                            fill="var(--chart-1)"
                                            radius={[6, 6, 0, 0]}
                                        />
                                    </BarChart>
                                </ChartContainer>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <PanelTitulo Icono={Trophy}>
                                Usuarios más activos
                            </PanelTitulo>
                        </CardHeader>
                        <CardContent className="space-y-1.5">
                            {usuariosActivos.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Sin actividad todavía.
                                </p>
                            ) : (
                                usuariosActivos.map((u, i) => (
                                    <div
                                        key={u.id}
                                        className="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-muted/50"
                                    >
                                        <div className="flex min-w-0 items-center gap-2.5">
                                            <RankingNumero n={i + 1} />
                                            <div className="min-w-0">
                                                <div className="truncate font-medium">
                                                    {u.name}
                                                </div>
                                                <div className="truncate text-[11px] text-muted-foreground">
                                                    {u.email}
                                                </div>
                                            </div>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className="shrink-0"
                                        >
                                            {u.obras}{' '}
                                            {u.obras === 1 ? 'obra' : 'obras'}
                                        </Badge>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </section>

                {/* Ranking carpetas + actividad reciente */}
                <section className="grid gap-4 lg:grid-cols-[1fr_2fr]">
                    <Card>
                        <CardHeader>
                            <PanelTitulo Icono={FileText}>
                                Obras con más carpetas
                            </PanelTitulo>
                        </CardHeader>
                        <CardContent className="space-y-1.5">
                            {documentosPorObra.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Sin obras todavía.
                                </p>
                            ) : (
                                documentosPorObra.map((o, i) => (
                                    <Link
                                        key={o.obra_id}
                                        href={`/obras/${o.obra_id}/documentos`}
                                        className="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-muted/50"
                                    >
                                        <div className="flex min-w-0 items-center gap-2.5">
                                            <RankingNumero n={i + 1} />
                                            <div className="min-w-0">
                                                <div className="truncate font-mono text-[11px] font-semibold text-muted-foreground">
                                                    {o.codigo}
                                                </div>
                                                <div className="truncate text-sm">
                                                    {o.nombre}
                                                </div>
                                            </div>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className="shrink-0"
                                        >
                                            {o.carpetas}
                                        </Badge>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <PanelTitulo Icono={Activity}>
                                Actividad reciente
                            </PanelTitulo>
                        </CardHeader>
                        <CardContent>
                            {actividadReciente.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Sin actividad registrada todavía.
                                </p>
                            ) : (
                                <ul className="space-y-0.5">
                                    {actividadReciente.map((ev) => {
                                        const Icono =
                                            ICONOS_ACTIVIDAD[ev.icono] ??
                                            Activity;

                                        return (
                                            <li
                                                key={`${ev.tipo}-${ev.enlace}-${ev.created_at_iso}`}
                                            >
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
                                                        <div className="truncate text-xs text-muted-foreground">
                                                            {ev.subtitulo}
                                                        </div>
                                                    </div>
                                                    <div className="flex shrink-0 items-center gap-1 text-[11px] text-muted-foreground">
                                                        <Clock className="size-3" />
                                                        {ev.created_at_relativo}
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

AdminPanel.layout = {
    title: 'Panel de Administración',
    description:
        'KPIs globales, ranking de almacenamiento y actividad reciente del sistema.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Administración', href: '/admin' },
    ],
};
