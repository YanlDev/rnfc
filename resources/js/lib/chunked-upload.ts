// Subida de archivos grandes (planos de varios GB) en trozos.
//
// El navegador parte el archivo con File.slice() y manda cada trozo por
// separado; el server los reensambla y los empuja a Bunny desde una cola.
// Así ningún request supera el tamaño de un trozo (~8 MB).

export type FaseSubida =
    | 'iniciando'
    | 'subiendo'
    | 'procesando'
    | 'completado'
    | 'error';

type Opciones = {
    obraId: number;
    file: File;
    carpetaId?: number;
    documentoId?: number;
    onProgreso?: (pct: number) => void;
    onFase?: (fase: FaseSubida) => void;
    signal?: AbortSignal;
};

type IniciarResp = {
    token: string;
    chunk_size: number;
    total_chunks: number;
};

function leerCookie(nombre: string): string | null {
    const match = document.cookie.match(
        new RegExp('(^|;\\s*)' + nombre + '=([^;]*)'),
    );

    return match ? decodeURIComponent(match[2]) : null;
}

function cabecerasBase(): Record<string, string> {
    const xsrf = leerCookie('XSRF-TOKEN');

    return {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(xsrf ? { 'X-XSRF-TOKEN': xsrf } : {}),
    };
}

async function postJson<T>(
    url: string,
    body: unknown,
    signal?: AbortSignal,
): Promise<T> {
    const res = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { ...cabecerasBase(), 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
        signal,
    });

    if (!res.ok) {
        throw new Error(await mensajeError(res));
    }

    return res.json() as Promise<T>;
}

async function mensajeError(res: Response): Promise<string> {
    try {
        const data = await res.json();

        return data.message || `Error ${res.status}`;
    } catch {
        return `Error ${res.status}`;
    }
}

async function subirChunk(
    token: string,
    index: number,
    blob: Blob,
    signal?: AbortSignal,
): Promise<void> {
    let intento = 0;

    // Reintentos ante cortes de red puntuales.
    while (true) {
        try {
            const res = await fetch(`/uploads/${token}/chunk?index=${index}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    ...cabecerasBase(),
                    'Content-Type': 'application/octet-stream',
                },
                body: blob,
                signal,
            });

            if (!res.ok) {
                throw new Error(await mensajeError(res));
            }

            return;
        } catch (e) {
            if (signal?.aborted) {
                throw e;
            }

            if (++intento >= 3) {
                throw e;
            }

            await new Promise((r) => setTimeout(r, 1000 * intento));
        }
    }
}

const espera = (ms: number) => new Promise((r) => setTimeout(r, ms));

/**
 * Sube un archivo en trozos y resuelve cuando el server terminó de procesarlo
 * (subir a Bunny + crear el Documento). Lanza si falla en cualquier fase.
 */
export async function subirEnChunks(opts: Opciones): Promise<number | null> {
    const { obraId, file, carpetaId, documentoId, onProgreso, onFase, signal } =
        opts;

    onFase?.('iniciando');
    const init = await postJson<IniciarResp>(
        `/obras/${obraId}/uploads`,
        {
            nombre: file.name,
            tamano: file.size,
            carpeta_id: carpetaId ?? null,
            documento_id: documentoId ?? null,
        },
        signal,
    );

    onFase?.('subiendo');

    for (let i = 0; i < init.total_chunks; i++) {
        const inicio = i * init.chunk_size;
        const fin = Math.min(inicio + init.chunk_size, file.size);
        await subirChunk(init.token, i, file.slice(inicio, fin), signal);
        onProgreso?.(Math.round(((i + 1) / init.total_chunks) * 100));
    }

    await postJson(`/uploads/${init.token}/completar`, {}, signal);

    // Polling del estado mientras el Job sube a Bunny.
    onFase?.('procesando');

    while (true) {
        if (signal?.aborted) {
            throw new Error('Subida cancelada.');
        }

        await espera(2000);

        const res = await fetch(`/uploads/${init.token}/estado`, {
            credentials: 'same-origin',
            headers: cabecerasBase(),
            signal,
        });

        if (!res.ok) {
            continue;
        } // reintenta en el siguiente ciclo

        const estado = (await res.json()) as {
            estado: FaseSubida;
            error: string | null;
            documento_id: number | null;
        };

        if (estado.estado === 'completado') {
            onFase?.('completado');

            return estado.documento_id;
        }

        if (estado.estado === 'error') {
            throw new Error(estado.error || 'No se pudo procesar el archivo.');
        }
    }
}
