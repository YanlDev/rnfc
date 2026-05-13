import { Head, Link } from '@inertiajs/react';
import { Building2, ChevronRight, NotebookPen } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import obras from '@/routes/obras';

type ObraConCuaderno = {
    id: number;
    codigo: string;
    nombre: string;
    entidad_contratante: string | null;
    estado: string;
    estado_label: string;
    asientos_supervisor: number;
    asientos_residente: number;
};

type Props = {
    obras: ObraConCuaderno[];
};

const ESTADO_BADGE: Record<string, string> = {
    planificacion: 'bg-slate-200 text-slate-800 hover:bg-slate-200',
    en_ejecucion: 'bg-[var(--color-brand-verde)] text-white hover:bg-[var(--color-brand-verde)]',
    paralizada: 'bg-[var(--color-brand-amarillo)] text-[#3e4142] hover:bg-[var(--color-brand-amarillo)]',
    finalizada: 'bg-[var(--color-brand-azul-oscuro)] text-white hover:bg-[var(--color-brand-azul-oscuro)]',
    archivada: 'bg-slate-500 text-white hover:bg-slate-500',
};

export default function CuadernoSelector({ obras: lista }: Props) {
    const [busqueda, setBusqueda] = useState('');

    const filtradas = useMemo(() => {
        const q = busqueda.trim().toLowerCase();
        if (!q) return lista;
        return lista.filter(
            (o) =>
                o.codigo.toLowerCase().includes(q) ||
                o.nombre.toLowerCase().includes(q) ||
                (o.entidad_contratante ?? '').toLowerCase().includes(q),
        );
    }, [lista, busqueda]);

    if (lista.length === 0) {
        return (
            <>
                <Head title="Cuaderno de Obra" />
                <div className="flex flex-1 flex-col items-center justify-center gap-4 p-8 text-center">
                    <NotebookPen className="size-12 text-muted-foreground" />
                    <h2 className="text-xl font-semibold">
                        No tienes obras con cuaderno disponible
                    </h2>
                    <p className="max-w-md text-sm text-muted-foreground">
                        El cuaderno se administra por obra. Crea una obra (o
                        pide que te vinculen a una) para empezar a registrar
                        asientos.
                    </p>
                    <Button asChild>
                        <Link href={obras.create().url}>
                            <Building2 className="size-4" />
                            Crear obra
                        </Link>
                    </Button>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Cuaderno de Obra" />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <Input
                    placeholder="Buscar obra por código, nombre o entidad…"
                    value={busqueda}
                    onChange={(e) => setBusqueda(e.target.value)}
                    className="max-w-md"
                />

                {filtradas.length === 0 ? (
                    <Card className="p-10 text-center text-sm text-muted-foreground">
                        Sin coincidencias.
                    </Card>
                ) : (
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {filtradas.map((o) => (
                            <Link
                                key={o.id}
                                href={`/obras/${o.id}/cuaderno`}
                                className="block"
                            >
                                <Card className="group h-full transition-shadow hover:shadow-md">
                                    <CardContent className="flex h-full flex-col gap-3 p-4">
                                        <div className="flex items-start justify-between gap-2">
                                            <span className="font-mono text-xs font-semibold tracking-wider text-primary">
                                                {o.codigo}
                                            </span>
                                            <Badge
                                                className={
                                                    ESTADO_BADGE[o.estado] ?? ''
                                                }
                                            >
                                                {o.estado_label}
                                            </Badge>
                                        </div>
                                        <h3 className="line-clamp-2 text-sm font-semibold">
                                            {o.nombre}
                                        </h3>
                                        {o.entidad_contratante && (
                                            <p className="line-clamp-1 text-xs text-muted-foreground">
                                                {o.entidad_contratante}
                                            </p>
                                        )}
                                        <div className="mt-auto grid grid-cols-2 gap-2 border-t border-border pt-3 text-center">
                                            <div>
                                                <div className="text-[10px] font-medium tracking-wide text-muted-foreground uppercase">
                                                    Supervisión
                                                </div>
                                                <div className="text-lg font-bold tabular-nums text-foreground">
                                                    {o.asientos_supervisor}
                                                </div>
                                            </div>
                                            <div>
                                                <div className="text-[10px] font-medium tracking-wide text-muted-foreground uppercase">
                                                    Residencia
                                                </div>
                                                <div className="text-lg font-bold tabular-nums text-foreground">
                                                    {o.asientos_residente}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-end gap-1 text-xs text-primary opacity-0 transition-opacity group-hover:opacity-100">
                                            Abrir cuaderno
                                            <ChevronRight className="size-3.5" />
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

CuadernoSelector.layout = {
    title: 'Cuaderno de Obra',
    description: 'Elige la obra para abrir su cuaderno digital (supervisor y residente).',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Cuaderno', href: '/cuaderno' },
    ],
};
