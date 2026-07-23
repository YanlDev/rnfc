import { router } from '@inertiajs/react';
import { Calendar, Download, FileText, Trash2, User, X } from 'lucide-react';
import { useConfirm } from '@/components/confirm-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

export type AsientoDetalle = {
    id: number;
    numero: number;
    tipo_autor: string;
    tipo_label: string;
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
    puedeEliminar: boolean;
    onClose: () => void;
};

export default function AsientoDetalleModal({
    asiento,
    obraId,
    puedeEliminar,
    onClose,
}: Props) {
    const { confirm, dialog } = useConfirm();

    if (!asiento) return null;

    const eliminar = async () => {
        const ok = await confirm({
            titulo: `¿Eliminar el asiento N° ${asiento.numero}?`,
            destructivo: true,
            confirmar: 'Eliminar asiento',
            descripcion:
                'Esto deja un registro de auditoría pero oculta el contenido.',
        });

        if (!ok) {
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
            {dialog}
            <div
                className="flex items-center justify-between gap-3 border-b border-white/10 bg-black/40 px-4 py-3 text-white"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="min-w-0 flex-1">
                    <div className="flex items-center gap-2 text-sm font-semibold">
                        <Badge className="bg-primary text-primary-foreground">
                            N° {asiento.numero}
                        </Badge>
                        <span>{asiento.tipo_label}</span>
                    </div>
                    {/* El contenido es sólo una referencia: el documento manda. */}
                    <p
                        className="mt-0.5 truncate text-xs text-white/70"
                        title={asiento.contenido}
                    >
                        {asiento.contenido}
                    </p>
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
                                <span className="hidden sm:inline">
                                    Descargar
                                </span>
                            </a>
                        </Button>
                    )}
                    {puedeEliminar && (
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={eliminar}
                        >
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

            {/* El documento ocupa todo el espacio disponible. */}
            <div
                className="flex-1 overflow-hidden p-2 sm:p-4"
                onClick={(e) => e.stopPropagation()}
            >
                {!asiento.tiene_archivo ? (
                    <div className="mx-auto flex h-full max-w-2xl flex-col items-center justify-center gap-4 rounded-md bg-card p-8">
                        <FileText className="size-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            Este asiento no tiene documento adjunto. Contenido
                            registrado:
                        </p>
                        <p className="max-h-full overflow-auto text-sm leading-relaxed whitespace-pre-line">
                            {asiento.contenido}
                        </p>
                    </div>
                ) : asiento.es_pdf ? (
                    <iframe
                        src={asiento.url_preview ?? ''}
                        title={asiento.archivo_nombre ?? 'PDF'}
                        className="size-full rounded-md border-0 bg-card"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center overflow-auto rounded-md bg-card p-4">
                        <img
                            src={asiento.url_preview ?? ''}
                            alt={asiento.archivo_nombre ?? ''}
                            className="max-h-full max-w-full rounded-md object-contain"
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
