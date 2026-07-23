import { useEffect, useState } from 'react';

/**
 * Celdas editables tipo Excel: inputs sin borde que confirman el cambio al
 * salir de la celda (blur) o con Enter. Escape descarta.
 */

const claseCelda =
    'w-full bg-transparent px-2 py-1.5 text-sm outline-none transition-colors ' +
    'focus:bg-primary/5 focus:ring-1 focus:ring-primary/40 rounded-sm ' +
    'disabled:cursor-default';

type CeldaTextoProps = {
    valor: string;
    onConfirmar: (valor: string) => void;
    deshabilitado?: boolean;
    placeholder?: string;
    lista?: string;
    className?: string;
    alineado?: 'left' | 'right';
};

export function CeldaTexto({
    valor,
    onConfirmar,
    deshabilitado,
    placeholder,
    lista,
    className,
    alineado = 'left',
}: CeldaTextoProps) {
    const [local, setLocal] = useState(valor);

    useEffect(() => setLocal(valor), [valor]);

    const confirmar = () => {
        if (local !== valor) {
            onConfirmar(local);
        }
    };

    return (
        <input
            type="text"
            value={local}
            list={lista}
            placeholder={placeholder}
            disabled={deshabilitado}
            onChange={(e) => setLocal(e.target.value)}
            onBlur={confirmar}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    e.currentTarget.blur();
                }
                if (e.key === 'Escape') {
                    setLocal(valor);
                    e.currentTarget.blur();
                }
            }}
            className={
                claseCelda +
                (alineado === 'right' ? ' text-right tabular-nums' : '') +
                (className ? ` ${className}` : '')
            }
        />
    );
}

type CeldaFechaProps = {
    valor: string;
    onConfirmar: (valor: string) => void;
    deshabilitado?: boolean;
    max?: string;
};

export function CeldaFecha({
    valor,
    onConfirmar,
    deshabilitado,
    max,
}: CeldaFechaProps) {
    const [local, setLocal] = useState(valor);

    useEffect(() => setLocal(valor), [valor]);

    return (
        <input
            type="date"
            value={local}
            max={max}
            disabled={deshabilitado}
            onChange={(e) => setLocal(e.target.value)}
            onBlur={() => {
                if (local && local !== valor) {
                    onConfirmar(local);
                }
            }}
            className={claseCelda + ' tabular-nums'}
        />
    );
}

type CeldaMontoProps = {
    valor: number;
    onConfirmar: (valor: number) => void;
    deshabilitado?: boolean;
};

export function CeldaMonto({
    valor,
    onConfirmar,
    deshabilitado,
}: CeldaMontoProps) {
    const [local, setLocal] = useState(valor.toFixed(2));

    useEffect(() => setLocal(valor.toFixed(2)), [valor]);

    const confirmar = () => {
        const n = parseFloat(local);
        if (Number.isFinite(n) && n > 0 && n !== valor) {
            onConfirmar(n);
        } else {
            setLocal(valor.toFixed(2));
        }
    };

    return (
        <input
            type="number"
            step="0.01"
            min="0.01"
            inputMode="decimal"
            value={local}
            disabled={deshabilitado}
            onChange={(e) => setLocal(e.target.value)}
            onBlur={confirmar}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    e.currentTarget.blur();
                }
                if (e.key === 'Escape') {
                    setLocal(valor.toFixed(2));
                    e.currentTarget.blur();
                }
            }}
            className={claseCelda + ' text-right tabular-nums'}
        />
    );
}

type Opcion = { value: string; label: string };

type CeldaSelectProps = {
    valor: string;
    opciones: Opcion[];
    onConfirmar: (valor: string) => void;
    deshabilitado?: boolean;
    placeholder?: string;
};

export function CeldaSelect({
    valor,
    opciones,
    onConfirmar,
    deshabilitado,
    placeholder,
}: CeldaSelectProps) {
    return (
        <select
            value={valor}
            disabled={deshabilitado}
            onChange={(e) => onConfirmar(e.target.value)}
            className={claseCelda + ' appearance-none'}
        >
            {placeholder !== undefined && (
                <option value="" disabled>
                    {placeholder}
                </option>
            )}
            {opciones.map((o) => (
                <option key={o.value} value={o.value}>
                    {o.label}
                </option>
            ))}
        </select>
    );
}
