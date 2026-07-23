// Subida de documentos de obra con dos caminos según el tamaño:
//   - Hasta 50 MB: subida directa (un solo POST multipart).
//   - Más de 50 MB: subida en trozos (chunks) reensamblada en el servidor.
//
// La validación de tipo/tamaño la impone el backend; aquí replicamos la lista
// de extensiones sólo para dar feedback inmediato antes de gastar ancho de banda.

import { subirEnChunks } from './chunked-upload';
import type { FaseSubida } from './chunked-upload';

// Umbral directo/chunks. Debe coincidir con `max:51200` (KB) de
// DocumentoController@store y @storeVersion.
export const LIMITE_SUBIDA_DIRECTA = 50 * 1024 * 1024; // 50 MB

// Espejo de App\Support\TipoDocumento::EXTENSIONES (sin el punto).
export const EXTENSIONES_PERMITIDAS = [
    'pdf',
    'jpg',
    'jpeg',
    'png',
    'webp',
    'gif',
    'dwg',
    'dxf',
    'doc',
    'docx',
    'xls',
    'xlsx',
    'ppt',
    'pptx',
    'txt',
    'csv',
    'zip',
    'rar',
    '7z',
] as const;

export function extensionDe(nombre: string): string {
    const punto = nombre.lastIndexOf('.');

    return punto >= 0 ? nombre.slice(punto + 1).toLowerCase() : '';
}

export function extensionPermitida(nombre: string): boolean {
    return (EXTENSIONES_PERMITIDAS as readonly string[]).includes(
        extensionDe(nombre),
    );
}

export const ACCEPT_ATTR = EXTENSIONES_PERMITIDAS.map((e) => `.${e}`).join(',');

type Opciones = {
    obraId: number;
    file: File;
    carpetaId?: number;
    documentoId?: number;
    onProgreso?: (pct: number) => void;
    onFase?: (fase: FaseSubida) => void;
    signal?: AbortSignal;
};

function leerCookie(nombre: string): string | null {
    const match = document.cookie.match(
        new RegExp('(^|;\\s*)' + nombre + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[2]) : null;
}

/**
 * Subida directa (≤ 50 MB) vía XHR para poder reportar progreso real.
 * Resuelve cuando el servidor creó el Documento; rechaza con el mensaje de
 * validación (p. ej. tipo no permitido) en caso de error.
 */
function subirDirecto(opts: Opciones): Promise<void> {
    const { obraId, file, carpetaId, documentoId, onProgreso, onFase, signal } =
        opts;

    const url =
        documentoId != null
            ? `/obras/${obraId}/documentos/${documentoId}/version`
            : `/obras/${obraId}/carpetas/${carpetaId}/documentos`;

    return new Promise<void>((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url);
        xhr.withCredentials = true;
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        const xsrf = leerCookie('XSRF-TOKEN');

        if (xsrf) {
            xhr.setRequestHeader('X-XSRF-TOKEN', xsrf);
        }

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                onProgreso?.(Math.round((e.loaded / e.total) * 100));
            }
        };

        xhr.onload = () => {
            // back() responde 302 → XHR lo sigue y termina en 200 (éxito).
            if (xhr.status >= 200 && xhr.status < 400) {
                onProgreso?.(100);
                resolve();

                return;
            }

            let mensaje = `Error ${xhr.status}`;

            try {
                const data = JSON.parse(xhr.responseText);
                mensaje = data.message || mensaje;
            } catch {
                /* respuesta no-JSON */
            }

            reject(new Error(mensaje));
        };

        xhr.onerror = () =>
            reject(new Error('Error de red al subir el archivo.'));
        xhr.onabort = () => reject(new Error('Subida cancelada.'));

        if (signal) {
            signal.addEventListener('abort', () => xhr.abort(), { once: true });
        }

        const form = new FormData();
        form.append('archivo', file);
        onFase?.('subiendo');
        xhr.send(form);
    });
}

/**
 * Punto de entrada único: elige subida directa o en trozos según el tamaño.
 */
export async function subirDocumento(opts: Opciones): Promise<number | null> {
    if (!extensionPermitida(opts.file.name)) {
        throw new Error(
            `Tipo de archivo no permitido: .${extensionDe(opts.file.name) || '?'}`,
        );
    }

    if (opts.file.size <= LIMITE_SUBIDA_DIRECTA) {
        opts.onFase?.('iniciando');
        await subirDirecto(opts);
        opts.onFase?.('completado');

        return null;
    }

    return subirEnChunks(opts);
}
