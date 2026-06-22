/**
 * Contratos de paginación de Laravel reutilizados por todas las páginas.
 * Antes cada página redefinía su propio `Paginado<T>` / `Paginated<T>`.
 */
export type LinkPaginacion = {
    url: string | null;
    label: string;
    active: boolean;
};

export type MetaPaginacion = {
    current_page: number;
    last_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
};

export type Paginado<T> = {
    data: T[];
    links?: LinkPaginacion[];
    meta?: MetaPaginacion;
};
