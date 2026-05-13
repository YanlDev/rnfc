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
    type ChartConfig,
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from '@/components/ui/chart';

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

const COLORES_ESTADO: Record<string, string> = {
    planificacion: '#94a3b8',
    en_ejecucion: '#5da235',
    paralizada: '#ffd21c',
    finalizada: '#145694',
    archivada: '#64748b',
};

const COLORES_TIPO_CERT = [
    '#145694',
    '#2850da',
    '#5da235',
    '#9ed146',
    '#ffd21c',
    '#1aa39c',
    '#c1272d',
    '#5d6166',
];

const ICONOS_ACTIVIDAD: Record<string, React.ComponentType<{ className?: string }>> = {
    Award,
    FolderTree,
    NotebookPen,
    Building2,
};

function bytesHumano(bytes: number) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 ** 2) return `${(bytes / 1024).toFixed(1)} KB`;
    if (bytes < 1024 ** 3) return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
    return `${(bytes / 1024 ** 3).toFixed(2)} GB`;
}

function KpiCard({
    label,
    value,
    sub,
    Icono,
    acento,
}: {
    label: string;
    value: string | number;
    sub?: string;
    Icono: React.ComponentType<{ className?: string }>;
    acento: string;
}) {
    return (
        <Card className="relative overflow-hidden">
            <span
                aria-hidden
                className="absolute inset-x-0 top-0 h-1"
                style={{ backgroundColor: acento }}
            />
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                    {label}
                </CardTitle>
                <Icono className="size-4 text-primary" />
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold tabular-nums">{value}</div>
                {sub && (
                    <div className="mt-0.5 text-xs text-muted-foreground">{sub}</div>
                )}
            </CardContent>
        </Card>
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
        .map((e) => ({
            ...e,
            fill: COLORES_ESTADO[e.value] ?? '#5d6166',
        }));

    const configEstado: ChartConfig = Object.fromEntries(
        estadosObras.map((e) => [
            e.value,
            { label: e.label, color: COLORES_ESTADO[e.value] ?? '#5d6166' },
        ]),
    );

    const datosCertChart = certificadosPorTipo.map((t, i) => ({
        ...t,
        fill: COLORES_TIPO_CERT[i % COLORES_TIPO_CERT.length],
    }));

    const configCert: ChartConfig = Object.fromEntries(
        certificadosPorTipo.map((t, i) => [
            t.value,
            { label: t.label, color: COLORES_TIPO_CERT[i % COLORES_TIPO_CERT.length] },
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
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* KPIs principales */}
                <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <KpiCard
                        label="Obras"
                        value={kpis.obras_total}
                        sub={`${kpis.obras_en_ejecucion} en ejecución · ${kpis.obras_finalizadas} finalizadas`}
                        Icono={Building2}
                        acento="#145694"
                    />
                    <KpiCard
                        label="Certificados"
                        value={kpis.certificados_total}
                        sub={
                            kpis.certificados_revocados > 0
                                ? `${kpis.certificados_revocados} revocado(s)`
                                : 'Todos vigentes'
                        }
                        Icono={Award}
                        acento="#2850da"
                    />
                    <KpiCard
                        label="Documentos"
                        value={kpis.documentos_total}
                        sub={`${kpis.documentos_con_versiones} con versiones · ${kpis.carpetas_total} carpetas`}
                        Icono={FolderTree}
                        acento="#5da235"
                    />
                    <KpiCard
                        label="Asientos cuaderno"
                        value={kpis.asientos_total}
                        sub={`${kpis.eventos_total} eventos de calendario`}
                        Icono={NotebookPen}
                        acento="#9ed146"
                    />
                    <KpiCard
                        label="Usuarios"
                        value={kpis.usuarios_total}
                        sub={`${kpis.usuarios_activos} con obras asignadas`}
                        Icono={Users}
                        acento="#1aa39c"
                    />
                    <KpiCard
                        label="Invitaciones"
                        value={kpis.invitaciones_pendientes}
                        sub="pendientes de aceptación"
                        Icono={Mail}
                        acento="#ffd21c"
                    />
                    <KpiCard
                        label="Almacenamiento"
                        value={bytesHumano(kpis.almacenamiento_total_bytes)}
                        sub="ocupado por documentos"
                        Icono={HardDrive}
                        acento="#c1272d"
                    />
                    <KpiCard
                        label="Obras paralizadas"
                        value={kpis.obras_paralizadas}
                        sub={
                            kpis.obras_paralizadas > 0
                                ? 'Requieren atención'
                                : 'Ninguna paralizada'
                        }
                        Icono={Bell}
                        acento="#3e4142"
                    />
                </section>

                {/* Charts */}
                <section className="grid gap-4 lg:grid-cols-2">
                    {/* Donut estado de obras */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Building2 className="size-5 text-primary" />
                                Distribución de obras por estado
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {datosEstadoChart.length === 0 ? (
                                <p className="py-12 text-center text-sm text-muted-foreground">
                                    Aún no hay obras registradas.
                                </p>
                            ) : (
                                <ChartContainer
                                    config={configEstado}
                                    className="mx-auto aspect-square max-h-[280px]"
                                >
                                    <PieChart>
                                        <ChartTooltip
                                            cursor={false}
                                            content={<ChartTooltipContent hideLabel />}
                                        />
                                        <Pie
                                            data={datosEstadoChart}
                                            dataKey="total"
                                            nameKey="value"
                                            innerRadius={60}
                                            strokeWidth={2}
                                        >
                                            {datosEstadoChart.map((entry) => (
                                                <Cell key={entry.value} fill={entry.fill} />
                                            ))}
                                        </Pie>
                                        <ChartLegend
                                            content={<ChartLegendContent nameKey="value" />}
                                        />
                                    </PieChart>
                                </ChartContainer>
                            )}
                        </CardContent>
                    </Card>

                    {/* Donut tipos de certificado */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Award className="size-5 text-primary" />
                                Certificados por tipo
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {datosCertChart.length === 0 ? (
                                <p className="py-12 text-center text-sm text-muted-foreground">
                                    Aún no hay certificados emitidos.
                                </p>
                            ) : (
                                <ChartContainer
                                    config={configCert}
                                    className="mx-auto aspect-square max-h-[280px]"
                                >
                                    <PieChart>
                                        <ChartTooltip
                                            cursor={false}
                                            content={<ChartTooltipContent hideLabel />}
                                        />
                                        <Pie
                                            data={datosCertChart}
                                            dataKey="total"
                                            nameKey="value"
                                            innerRadius={60}
                                            strokeWidth={2}
                                        >
                                            {datosCertChart.map((entry) => (
                                                <Cell key={entry.value} fill={entry.fill} />
                                            ))}
                                        </Pie>
                                        <ChartLegend
                                            content={<ChartLegendContent nameKey="value" />}
                                        />
                                    </PieChart>
                                </ChartContainer>
                            )}
                        </CardContent>
                    </Card>
                </section>

                {/* Almacenamiento bar chart */}
                <section className="grid gap-4 lg:grid-cols-[2fr_1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <HardDrive className="size-5 text-primary" />
                                Top 5 obras por almacenamiento
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {datosAlmacenamiento.length === 0 ? (
                                <p className="py-12 text-center text-sm text-muted-foreground">
                                    Aún no hay documentos subidos.
                                </p>
                            ) : (
                                <ChartContainer
                                    config={{
                                        mb: { label: 'MB', color: '#145694' },
                                    }}
                                    className="aspect-[16/7] max-h-[260px] w-full"
                                >
                                    <BarChart
                                        data={datosAlmacenamiento}
                                        margin={{ top: 10, right: 10, left: -10, bottom: 0 }}
                                    >
                                        <CartesianGrid vertical={false} />
                                        <XAxis
                                            dataKey="nombre"
                                            tickLine={false}
                                            axisLine={false}
                                        />
                                        <YAxis tickLine={false} axisLine={false} />
                                        <ChartTooltip
                                            cursor={{ fill: 'rgba(20, 86, 148, 0.08)' }}
                                            content={
                                                <ChartTooltipContent
                                                    labelFormatter={(_v: unknown, p: { payload?: { nombreCompleto?: string } }[]) =>
                                                        p?.[0]?.payload?.nombreCompleto ?? ''
                                                    }
                                                />
                                            }
                                        />
                                        <Bar
                                            dataKey="mb"
                                            fill="#145694"
                                            radius={[6, 6, 0, 0]}
                                        />
                                    </BarChart>
                                </ChartContainer>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Trophy className="size-5 text-primary" />
                                Top usuarios activos
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {usuariosActivos.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Sin actividad todavía.
                                </p>
                            ) : (
                                usuariosActivos.map((u, i) => (
                                    <div
                                        key={u.id}
                                        className="flex items-center justify-between gap-2 rounded-md border border-border p-2 text-sm"
                                    >
                                        <div className="flex min-w-0 items-center gap-2">
                                            <span className="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary tabular-nums">
                                                {i + 1}
                                            </span>
                                            <div className="min-w-0">
                                                <div className="truncate font-medium">
                                                    {u.name}
                                                </div>
                                                <div className="truncate text-[10px] text-muted-foreground">
                                                    {u.email}
                                                </div>
                                            </div>
                                        </div>
                                        <Badge variant="secondary">
                                            {u.obras}{' '}
                                            {u.obras === 1 ? 'obra' : 'obras'}
                                        </Badge>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </section>

                {/* Documentos por obra + Actividad reciente */}
                <section className="grid gap-4 lg:grid-cols-[1fr_2fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <FileText className="size-5 text-primary" />
                                Top obras por carpetas
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2">
                            {documentosPorObra.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Sin obras todavía.
                                </p>
                            ) : (
                                documentosPorObra.map((o, i) => (
                                    <Link
                                        key={o.obra_id}
                                        href={`/obras/${o.obra_id}/documentos`}
                                        className="flex items-center justify-between gap-2 rounded-md border border-border p-2 text-sm hover:bg-muted/30"
                                    >
                                        <div className="flex min-w-0 items-center gap-2">
                                            <span className="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary tabular-nums">
                                                {i + 1}
                                            </span>
                                            <div className="min-w-0">
                                                <div className="truncate font-mono text-[10px] font-semibold text-primary">
                                                    {o.codigo}
                                                </div>
                                                <div className="truncate text-xs">
                                                    {o.nombre}
                                                </div>
                                            </div>
                                        </div>
                                        <Badge variant="secondary">
                                            {o.carpetas}
                                        </Badge>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Activity className="size-5 text-primary" />
                                Actividad reciente
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {actividadReciente.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">
                                    Sin actividad registrada todavía.
                                </p>
                            ) : (
                                <ul className="space-y-1">
                                    {actividadReciente.map((ev, i) => {
                                        const Icono =
                                            ICONOS_ACTIVIDAD[ev.icono] ?? Activity;
                                        return (
                                            <li key={i}>
                                                <Link
                                                    href={ev.enlace}
                                                    className="flex items-start gap-3 rounded-md p-2 transition-colors hover:bg-muted/40"
                                                >
                                                    <div
                                                        className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full"
                                                        style={{
                                                            backgroundColor: `${ev.color}1a`,
                                                            color: ev.color,
                                                        }}
                                                    >
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
