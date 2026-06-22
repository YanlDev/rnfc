import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import type { LinkPaginacion, MetaPaginacion } from '@/types';

type Props = {
    meta?: MetaPaginacion;
    links?: LinkPaginacion[];
};

/**
 * Barra de paginación estándar para listados paginados por Laravel.
 * Centraliza el bloque que estaba duplicado en obras/usuarios/certificados.
 */
export default function Paginacion({ meta, links }: Props) {
    if (!meta || meta.last_page <= 1 || !links) {
        return null;
    }

    return (
        <div className="flex items-center justify-between text-sm text-muted-foreground">
            <div>
                {meta.from ?? 0}–{meta.to ?? 0} de {meta.total}
            </div>
            <div className="flex gap-1">
                {links.map((l) => (
                    <Button
                        key={l.label}
                        size="sm"
                        variant={l.active ? 'default' : 'outline'}
                        disabled={!l.url}
                        onClick={() =>
                            l.url &&
                            router.visit(l.url, { preserveScroll: true })
                        }
                        // Las labels vienen de Laravel («Anterior», «1», «»»), no de usuarios.
                        dangerouslySetInnerHTML={{ __html: l.label }}
                    />
                ))}
            </div>
        </div>
    );
}
