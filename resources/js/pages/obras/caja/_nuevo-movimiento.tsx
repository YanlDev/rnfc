import { router, useForm } from '@inertiajs/react';
import { Save, Upload, X } from 'lucide-react';
import { useRef } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

export type CategoriaOpcion = { value: string; label: string };

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    obraId: number;
    categorias: CategoriaOpcion[];
};

function tamanoHumano(bytes: number) {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 ** 2) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
}

export default function NuevoMovimientoDialog({
    open,
    onOpenChange,
    obraId,
    categorias,
}: Props) {
    const hoy = new Date().toISOString().slice(0, 10);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const form = useForm<{
        tipo: 'ingreso' | 'egreso';
        monto: string;
        categoria: string;
        descripcion: string;
        fecha: string;
        comprobante: File | null;
    }>({
        tipo: 'egreso',
        monto: '',
        categoria: '',
        descripcion: '',
        fecha: hoy,
        comprobante: null,
    });

    const esEgreso = form.data.tipo === 'egreso';

    const guardar = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(`/obras/${obraId}/caja`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.setData('fecha', hoy);
                onOpenChange(false);
                router.reload({ only: ['movimientos', 'resumen'] });
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <form onSubmit={guardar}>
                    <DialogHeader>
                        <DialogTitle>Nuevo movimiento de caja</DialogTitle>
                        <DialogDescription>
                            Registra un ingreso (asignación de fondos) o un
                            egreso (gasto). Puedes adjuntar la boleta o factura.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        {/* Tipo */}
                        <div className="grid grid-cols-2 gap-2">
                            {(['ingreso', 'egreso'] as const).map((t) => (
                                <button
                                    key={t}
                                    type="button"
                                    onClick={() => form.setData('tipo', t)}
                                    className={
                                        'rounded-md border px-3 py-2 text-sm font-medium capitalize transition-colors ' +
                                        (form.data.tipo === t
                                            ? t === 'ingreso'
                                                ? 'border-emerald-500 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'
                                                : 'border-rose-500 bg-rose-500/10 text-rose-700 dark:text-rose-400'
                                            : 'border-border text-muted-foreground hover:bg-muted/40')
                                    }
                                >
                                    {t}
                                </button>
                            ))}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="monto">Monto (S/) *</Label>
                                <Input
                                    id="monto"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    inputMode="decimal"
                                    value={form.data.monto}
                                    onChange={(e) =>
                                        form.setData('monto', e.target.value)
                                    }
                                    placeholder="0.00"
                                />
                                <InputError message={form.errors.monto} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="fecha">Fecha *</Label>
                                <Input
                                    id="fecha"
                                    type="date"
                                    max={hoy}
                                    value={form.data.fecha}
                                    onChange={(e) =>
                                        form.setData('fecha', e.target.value)
                                    }
                                />
                                <InputError message={form.errors.fecha} />
                            </div>
                        </div>

                        {esEgreso && (
                            <div className="grid gap-2">
                                <Label>Categoría *</Label>
                                <Select
                                    value={form.data.categoria}
                                    onValueChange={(v) =>
                                        form.setData('categoria', v)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Elige una categoría" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {categorias.map((c) => (
                                            <SelectItem
                                                key={c.value}
                                                value={c.value}
                                            >
                                                {c.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={form.errors.categoria} />
                            </div>
                        )}

                        <div className="grid gap-2">
                            <Label htmlFor="descripcion">Descripción *</Label>
                            <Input
                                id="descripcion"
                                value={form.data.descripcion}
                                onChange={(e) =>
                                    form.setData('descripcion', e.target.value)
                                }
                                placeholder="Concepto del movimiento"
                            />
                            <InputError message={form.errors.descripcion} />
                        </div>

                        <div className="grid gap-2">
                            <Label>Comprobante (opcional)</Label>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="application/pdf,image/jpeg,image/png,image/webp"
                                className="hidden"
                                onChange={(e) =>
                                    form.setData(
                                        'comprobante',
                                        e.target.files?.[0] ?? null,
                                    )
                                }
                            />
                            {!form.data.comprobante ? (
                                <button
                                    type="button"
                                    onClick={() =>
                                        fileInputRef.current?.click()
                                    }
                                    className="rounded-lg border-2 border-dashed border-border p-4 text-center transition-colors hover:border-primary/50 hover:bg-muted/30"
                                >
                                    <Upload className="mx-auto mb-1.5 size-5 text-muted-foreground" />
                                    <div className="text-xs text-muted-foreground">
                                        Adjuntar boleta/factura (PDF o imagen,
                                        máx. 50 MB)
                                    </div>
                                </button>
                            ) : (
                                <div className="flex items-center gap-2 rounded-md border border-border bg-muted/30 p-3">
                                    <Upload className="size-4 text-primary" />
                                    <div className="min-w-0 flex-1">
                                        <div className="truncate text-sm font-medium">
                                            {form.data.comprobante.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {tamanoHumano(
                                                form.data.comprobante.size,
                                            )}
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            form.setData('comprobante', null)
                                        }
                                        className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                                    >
                                        <X className="size-4" />
                                    </button>
                                </div>
                            )}
                            <InputError message={form.errors.comprobante} />
                        </div>

                        {form.progress && (
                            <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full bg-primary transition-all"
                                    style={{
                                        width: `${form.progress.percentage ?? 0}%`,
                                    }}
                                />
                            </div>
                        )}
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                            disabled={form.processing}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? (
                                <Spinner />
                            ) : (
                                <Save className="size-4" />
                            )}
                            Registrar
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
