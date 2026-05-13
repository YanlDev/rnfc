import { router } from '@inertiajs/react';
import {
    Download,
    Eye,
    File,
    FileImage,
    FileSpreadsheet,
    FileText,
    History,
    Trash2,
    Upload,
} from 'lucide-react';
import { useRef } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import type { DocumentoPreview } from './_preview-modal';

export type DocumentoCardData = DocumentoPreview & {
    subido_por: string | null;
    updated_at: string;
};

type Props = {
    documento: DocumentoCardData;
    obraId: number;
    puedeAdministrar: boolean;
    onPreview: () => void;
};

function IconoArchivo({ mime, className }: { mime: string; className: string }) {
    if (mime.startsWith('image/')) return <FileImage className={className} />;
    if (mime === 'application/pdf') return <FileText className={className} />;
    if (
        mime.includes('spreadsheet') ||
        mime.includes('excel') ||
        mime.includes('csv')
    )
        return <FileSpreadsheet className={className} />;
    if (mime.includes('word') || mime.includes('document'))
        return <FileText className={className} />;
    return <File className={className} />;
}

export default function DocumentoCard({
    documento,
    obraId,
    puedeAdministrar,
    onPreview,
}: Props) {
    const versionInputRef = useRef<HTMLInputElement>(null);

    const subirVersion = (file: File) => {
        const form = new FormData();
        form.append('archivo', file);
        router.post(
            `/obras/${obraId}/documentos/${documento.id}/version`,
            form,
            {
                forceFormData: true,
                preserveScroll: true,
            },
        );
    };

    const eliminar = () => {
        if (!confirm(`¿Eliminar «${documento.nombre}» y todas sus versiones?`)) return;
        router.delete(`/obras/${obraId}/documentos/${documento.id}`, {
            preserveScroll: true,
        });
    };

    return (
        <Card className="group overflow-hidden p-0 transition-all hover:shadow-md">
            {/* Thumbnail / preview */}
            <button
                type="button"
                onClick={onPreview}
                className="relative flex aspect-video w-full items-center justify-center overflow-hidden bg-muted/40"
            >
                {documento.es_imagen ? (
                    <img
                        src={documento.url_preview}
                        alt={documento.nombre}
                        className="size-full object-cover transition-transform group-hover:scale-105"
                        loading="lazy"
                    />
                ) : (
                    <IconoArchivo
                        mime={documento.mime}
                        className="size-14 text-muted-foreground transition-transform group-hover:scale-110"
                    />
                )}
                <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/30">
                    <Eye className="size-8 text-white opacity-0 transition-opacity group-hover:opacity-100" />
                </div>
                {documento.version > 1 && (
                    <Badge className="absolute top-2 right-2 bg-[var(--color-brand-azul-oscuro)] text-white">
                        v{documento.version}
                    </Badge>
                )}
            </button>

            {/* Info */}
            <div className="space-y-2 p-3">
                <div
                    className="line-clamp-2 cursor-pointer text-sm font-medium hover:text-primary"
                    onClick={onPreview}
                    title={documento.nombre}
                >
                    {documento.nombre}
                </div>
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                    <span className="tabular-nums">{documento.tamano_humano}</span>
                    <span>{new Date(documento.updated_at).toLocaleDateString('es-PE')}</span>
                </div>
                {documento.subido_por && (
                    <div className="text-xs text-muted-foreground">
                        Por {documento.subido_por}
                    </div>
                )}

                {/* Actions */}
                <div className="flex items-center justify-between gap-1 border-t border-border pt-2">
                    <div className="flex gap-0.5">
                        <Button
                            asChild
                            size="sm"
                            variant="ghost"
                            title="Descargar"
                        >
                            <a href={documento.url_descarga}>
                                <Download className="size-4" />
                            </a>
                        </Button>
                        {puedeAdministrar && (
                            <>
                                <input
                                    ref={versionInputRef}
                                    type="file"
                                    className="hidden"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0];
                                        if (file) subirVersion(file);
                                        e.target.value = '';
                                    }}
                                />
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    onClick={() => versionInputRef.current?.click()}
                                    title="Subir nueva versión"
                                >
                                    <Upload className="size-4" />
                                </Button>
                                {documento.version > 1 && (
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        title={`Tiene ${documento.version - 1} versión(es) anterior(es)`}
                                    >
                                        <History className="size-4 text-muted-foreground" />
                                    </Button>
                                )}
                            </>
                        )}
                    </div>
                    {puedeAdministrar && (
                        <Button
                            size="sm"
                            variant="ghost"
                            onClick={eliminar}
                            title="Eliminar"
                        >
                            <Trash2 className="size-4 text-destructive" />
                        </Button>
                    )}
                </div>
            </div>
        </Card>
    );
}
