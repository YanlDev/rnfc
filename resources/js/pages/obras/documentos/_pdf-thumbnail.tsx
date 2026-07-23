import { useEffect, useRef, useState } from 'react';
import { cargarPdfjs } from '@/lib/pdfjs';

/** No renderizamos miniaturas de PDFs enormes: costaría descargarlos enteros. */
const MAX_BYTES_THUMBNAIL = 20 * 1024 * 1024; // 20 MB

type Props = {
    url: string;
    tamano: number;
    /** Se muestra mientras carga o si el render falla (icono del tipo). */
    fallback: React.ReactNode;
};

/**
 * Miniatura de la primera página de un PDF, renderizada en el navegador con
 * pdf.js. Carga perezosa: sólo cuando la tarjeta entra al viewport.
 */
export default function PdfThumbnail({ url, tamano, fallback }: Props) {
    const contenedorRef = useRef<HTMLDivElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const [estado, setEstado] = useState<
        'espera' | 'cargando' | 'ok' | 'error'
    >('espera');

    // Dispara la carga cuando la tarjeta se hace visible.
    useEffect(() => {
        if (tamano > MAX_BYTES_THUMBNAIL) {
            setEstado('error');
            return;
        }

        const el = contenedorRef.current;
        if (!el) return;

        const observer = new IntersectionObserver(
            (entradas) => {
                if (entradas[0]?.isIntersecting) {
                    setEstado('cargando');
                    observer.disconnect();
                }
            },
            { rootMargin: '300px' },
        );

        observer.observe(el);

        return () => observer.disconnect();
    }, [tamano]);

    useEffect(() => {
        if (estado !== 'cargando') return;

        let cancelado = false;

        (async () => {
            try {
                const pdfjs = await cargarPdfjs();
                const tarea = pdfjs.getDocument({ url });
                const doc = await tarea.promise;
                const pagina = await doc.getPage(1);

                const canvas = canvasRef.current;
                if (!canvas || cancelado) {
                    void tarea.destroy();
                    return;
                }

                // Escala para un ancho de ~360px (nítido en la tarjeta).
                const base = pagina.getViewport({ scale: 1 });
                const viewport = pagina.getViewport({
                    scale: 360 / base.width,
                });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                await pagina.render({
                    canvas,
                    viewport,
                }).promise;

                void tarea.destroy();

                if (!cancelado) setEstado('ok');
            } catch {
                if (!cancelado) setEstado('error');
            }
        })();

        return () => {
            cancelado = true;
        };
    }, [estado, url]);

    return (
        <div
            ref={contenedorRef}
            className="flex size-full items-center justify-center"
        >
            {estado !== 'ok' && fallback}
            <canvas
                ref={canvasRef}
                className={
                    estado === 'ok'
                        ? 'size-full bg-white object-cover object-top'
                        : 'hidden'
                }
            />
        </div>
    );
}
