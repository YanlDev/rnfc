import { TriangleAlert } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type OpcionesConfirm = {
    titulo: string;
    descripcion?: ReactNode;
    confirmar?: string;
    cancelar?: string;
    destructivo?: boolean;
};

/**
 * Reemplazo accesible y estilizado de `window.confirm`.
 *
 * Uso:
 *   const { confirm, dialog } = useConfirm();
 *   const eliminar = async () => {
 *       if (!(await confirm({ titulo: '¿Eliminar?', destructivo: true }))) return;
 *       router.delete(...);
 *   };
 *   return <>{...}{dialog}</>;
 */
export function useConfirm() {
    const [opts, setOpts] = useState<OpcionesConfirm | null>(null);
    const resolver = useRef<((valor: boolean) => void) | null>(null);

    const confirm = useCallback(
        (o: OpcionesConfirm) =>
            new Promise<boolean>((resolve) => {
                resolver.current = resolve;
                setOpts(o);
            }),
        [],
    );

    const cerrar = (valor: boolean) => {
        resolver.current?.(valor);
        resolver.current = null;
        setOpts(null);
    };

    const dialog = (
        <Dialog
            open={!!opts}
            onOpenChange={(abierto) => !abierto && cerrar(false)}
        >
            <DialogContent className="sm:max-w-md">
                <DialogHeader className="items-center gap-3 sm:flex-row sm:items-start">
                    {opts?.destructivo && (
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-full bg-destructive/10">
                            <TriangleAlert className="size-5 text-destructive" />
                        </div>
                    )}
                    <div className="space-y-1.5">
                        <DialogTitle className="leading-snug">
                            {opts?.titulo}
                        </DialogTitle>
                        {opts?.descripcion && (
                            <DialogDescription asChild>
                                <div>{opts.descripcion}</div>
                            </DialogDescription>
                        )}
                    </div>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={() => cerrar(false)}>
                        {opts?.cancelar ?? 'Cancelar'}
                    </Button>
                    <Button
                        variant={opts?.destructivo ? 'destructive' : 'default'}
                        onClick={() => cerrar(true)}
                    >
                        {opts?.confirmar ?? 'Confirmar'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );

    return { confirm, dialog };
}
