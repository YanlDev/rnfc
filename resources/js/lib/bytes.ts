/**
 * Formatea bytes a una cadena legible (B, KB, MB, GB).
 * Para tamaños calculados en el cliente (p. ej. archivos en el dropzone antes
 * de subirlos). Los recursos ya persistidos llegan formateados del servidor.
 */
export function formatearBytes(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 ** 2) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    if (bytes < 1024 ** 3) {
        return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
    }

    return `${(bytes / 1024 ** 3).toFixed(2)} GB`;
}
