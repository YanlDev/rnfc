import { ChevronLeft, ChevronRight, FileText } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';

type Asiento = {
    id: number;
    numero: number;
    tipo_autor: string;
    fecha: string;
    contenido: string;
    tiene_archivo: boolean;
};

// Colores por cuaderno para distinguirlos dentro del mismo día.
const ESTILO_TIPO: Record<string, { chip: string; punto: string; corto: string }> = {
    supervisor: {
        chip: 'bg-primary/10 text-primary hover:bg-primary/20',
        punto: 'bg-primary',
        corto: 'Sup.',
    },
    residente: {
        chip: 'bg-amber-500/15 text-amber-700 hover:bg-amber-500/25 dark:text-amber-400',
        punto: 'bg-amber-500',
        corto: 'Res.',
    },
};

const estiloTipo = (tipo: string) =>
    ESTILO_TIPO[tipo] ?? ESTILO_TIPO.supervisor;

type Props = {
    asientos: Asiento[];
    onSeleccionarAsiento: (id: number) => void;
};

const MESES = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Setiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

const DIAS_SEMANA = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

export default function CalendarioAsientos({ asientos, onSeleccionarAsiento }: Props) {
    const hoy = new Date();
    const [anio, setAnio] = useState(hoy.getFullYear());
    const [mes, setMes] = useState(hoy.getMonth()); // 0..11

    const asientosPorDia = useMemo(() => {
        const mapa = new Map<string, Asiento[]>();
        for (const a of asientos) {
            const lista = mapa.get(a.fecha) ?? [];
            lista.push(a);
            mapa.set(a.fecha, lista);
        }
        return mapa;
    }, [asientos]);

    const primerDia = new Date(anio, mes, 1);
    const ultimoDia = new Date(anio, mes + 1, 0);
    const numDias = ultimoDia.getDate();
    // Lunes = 0 según nuestro grid
    const offsetInicial = (primerDia.getDay() + 6) % 7;

    const celdas: (number | null)[] = [];
    for (let i = 0; i < offsetInicial; i++) celdas.push(null);
    for (let d = 1; d <= numDias; d++) celdas.push(d);
    // Completar al múltiplo de 7
    while (celdas.length % 7 !== 0) celdas.push(null);

    const irMesAnterior = () => {
        if (mes === 0) {
            setMes(11);
            setAnio(anio - 1);
        } else {
            setMes(mes - 1);
        }
    };
    const irMesSiguiente = () => {
        if (mes === 11) {
            setMes(0);
            setAnio(anio + 1);
        } else {
            setMes(mes + 1);
        }
    };
    const irHoy = () => {
        setMes(hoy.getMonth());
        setAnio(hoy.getFullYear());
    };

    const fechaIso = (dia: number) => {
        const m = String(mes + 1).padStart(2, '0');
        const d = String(dia).padStart(2, '0');
        return `${anio}-${m}-${d}`;
    };

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <h3 className="text-base font-semibold">
                        {MESES[mes]} {anio}
                    </h3>
                    <div className="hidden items-center gap-3 text-[11px] text-muted-foreground sm:flex">
                        <span className="flex items-center gap-1">
                            <span className="size-2 rounded-full bg-primary" />
                            Supervisor
                        </span>
                        <span className="flex items-center gap-1">
                            <span className="size-2 rounded-full bg-amber-500" />
                            Residente
                        </span>
                    </div>
                </div>
                <div className="flex gap-1">
                    <Button size="sm" variant="outline" onClick={irMesAnterior}>
                        <ChevronLeft className="size-4" />
                    </Button>
                    <Button size="sm" variant="outline" onClick={irHoy}>
                        Hoy
                    </Button>
                    <Button size="sm" variant="outline" onClick={irMesSiguiente}>
                        <ChevronRight className="size-4" />
                    </Button>
                </div>
            </div>

            <div className="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold tracking-wide text-muted-foreground uppercase">
                {DIAS_SEMANA.map((d) => (
                    <div key={d} className="py-1">
                        {d}
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-7 gap-1">
                {celdas.map((dia, i) => {
                    if (dia === null) {
                        return <div key={i} className="min-h-[88px]" />;
                    }
                    const iso = fechaIso(dia);
                    const asientosDelDia = asientosPorDia.get(iso) ?? [];
                    const esHoy =
                        anio === hoy.getFullYear() &&
                        mes === hoy.getMonth() &&
                        dia === hoy.getDate();

                    return (
                        <div
                            key={i}
                            className={
                                'flex min-h-[88px] flex-col gap-1 rounded-md border p-1.5 text-left ' +
                                (esHoy
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border')
                            }
                        >
                            <div
                                className={
                                    'text-xs font-semibold ' +
                                    (esHoy ? 'text-primary' : 'text-foreground')
                                }
                            >
                                {dia}
                            </div>
                            <div className="space-y-0.5">
                                {asientosDelDia.slice(0, 3).map((a) => {
                                    const estilo = estiloTipo(a.tipo_autor);

                                    return (
                                        <button
                                            key={a.id}
                                            type="button"
                                            onClick={() =>
                                                onSeleccionarAsiento(a.id)
                                            }
                                            className={`flex w-full items-center gap-1 truncate rounded px-1 py-0.5 text-left text-[10px] font-medium ${estilo.chip}`}
                                            title={`${estilo.corto} N° ${a.numero} · ${a.contenido}`}
                                        >
                                            {a.tiene_archivo && (
                                                <FileText className="size-2.5 shrink-0" />
                                            )}
                                            <span className="truncate">
                                                {estilo.corto} #{a.numero}
                                            </span>
                                        </button>
                                    );
                                })}
                                {asientosDelDia.length > 3 && (
                                    <div className="text-[10px] text-muted-foreground">
                                        +{asientosDelDia.length - 3} más
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
