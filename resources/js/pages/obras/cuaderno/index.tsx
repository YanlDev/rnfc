import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Calendar as CalendarIcon,
    FileText,
    List as ListIcon,
    NotebookPen,
    Plus,
    User,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import obras from '@/routes/obras';
import AsientoDetalleModal, {
    type AsientoDetalle,
} from './_asiento-detalle';
import CalendarioAsientos from './_calendario';
import NuevoAsientoDialog from './_nuevo-asiento';

type Cuaderno = {
    value: string;
    label: string;
    label_corto: string;
    total: number;
};

type Asiento = AsientoDetalle & {
    created_at: string;
};

type ObraResumen = {
    id: number;
    codigo: string;
    nombre: string;
};

type Props = {
    obra: ObraResumen;
    tipoActivo: string;
    cuadernos: Cuaderno[];
    asientos: Asiento[];
    siguienteNumero: number;
    puedeEscribir: boolean;
    puedeEliminar: boolean;
};

type Vista = 'lista' | 'calendario';

export default function CuadernoIndex({
    obra,
    tipoActivo,
    cuadernos,
    asientos,
    siguienteNumero,
    puedeEscribir,
    puedeEliminar,
}: Props) {
    const [vista, setVista] = useState<Vista>('lista');
    const [mostrandoNuevo, setMostrandoNuevo] = useState(false);
    const [seleccionado, setSeleccionado] = useState<Asiento | null>(null);

    const cuadernoActivo = useMemo(
        () => cuadernos.find((c) => c.value === tipoActivo) ?? cuadernos[0],
        [cuadernos, tipoActivo],
    );

    const cambiarTipo = (nuevo: string) => {
        router.get(
            `/obras/${obra.id}/cuaderno`,
            { tipo: nuevo },
            { preserveScroll: true, preserveState: false },
        );
    };

    const abrirAsiento = (id: number) => {
        const a = asientos.find((x) => x.id === id) ?? null;
        setSeleccionado(a);
    };

    return (
        <>
            <Head title={`Cuaderno · ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={obras.show(obra.id).url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver a la obra
                    </Link>
                    {puedeEscribir && (
                        <Button onClick={() => setMostrandoNuevo(true)}>
                            <Plus className="size-4" />
                            Nuevo asiento N° {siguienteNumero}
                        </Button>
                    )}
                </div>

                {/* Tabs de cuaderno */}
                <div className="flex flex-wrap gap-2 border-b border-border">
                    {cuadernos.map((c) => (
                        <button
                            key={c.value}
                            type="button"
                            onClick={() => cambiarTipo(c.value)}
                            className={
                                'relative flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium transition-colors ' +
                                (c.value === tipoActivo
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground')
                            }
                        >
                            <NotebookPen className="size-4" />
                            {c.label}
                            <Badge variant="secondary" className="ml-1 text-[10px]">
                                {c.total}
                            </Badge>
                        </button>
                    ))}
                </div>

                {/* Toggle vista */}
                <div className="flex items-center justify-between">
                    <div className="inline-flex overflow-hidden rounded-md border border-border">
                        <button
                            type="button"
                            onClick={() => setVista('lista')}
                            className={
                                'flex items-center gap-1.5 px-3 py-1.5 text-sm transition-colors ' +
                                (vista === 'lista'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted')
                            }
                        >
                            <ListIcon className="size-4" />
                            Lista
                        </button>
                        <button
                            type="button"
                            onClick={() => setVista('calendario')}
                            className={
                                'flex items-center gap-1.5 border-l border-border px-3 py-1.5 text-sm transition-colors ' +
                                (vista === 'calendario'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted')
                            }
                        >
                            <CalendarIcon className="size-4" />
                            Calendario
                        </button>
                    </div>
                    <div className="text-xs text-muted-foreground">
                        {asientos.length}{' '}
                        {asientos.length === 1 ? 'asiento' : 'asientos'} en{' '}
                        {cuadernoActivo?.label_corto}
                    </div>
                </div>

                {asientos.length === 0 ? (
                    <Card className="p-12 text-center">
                        <NotebookPen className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <h3 className="mb-1 text-lg font-semibold">
                            Sin asientos todavía
                        </h3>
                        <p className="mx-auto max-w-md text-sm text-muted-foreground">
                            Sube el PDF descargado de OSCE como respaldo de cada
                            asiento del {cuadernoActivo?.label_corto.toLowerCase()}.
                            La numeración se asigna automáticamente.
                        </p>
                        {puedeEscribir && (
                            <Button
                                className="mt-4"
                                onClick={() => setMostrandoNuevo(true)}
                            >
                                <Plus className="size-4" />
                                Registrar primer asiento
                            </Button>
                        )}
                    </Card>
                ) : vista === 'lista' ? (
                    <div className="space-y-2">
                        {asientos.map((a) => (
                            <Card
                                key={a.id}
                                className="cursor-pointer p-4 transition-shadow hover:shadow-md"
                                onClick={() => setSeleccionado(a)}
                            >
                                <CardContent className="flex flex-col gap-3 p-0 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="flex gap-3">
                                        <div className="flex size-12 shrink-0 flex-col items-center justify-center rounded-md bg-primary/10 text-primary">
                                            <span className="text-[9px] font-semibold tracking-wide uppercase">
                                                N°
                                            </span>
                                            <span className="text-base font-bold leading-none tabular-nums">
                                                {a.numero}
                                            </span>
                                        </div>
                                        <div className="min-w-0 flex-1 space-y-1">
                                            <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                <CalendarIcon className="size-3" />
                                                {a.fecha}
                                                {a.autor && (
                                                    <>
                                                        <span>·</span>
                                                        <User className="size-3" />
                                                        {a.autor}
                                                    </>
                                                )}
                                            </div>
                                            <p className="line-clamp-2 text-sm">
                                                {a.contenido}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex shrink-0 items-center gap-2">
                                        {a.tiene_archivo ? (
                                            <Badge
                                                variant="secondary"
                                                className="gap-1"
                                            >
                                                <FileText className="size-3" />
                                                {a.archivo_tamano_humano}
                                            </Badge>
                                        ) : (
                                            <Badge
                                                variant="outline"
                                                className="text-muted-foreground"
                                            >
                                                Sin PDF
                                            </Badge>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                ) : (
                    <Card className="p-4">
                        <CalendarioAsientos
                            asientos={asientos.map((a) => ({
                                id: a.id,
                                numero: a.numero,
                                fecha: a.fecha,
                                contenido: a.contenido,
                                tiene_archivo: a.tiene_archivo,
                            }))}
                            onSeleccionarAsiento={abrirAsiento}
                        />
                    </Card>
                )}
            </div>

            <NuevoAsientoDialog
                open={mostrandoNuevo}
                onOpenChange={setMostrandoNuevo}
                obraId={obra.id}
                tipo={tipoActivo}
                tipoLabel={cuadernoActivo?.label ?? ''}
                siguienteNumero={siguienteNumero}
            />

            <AsientoDetalleModal
                asiento={seleccionado}
                obraId={obra.id}
                tipoLabel={cuadernoActivo?.label ?? ''}
                puedeEliminar={puedeEliminar}
                onClose={() => setSeleccionado(null)}
            />
        </>
    );
}

CuadernoIndex.layout = {
    title: 'Cuaderno de Obra',
    description: 'Asientos del supervisor y del residente, respaldados por el PDF de OSCE.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Cuaderno', href: '' },
    ],
};
