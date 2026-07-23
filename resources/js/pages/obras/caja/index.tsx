import { Head, Link } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowLeft,
    ArrowUpCircle,
    Wallet,
} from 'lucide-react';
import { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import obras from '@/routes/obras';
import Alquileres from './_alquileres';
import TablaDepositos from './_tabla-depositos';
import TablaGastos from './_tabla-gastos';

type ObraResumen = { id: number; codigo: string; nombre: string };

export type Opcion = { value: string; label: string };

export type Movimiento = {
    id: number;
    tipo: 'ingreso' | 'egreso';
    tipo_comprobante: string | null;
    numero_comprobante: string | null;
    proveedor: string | null;
    metodo: string | null;
    monto: number;
    descripcion: string;
    fecha: string | null;
    registrado_por: string | null;
    tiene_comprobante: boolean;
    url_comprobante: string | null;
    created_at: string | null;
};

export type AlquilerPago = {
    id: number;
    periodo: string; // YYYY-MM
    fecha_pago: string;
    monto: number;
};

export type Alquiler = {
    id: number;
    inquilino: string;
    arrendador: string | null;
    monto_mensual: number;
    forma_pago: string;
    forma_pago_label: string;
    fecha_inicio: string;
    activo: boolean;
    pagos: AlquilerPago[];
};

type Resumen = {
    ingresos: number;
    egresos: number;
    saldo: number;
    por_comprobante: Record<string, number>;
};

type Props = {
    obra: ObraResumen;
    movimientos: Movimiento[];
    resumen: Resumen;
    alquileres: Alquiler[];
    proveedores: string[];
    tiposComprobante: Opcion[];
    metodos: Opcion[];
    formasPago: Opcion[];
    puedeRegistrar: boolean;
    puedeGestionar: boolean;
};

const soles = (n: number) =>
    new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    }).format(n);

const PESTANAS = [
    { id: 'gastos', label: 'Gastos' },
    { id: 'depositos', label: 'Depósitos' },
    { id: 'alquileres', label: 'Alquileres' },
] as const;

type Pestana = (typeof PESTANAS)[number]['id'];

export default function CajaIndex({
    obra,
    movimientos,
    resumen,
    alquileres,
    proveedores,
    tiposComprobante,
    metodos,
    formasPago,
    puedeRegistrar,
    puedeGestionar,
}: Props) {
    const [pestana, setPestana] = useState<Pestana>('gastos');

    const gastos = movimientos.filter((m) => m.tipo === 'egreso');
    const depositos = movimientos.filter((m) => m.tipo === 'ingreso');

    const subtotales = tiposComprobante
        .map((t) => ({
            label: `${t.label}s`,
            monto: resumen.por_comprobante[t.value] ?? 0,
        }))
        .filter((s) => s.monto > 0);

    return (
        <>
            <Head title={`Caja chica · ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <Link
                    href={obras.show(obra.id).url}
                    className="inline-flex items-center gap-1 self-start text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-3.5" />
                    Volver a la obra
                </Link>

                {/* Resumen */}
                <div className="grid gap-3 sm:grid-cols-3">
                    <Card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <Wallet className="size-5" />
                            </span>
                            <div>
                                <div className="text-xs text-muted-foreground">
                                    Saldo actual
                                </div>
                                <div
                                    className={
                                        'text-xl font-semibold tabular-nums ' +
                                        (resumen.saldo < 0
                                            ? 'text-rose-600 dark:text-rose-400'
                                            : '')
                                    }
                                >
                                    {soles(resumen.saldo)}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <ArrowUpCircle className="size-5" />
                            </span>
                            <div>
                                <div className="text-xs text-muted-foreground">
                                    Depósitos
                                </div>
                                <div className="text-xl font-semibold tabular-nums">
                                    {soles(resumen.ingresos)}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                <ArrowDownCircle className="size-5" />
                            </span>
                            <div className="min-w-0">
                                <div className="text-xs text-muted-foreground">
                                    Gastos
                                </div>
                                <div className="text-xl font-semibold tabular-nums">
                                    {soles(resumen.egresos)}
                                </div>
                                {subtotales.length > 0 && (
                                    <div className="truncate text-[11px] text-muted-foreground">
                                        {subtotales
                                            .map(
                                                (s) =>
                                                    `${s.label} ${soles(s.monto)}`,
                                            )
                                            .join(' · ')}
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Pestañas */}
                <div className="flex gap-1 border-b border-border">
                    {PESTANAS.map((p) => (
                        <button
                            key={p.id}
                            type="button"
                            onClick={() => setPestana(p.id)}
                            className={
                                '-mb-px rounded-t-md border-b-2 px-4 py-2 text-sm font-medium transition-colors ' +
                                (pestana === p.id
                                    ? 'border-primary text-foreground'
                                    : 'border-transparent text-muted-foreground hover:text-foreground')
                            }
                        >
                            {p.label}
                            {p.id === 'alquileres' &&
                                alquileres.length > 0 &&
                                ` (${alquileres.length})`}
                        </button>
                    ))}
                </div>

                <Card className="overflow-hidden p-0">
                    <CardContent className="p-0">
                        {pestana === 'gastos' && (
                            <TablaGastos
                                obraId={obra.id}
                                gastos={gastos}
                                tiposComprobante={tiposComprobante}
                                proveedores={proveedores}
                                puedeRegistrar={puedeRegistrar}
                                puedeGestionar={puedeGestionar}
                            />
                        )}
                        {pestana === 'depositos' && (
                            <TablaDepositos
                                obraId={obra.id}
                                depositos={depositos}
                                metodos={metodos}
                                puedeRegistrar={puedeRegistrar}
                                puedeGestionar={puedeGestionar}
                            />
                        )}
                        {pestana === 'alquileres' && (
                            <div className="p-4">
                                <Alquileres
                                    obraId={obra.id}
                                    alquileres={alquileres}
                                    formasPago={formasPago}
                                    puedeRegistrar={puedeRegistrar}
                                    puedeGestionar={puedeGestionar}
                                />
                            </div>
                        )}
                    </CardContent>
                </Card>

                {puedeRegistrar && pestana !== 'alquileres' && (
                    <p className="text-xs text-muted-foreground">
                        Completa la fila «Agregar» y pulsa el botón (o Enter). Para
                        corregir algo, haz clic sobre el dato y escribe encima.
                    </p>
                )}
            </div>
        </>
    );
}

CajaIndex.layout = {
    title: 'Caja chica',
    description: 'Rendición de gastos, depósitos y alquileres de la obra.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Caja chica', href: '' },
    ],
};
