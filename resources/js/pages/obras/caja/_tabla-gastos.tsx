import { router } from '@inertiajs/react';
import { Paperclip, Plus, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { CeldaFecha, CeldaMonto, CeldaSelect, CeldaTexto } from './_celdas';
import type { Movimiento, Opcion } from './index';

type Props = {
    obraId: number;
    gastos: Movimiento[];
    tiposComprobante: Opcion[];
    proveedores: string[];
    puedeRegistrar: boolean;
    puedeGestionar: boolean;
};

const soles = (n: number) =>
    new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    }).format(n);

const recargar = () =>
    router.reload({ only: ['movimientos', 'resumen', 'proveedores'] });

export default function TablaGastos({
    obraId,
    gastos,
    tiposComprobante,
    proveedores,
    puedeRegistrar,
    puedeGestionar,
}: Props) {
    const hoy = new Date().toISOString().slice(0, 10);
    const puedeEditar = puedeRegistrar || puedeGestionar;

    // Fila de entrada, siempre lista al final — como la siguiente fila de un Excel.
    const filaVacia = {
        fecha: hoy,
        tipo_comprobante: 'boleta',
        proveedor: '',
        descripcion: '',
        monto: '',
    };
    const [nueva, setNueva] = useState(filaVacia);
    const [guardando, setGuardando] = useState(false);
    const primeraCeldaRef = useRef<HTMLInputElement>(null);
    const archivoRef = useRef<HTMLInputElement>(null);
    const [movimientoAdjunto, setMovimientoAdjunto] = useState<number | null>(
        null,
    );

    const nuevaCompleta =
        nueva.fecha !== '' &&
        nueva.descripcion.trim() !== '' &&
        parseFloat(nueva.monto) > 0;

    const guardarNueva = () => {
        if (!nuevaCompleta || guardando) {
            return;
        }

        setGuardando(true);
        router.post(
            `/obras/${obraId}/caja`,
            {
                tipo: 'egreso',
                fecha: nueva.fecha,
                tipo_comprobante: nueva.tipo_comprobante,
                proveedor: nueva.proveedor.trim() || null,
                descripcion: nueva.descripcion.trim(),
                monto: nueva.monto,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setNueva({ ...filaVacia, fecha: nueva.fecha });
                    recargar();
                    primeraCeldaRef.current?.focus();
                },
                onFinish: () => setGuardando(false),
            },
        );
    };

    const actualizar = (
        m: Movimiento,
        campo: string,
        valor: string | number | null,
    ) => {
        const datos: Record<string, string | number | null> = {
            [campo]: valor,
        };
        router.patch(`/obras/${obraId}/caja/${m.id}`, datos, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    const eliminar = (m: Movimiento) => {
        if (!confirm(`¿Eliminar el gasto «${m.descripcion}»?`)) {
            return;
        }

        router.delete(`/obras/${obraId}/caja/${m.id}`, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    const adjuntar = (movimientoId: number) => {
        setMovimientoAdjunto(movimientoId);
        archivoRef.current?.click();
    };

    const subirArchivo = (archivo: File | null) => {
        if (!archivo || movimientoAdjunto === null) {
            return;
        }

        router.post(
            `/obras/${obraId}/caja/${movimientoAdjunto}/comprobante`,
            { comprobante: archivo },
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: recargar,
            },
        );

        if (archivoRef.current) {
            archivoRef.current.value = '';
        }
        setMovimientoAdjunto(null);
    };

    return (
        <div className="overflow-x-auto">
            {/* input oculto para adjuntar comprobantes por fila */}
            <input
                ref={archivoRef}
                type="file"
                accept="application/pdf,image/jpeg,image/png,image/webp"
                className="hidden"
                onChange={(e) => subirArchivo(e.target.files?.[0] ?? null)}
            />
            <datalist id="proveedores-obra">
                {proveedores.map((p) => (
                    <option key={p} value={p} />
                ))}
            </datalist>

            <table className="w-full min-w-[720px] border-collapse text-sm">
                <thead>
                    <tr className="border-b border-border text-left text-xs uppercase tracking-wide text-muted-foreground">
                        <th className="w-36 px-2 py-2 font-medium">Fecha</th>
                        <th className="w-28 px-2 py-2 font-medium">Comprob.</th>
                        <th className="w-44 px-2 py-2 font-medium">
                            Proveedor
                        </th>
                        <th className="px-2 py-2 font-medium">Motivo</th>
                        <th className="w-28 px-2 py-2 text-right font-medium">
                            Monto S/
                        </th>
                        <th className="w-20 px-2 py-2" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-border/60">
                    {gastos.map((m) => (
                        <tr key={m.id} className="group hover:bg-muted/30">
                            <td>
                                <CeldaFecha
                                    valor={m.fecha ?? ''}
                                    max={hoy}
                                    deshabilitado={!puedeEditar}
                                    onConfirmar={(v) =>
                                        actualizar(m, 'fecha', v)
                                    }
                                />
                            </td>
                            <td>
                                <CeldaSelect
                                    valor={m.tipo_comprobante ?? ''}
                                    opciones={tiposComprobante}
                                    deshabilitado={!puedeEditar}
                                    placeholder="—"
                                    onConfirmar={(v) =>
                                        actualizar(m, 'tipo_comprobante', v)
                                    }
                                />
                            </td>
                            <td>
                                <CeldaTexto
                                    valor={m.proveedor ?? ''}
                                    lista="proveedores-obra"
                                    deshabilitado={!puedeEditar}
                                    onConfirmar={(v) =>
                                        actualizar(m, 'proveedor', v || null)
                                    }
                                />
                            </td>
                            <td>
                                <CeldaTexto
                                    valor={m.descripcion}
                                    deshabilitado={!puedeEditar}
                                    onConfirmar={(v) => {
                                        if (v.trim() !== '') {
                                            actualizar(
                                                m,
                                                'descripcion',
                                                v.trim(),
                                            );
                                        }
                                    }}
                                />
                            </td>
                            <td>
                                <CeldaMonto
                                    valor={m.monto}
                                    deshabilitado={!puedeEditar}
                                    onConfirmar={(v) =>
                                        actualizar(m, 'monto', v)
                                    }
                                />
                            </td>
                            <td>
                                <div className="flex items-center justify-end gap-0.5 pr-1">
                                    {m.tiene_comprobante && m.url_comprobante ? (
                                        <a
                                            href={m.url_comprobante}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded p-1 text-primary hover:bg-muted"
                                            title="Ver comprobante"
                                        >
                                            <Paperclip className="size-4" />
                                        </a>
                                    ) : (
                                        puedeEditar && (
                                            <button
                                                type="button"
                                                onClick={() => adjuntar(m.id)}
                                                className="rounded p-1 text-muted-foreground/40 opacity-0 transition-opacity hover:bg-muted hover:text-foreground group-hover:opacity-100"
                                                title="Adjuntar comprobante"
                                            >
                                                <Paperclip className="size-4" />
                                            </button>
                                        )
                                    )}
                                    {puedeGestionar && (
                                        <button
                                            type="button"
                                            onClick={() => eliminar(m)}
                                            className="rounded p-1 text-muted-foreground/40 opacity-0 transition-opacity hover:bg-muted hover:text-destructive group-hover:opacity-100"
                                            title="Eliminar"
                                        >
                                            <Trash2 className="size-4" />
                                        </button>
                                    )}
                                </div>
                            </td>
                        </tr>
                    ))}

                    {/* Fila de entrada */}
                    {puedeRegistrar && (
                        <tr className="bg-primary/[0.03]">
                            <td>
                                <input
                                    ref={primeraCeldaRef}
                                    type="date"
                                    value={nueva.fecha}
                                    max={hoy}
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            fecha: e.target.value,
                                        })
                                    }
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-sm tabular-nums outline-none focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <select
                                    value={nueva.tipo_comprobante}
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            tipo_comprobante: e.target.value,
                                        })
                                    }
                                    className="w-full appearance-none rounded-sm bg-transparent px-2 py-1.5 text-sm outline-none focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                >
                                    {tiposComprobante.map((t) => (
                                        <option key={t.value} value={t.value}>
                                            {t.label}
                                        </option>
                                    ))}
                                </select>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    value={nueva.proveedor}
                                    list="proveedores-obra"
                                    placeholder="Proveedor…"
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            proveedor: e.target.value,
                                        })
                                    }
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            guardarNueva();
                                        }
                                    }}
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-sm outline-none placeholder:text-muted-foreground/50 focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <input
                                    type="text"
                                    value={nueva.descripcion}
                                    placeholder="¿En qué se gastó?"
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            descripcion: e.target.value,
                                        })
                                    }
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            guardarNueva();
                                        }
                                    }}
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-sm outline-none placeholder:text-muted-foreground/50 focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    inputMode="decimal"
                                    value={nueva.monto}
                                    placeholder="0.00"
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            monto: e.target.value,
                                        })
                                    }
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            guardarNueva();
                                        }
                                    }}
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-right text-sm tabular-nums outline-none placeholder:text-muted-foreground/50 focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <div className="flex justify-end pr-1">
                                    <button
                                        type="button"
                                        onClick={guardarNueva}
                                        disabled={!nuevaCompleta || guardando}
                                        className="rounded p-1 text-primary transition-opacity disabled:opacity-25"
                                        title="Agregar (Enter)"
                                    >
                                        <Plus className="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    )}
                </tbody>
                {gastos.length > 0 && (
                    <tfoot>
                        <tr className="border-t border-border text-sm font-semibold">
                            <td
                                colSpan={4}
                                className="px-2 py-2 text-right text-xs uppercase tracking-wide text-muted-foreground"
                            >
                                Total gastos
                            </td>
                            <td className="px-2 py-2 text-right tabular-nums">
                                {soles(
                                    gastos.reduce((s, m) => s + m.monto, 0),
                                )}
                            </td>
                            <td />
                        </tr>
                    </tfoot>
                )}
            </table>

            {gastos.length === 0 && !puedeRegistrar && (
                <p className="p-6 text-center text-sm text-muted-foreground">
                    Aún no hay gastos registrados.
                </p>
            )}
        </div>
    );
}
