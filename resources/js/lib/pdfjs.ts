import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

/**
 * Carga perezosa de pdf.js (Mozilla) con su worker configurado para Vite.
 * Se importa dinámicamente para no engordar el bundle principal: sólo se
 * descarga cuando hay un PDF que renderizar.
 */
export async function cargarPdfjs() {
    const pdfjs = await import('pdfjs-dist');

    pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;

    return pdfjs;
}
