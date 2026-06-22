import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

/**
 * Badge de estado de obra, estilo ejecutivo: fondo tenue + punto de color.
 * Fuente única de verdad para los colores de estado en toda la app.
 */
const ESTADO_BADGE: Record<string, string> = {
    planificacion:
        'bg-slate-100 text-slate-700 dark:bg-slate-400/10 dark:text-slate-300',
    en_ejecucion:
        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    paralizada:
        'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    finalizada:
        'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    archivada:
        'bg-slate-100 text-slate-500 dark:bg-slate-400/10 dark:text-slate-400',
};

const ESTADO_DOT: Record<string, string> = {
    planificacion: 'bg-slate-400',
    en_ejecucion: 'bg-emerald-500',
    paralizada: 'bg-amber-500',
    finalizada: 'bg-blue-500',
    archivada: 'bg-slate-400',
};

export function EstadoObraBadge({
    estado,
    label,
    className,
}: {
    estado: string;
    label: string;
    className?: string;
}) {
    return (
        <Badge
            className={cn(
                'gap-1.5 border-0 px-2.5 py-1 text-[11px] font-semibold',
                ESTADO_BADGE[estado] ?? ESTADO_BADGE.planificacion,
                className,
            )}
        >
            <span
                aria-hidden
                className={cn(
                    'size-1.5 rounded-full',
                    ESTADO_DOT[estado] ?? 'bg-slate-400',
                )}
            />
            {label}
        </Badge>
    );
}
