import { router } from '@inertiajs/react';
import { Calendar, Download, FileText, Trash2, User, X } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

export type AsientoDetalle = {
    id: number;
    numero: number;
    fecha: string;
    contenido: string;
    autor: string | null;
    tiene_archivo: boolean;
    es_pdf: boolean;
    archivo_nombre: string | null;
    archivo_mime: string | null;
    archivo_tamano_humano: string;
    url_preview: string | null;
    url_descarga: string | null;
};

type Props = {
    asiento: AsientoDetalle | null;
    obraId: number;
    tipoLabel: string;
    puedeEliminar: boolean;
    onClose: () => void;
};

export default function AsientoDetalleModal({
    asiento,
    obraId,
    tipoLabel,
    puedeEliminar,
    onClose,
}: Props) {
    if (!asiento) return null;

    const eliminar = () => {
        if (
            !confirm(
                `¿Eliminar el asiento N° ${asiento.numero}? Esto deja un registro de auditoría pero oculta el contenido.`,
            )
        ) {
            return;
        }
        router.delete(`/obras/${obraId}/cuaderno/${asiento.id}`, {
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <div
            className="fixed inset-0 z-50 flex flex-col bg-black/80 backdrop-blur-sm"
            onClick={onClose}
        >
            <div
                className="flex items-center justify-between gap-3 border-b border-white/10 bg-black/40 px-4 py-3 text-white"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2 text-sm font-semibold">
                        <Badge className="bg-primary text-primary-foreground">
                            N° {asiento.numero}
                        </Badge>
                        <span>{tipoLabel}</span>
                    </div>
                    <div className="mt-0.5 flex items-center gap-3 text-[11px] text-white/60">
                        <span className="flex items-center gap-1">
                            <Calendar className="size-3" />
                            {asiento.fecha}
                        </span>
                        {asiento.autor && (
                            <span className="flex items-center gap-1">
                                <User className="size-3" />
                                {asiento.autor}
                            </span>
                        )}
                        {asiento.tiene_archivo && (
                            <span className="flex items-center gap-1">
                                <FileText className="size-3" />
                                {asiento.archivo_tamano_humano}
                            </span>
                        )}
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    {asiento.url_descarga && (
                        <Button asChild variant="secondary" size="sm">
                            <a href={asiento.url_descarga}>
                                <Download className="size-4" />
                                Descargar
                            </a>
                        </Button>
                    )}
                    {puedeEliminar && (
                        <Button variant="destructive" size="sm" onClick={eliminar}>
                            <Trash2 className="size-4" />
                        </Button>
                    )}
                    <button
                        onClick={onClose}
                        className="rounded-md p-2 text-white/80 hover:bg-white/10 hover:text-white"
                    >
                        <X className="size-5" />
                    </button>
                </div>
            </div>

            <div
                className="grid flex-1 gap-4 overflow-hidden p-4 lg:grid-cols-[1fr_2fr]"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="overflow-auto rounded-md bg-card p-4">
                    <h3 className="mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                        Resumen del asiento
                    </h3>
                    <p className="text-sm leading-relaxed whitespace-pre-line">
                        {asiento.contenido}
                    </p>
                </div>

                <div className="overflow-hidden rounded-md bg-card">
                    {!asiento.tiene_archivo ? (
                        <div className="flex h-full flex-col items-center justify-center gap-2 p-8 text-muted-foreground">
                            <FileText className="size-10" />
                            <p className="text-sm">
                                Este asiento no tiene PDF adjunto.
                            </p>
                        </div>
                    ) : asiento.es_pdf ? (
                        <iframe
                            src={asiento.url_preview ?? ''}
                            title={asiento.archivo_nombre ?? 'PDF'}
                            className="size-full border-0"
                        />
                    ) : (
                        <div className="flex h-full items-center justify-center bg-muted/20 p-4">
                            <img
                                src={asiento.url_preview ?? ''}
                                alt={asiento.archivo_nombre ?? ''}
                                className="max-h-full max-w-full rounded-md object-contain"
                            />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
