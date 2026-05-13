import { Head, usePage } from '@inertiajs/react';
import {
    Activity,
    Building2,
    CheckCircle2,
    PauseCircle,
} from 'lucide-react';
import type { ComponentType, SVGProps } from 'react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';

type Kpis = {
    totalObras: number;
    enEjecucion: number;
    finalizadas: number;
    paralizadas: number;
};

type DashboardPageProps = {
    kpis: Kpis;
};

type KpiCardProps = {
    titulo: string;
    valor: number;
    descripcion: string;
    Icono: ComponentType<SVGProps<SVGSVGElement>>;
    acento: string;
};

function KpiCard({ titulo, valor, descripcion, Icono, acento }: KpiCardProps) {
    return (
        <Card className="relative overflow-hidden">
            <span
                aria-hidden
                className={`absolute inset-x-0 top-0 h-1 ${acento}`}
            />
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                    {titulo}
                </CardTitle>
                <Icono className="size-5 text-primary" />
            </CardHeader>
            <CardContent>
                <div className="text-3xl font-bold text-foreground tabular-nums">
                    {valor}
                </div>
                <CardDescription className="mt-1 text-xs">
                    {descripcion}
                </CardDescription>
            </CardContent>
        </Card>
    );
}

export default function Dashboard() {
    const { kpis } = usePage<DashboardPageProps>().props;

    return (
        <>
            <Head title="Panel" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <KpiCard
                        titulo="Total de obras"
                        valor={kpis.totalObras}
                        descripcion="Obras registradas en la plataforma"
                        Icono={Building2}
                        acento="bg-primary"
                    />
                    <KpiCard
                        titulo="En ejecución"
                        valor={kpis.enEjecucion}
                        descripcion="Obras activas en supervisión"
                        Icono={Activity}
                        acento="bg-[var(--color-brand-verde)]"
                    />
                    <KpiCard
                        titulo="Finalizadas"
                        valor={kpis.finalizadas}
                        descripcion="Obras culminadas y entregadas"
                        Icono={CheckCircle2}
                        acento="bg-[var(--color-brand-verde-claro)]"
                    />
                    <KpiCard
                        titulo="Paralizadas"
                        valor={kpis.paralizadas}
                        descripcion="Requieren atención inmediata"
                        Icono={PauseCircle}
                        acento="bg-[var(--color-brand-amarillo)]"
                    />
                </section>

                <section className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Actividad reciente</CardTitle>
                            <CardDescription>
                                Últimos movimientos en obras, documentos y
                                cuaderno.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex h-48 items-center justify-center rounded-md border border-dashed border-border bg-muted/30 text-sm text-muted-foreground">
                                Sin actividad registrada todavía
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-[#145694] to-[#2850da] text-white">
                        <CardHeader>
                            <CardTitle className="text-white">
                                Bienvenido a RNFC
                            </CardTitle>
                            <CardDescription className="text-white/80">
                                Plataforma de supervisión y consultoría de
                                obras.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-white/90">
                            <p>
                                Gestiona obras, equipos, documentos y cuadernos
                                de obra en un solo lugar.
                            </p>
                            <p className="text-xs text-white/70">
                                Plataforma en desarrollo · v0.1
                            </p>
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
