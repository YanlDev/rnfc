import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

type Opciones = {
    /** Valores que deben tratarse como "sin filtro" y omitirse de la query. */
    vacios?: string[];
    /** Milisegundos de debounce antes de navegar. */
    debounce?: number;
};

/**
 * Maneja filtros de listado sincronizados con la URL vía Inertia.
 *
 * Centraliza el patrón (antes duplicado en obras/usuarios): debounce +
 * `router.get` con `preserveState/Scroll/replace`. Evita el request redundante
 * en el montaje (sólo navega cuando el usuario cambia un filtro).
 */
export function useFiltrosUrl<T extends Record<string, string>>(
    url: string,
    iniciales: T,
    { vacios = ['todos', ''], debounce = 250 }: Opciones = {},
) {
    const [filtros, setFiltros] = useState<T>(iniciales);
    const primeraVez = useRef(true);

    const set = (clave: keyof T, valor: string) =>
        setFiltros((prev) => ({ ...prev, [clave]: valor }));

    useEffect(() => {
        // No dispares una petición en el primer render: los datos ya vienen
        // del servidor con estos mismos filtros.
        if (primeraVez.current) {
            primeraVez.current = false;

            return;
        }

        const t = setTimeout(() => {
            const params = Object.fromEntries(
                Object.entries(filtros).map(([k, v]) => [
                    k,
                    vacios.includes(v) ? undefined : v,
                ]),
            );
            router.get(url, params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, debounce);

        return () => clearTimeout(t);
        // `vacios`/`url` son estables; sólo reaccionamos a cambios de filtros.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filtros]);

    return { filtros, set, setFiltros };
}
