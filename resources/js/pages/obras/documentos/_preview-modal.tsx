import { Download, X } from 'lucide-react';
import { Button } from '@/components/ui/button';

export type DocumentoPreview = {
    id: number;
    nombre: string;
    mime: string;
    tamano_humano: string;
    version: number;
    es_imagen: boolean;
    es_pdf: boolean;
    url_preview: string;
    url_descarga: string;
};

type Props = {
    documento: DocumentoPreview | null;
    onClose: () => void;
};

export default function PreviewModal({ documento, onClose }: Props) {
    if (!documento) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex flex-col bg-black/80 backdrop-blur-sm"
            onClick={onClose}
        >
            {/* Top bar */}
            <div
                className="flex items-center justify-between gap-3 border-b border-white/10 bg-black/40 px-4 py-3 text-white"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-semibold">
                        {documento.nombre}
                    </div>
                    <div className="text-[11px] text-white/60">
                        v{documento.version} · {documento.tamano_humano} · {documento.mime}
                    </div>
                </div>
                <div className="flex gap-2">
                    <Button asChild variant="secondary" size="sm">
                        <a href={documento.url_descarga}>
                            <Download className="size-4" />
                            Descargar
                        </a>
                    </Button>
                    <button
                        onClick={onClose}
                        className="rounded-md p-2 text-white/80 hover:bg-white/10 hover:text-white"
                        aria-label="Cerrar"
                    >
                        <X className="size-5" />
                    </button>
                </div>
            </div>

            {/* Content */}
            <div
                className="flex flex-1 items-center justify-center overflow-auto p-4"
                onClick={(e) => e.stopPropagation()}
            >
                {documento.es_imagen ? (
                    <img
                        src={documento.url_preview}
                        alt={documento.nombre}
                        className="max-h-full max-w-full rounded-md shadow-2xl"
                    />
                ) : documento.es_pdf ? (
                    <iframe
                        src={documento.url_preview}
                        title={documento.nombre}
                        className="h-full w-full max-w-5xl rounded-md border-0 bg-white shadow-2xl"
                    />
                ) : (
                    <div className="max-w-md rounded-lg bg-card p-8 text-center">
                        <p className="text-sm text-muted-foreground">
                            Este tipo de archivo no se puede previsualizar en línea.
                        </p>
                        <Button asChild className="mt-4">
                            <a href={documento.url_descarga}>
                                <Download className="size-4" />
                                Descargar archivo
                            </a>
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}
