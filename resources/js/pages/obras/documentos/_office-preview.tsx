import { Download, Loader2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';

/** Filas máximas por hoja al previsualizar un Excel (evita congelar el DOM). */
const MAX_FILAS = 500;

export type TipoOffice = 'docx' | 'hoja';

/** Tipo de visor Office según la extensión, o null si no hay visor. */
export function tipoOffice(nombre: string): TipoOffice | null {
    const ext = (nombre.split('.').pop() ?? '').toLowerCase();

    if (ext === 'docx') return 'docx';
    if (['xlsx', 'xls', 'csv'].includes(ext)) return 'hoja';

    return null;
}

type Props = {
    tipo: TipoOffice;
    url: string;
    nombre: string;
    urlDescarga: string;
};

type Hoja = { nombre: string; html: string };

/**
 * Previsualización en el navegador de documentos Office, 100% local:
 * - .docx con docx-preview (render fiel de Word).
 * - .xlsx/.xls/.csv con SheetJS → tabla HTML por hoja.
 * El archivo nunca sale del servidor propio (a diferencia de los visores
 * embebidos de Google/Microsoft).
 */
export default function OfficePreview({
    tipo,
    url,
    nombre,
    urlDescarga,
}: Props) {
    const docxRef = useRef<HTMLDivElement>(null);
    const [estado, setEstado] = useState<'cargando' | 'ok' | 'error'>(
        'cargando',
    );
    const [hojas, setHojas] = useState<Hoja[]>([]);
    const [hojaActiva, setHojaActiva] = useState(0);

    useEffect(() => {
        let cancelado = false;

        (async () => {
            try {
                const respuesta = await fetch(url, {
                    credentials: 'same-origin',
                });
                if (!respuesta.ok) throw new Error('descarga');
                const buffer = await respuesta.arrayBuffer();
                if (cancelado) return;

                if (tipo === 'docx') {
                    const { renderAsync } = await import('docx-preview');
                    if (cancelado || !docxRef.current) return;

                    await renderAsync(buffer, docxRef.current, undefined, {
                        inWrapper: true,
                        ignoreLastRenderedPageBreak: false,
                    });
                } else {
                    const XLSX = await import('xlsx');
                    const libro = XLSX.read(buffer, { dense: true });

                    const resultado: Hoja[] = libro.SheetNames.map(
                        (nombreHoja) => {
                            const hoja = libro.Sheets[nombreHoja];

                            // Recorta hojas gigantes: sólo las primeras filas.
                            if (hoja['!ref']) {
                                const rango = XLSX.utils.decode_range(
                                    hoja['!ref'],
                                );
                                if (rango.e.r > MAX_FILAS) {
                                    rango.e.r = MAX_FILAS;
                                    hoja['!ref'] =
                                        XLSX.utils.encode_range(rango);
                                }
                            }

                            return {
                                nombre: nombreHoja,
                                html: XLSX.utils.sheet_to_html(hoja),
                            };
                        },
                    );

                    if (cancelado) return;
                    setHojas(resultado);
                }

                if (!cancelado) setEstado('ok');
            } catch {
                if (!cancelado) setEstado('error');
            }
        })();

        return () => {
            cancelado = true;
        };
    }, [tipo, url]);

    if (estado === 'error') {
        return (
            <div className="max-w-md rounded-lg bg-card p-8 text-center">
                <p className="text-sm text-muted-foreground">
                    No se pudo previsualizar «{nombre}».
                </p>
                <Button asChild className="mt-4">
                    <a href={urlDescarga}>
                        <Download className="size-4" />
                        Descargar archivo
                    </a>
                </Button>
            </div>
        );
    }

    return (
        <div className="flex h-full w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white shadow-2xl">
            {estado === 'cargando' && (
                <div className="flex flex-1 items-center justify-center gap-2 text-sm text-gray-500">
                    <Loader2 className="size-4 animate-spin" />
                    Preparando vista previa…
                </div>
            )}

            {tipo === 'docx' ? (
                <div
                    ref={docxRef}
                    className={
                        estado === 'ok'
                            ? 'docx-preview-wrap flex-1 overflow-auto bg-gray-100'
                            : 'hidden'
                    }
                />
            ) : (
                estado === 'ok' && (
                    <>
                        {hojas.length > 1 && (
                            <div className="flex shrink-0 gap-1 overflow-x-auto border-b border-gray-200 bg-gray-50 px-2 pt-2">
                                {hojas.map((h, i) => (
                                    <button
                                        key={h.nombre}
                                        type="button"
                                        onClick={() => setHojaActiva(i)}
                                        className={`rounded-t-md px-3 py-1.5 text-xs font-medium whitespace-nowrap ${
                                            i === hojaActiva
                                                ? 'border border-b-0 border-gray-200 bg-white text-gray-900'
                                                : 'text-gray-500 hover:text-gray-900'
                                        }`}
                                    >
                                        {h.nombre}
                                    </button>
                                ))}
                            </div>
                        )}
                        <div
                            className="hoja-preview flex-1 overflow-auto p-2 text-gray-900"
                            /* HTML generado localmente por SheetJS a partir de
                               celdas escapadas; no incluye scripts. */
                            dangerouslySetInnerHTML={{
                                __html: hojas[hojaActiva]?.html ?? '',
                            }}
                        />
                    </>
                )
            )}
        </div>
    );
}
