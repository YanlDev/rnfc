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
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    obraId: number;
    tipo: string;
    tipoLabel: string;
    siguienteNumero: number;
};

function tamanoHumano(bytes: number) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 ** 2) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
}

export default function NuevoAsientoDialog({
    open,
    onOpenChange,
    obraId,
    tipo,
    tipoLabel,
    siguienteNumero,
}: Props) {
    const hoy = new Date().toISOString().slice(0, 10);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const form = useForm<{
        tipo_autor: string;
        fecha: string;
        contenido: string;
        archivo: File | null;
    }>({
        tipo_autor: tipo,
        fecha: hoy,
        contenido: '',
        archivo: null,
    });

    const guardar = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(`/obras/${obraId}/cuaderno`, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.setData('tipo_autor', tipo);
                form.setData('fecha', hoy);
                onOpenChange(false);
                router.reload({ only: ['asientos', 'siguienteNumero', 'cuadernos'] });
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-2xl">
                <form onSubmit={guardar}>
                    <DialogHeader>
                        <DialogTitle>
                            Nuevo asiento N° {siguienteNumero} ·{' '}
                            <span className="text-primary">{tipoLabel}</span>
                        </DialogTitle>
                        <DialogDescription>
                            Adjunta el PDF descargado de OSCE y registra la fecha
                            y un resumen del contenido del asiento.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="fecha">Fecha del asiento *</Label>
                            <Input
                                id="fecha"
                                type="date"
                                value={form.data.fecha}
                                onChange={(e) =>
                                    form.setData('fecha', e.target.value)
                                }
                                className="sm:max-w-xs"
                            />
                            <InputError message={form.errors.fecha} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="contenido">Resumen del asiento *</Label>
                            <Textarea
                                id="contenido"
                                rows={4}
                                value={form.data.contenido}
                                onChange={(e) =>
                                    form.setData('contenido', e.target.value)
                                }
                                placeholder="Describe brevemente lo registrado (avances, observaciones, paralizaciones…)"
                            />
                            <InputError message={form.errors.contenido} />
                        </div>

                        <div className="grid gap-2">
                            <Label>PDF de OSCE (opcional pero recomendado)</Label>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="application/pdf,image/jpeg,image/png"
                                className="hidden"
                                onChange={(e) =>
                                    form.setData(
                                        'archivo',
                                        e.target.files?.[0] ?? null,
                                    )
                                }
                            />
                            {!form.data.archivo ? (
                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="rounded-lg border-2 border-dashed border-border p-6 text-center transition-colors hover:border-primary/50 hover:bg-muted/30"
                                >
                                    <Upload className="mx-auto mb-2 size-6 text-muted-foreground" />
                                    <div className="text-sm">
                                        <strong className="text-foreground">
                                            Adjuntar archivo
                                        </strong>{' '}
                                        <span className="text-muted-foreground">
                                            (PDF o imagen, máx. 50 MB)
                                        </span>
                                    </div>
                                </button>
                            ) : (
                                <div className="flex items-center gap-2 rounded-md border border-border bg-muted/30 p-3">
                                    <Upload className="size-4 text-primary" />
                                    <div className="min-w-0 flex-1">
                                        <div className="truncate text-sm font-medium">
                                            {form.data.archivo.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {tamanoHumano(form.data.archivo.size)}
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            form.setData('archivo', null)
                                        }
                                        className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                                    >
                                        <X className="size-4" />
                                    </button>
                                </div>
                            )}
                            <InputError message={form.errors.archivo} />
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
                            Guardar asiento
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
