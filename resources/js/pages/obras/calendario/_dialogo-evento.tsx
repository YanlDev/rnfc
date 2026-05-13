import { router, useForm } from '@inertiajs/react';
import { CalendarPlus, Save, Trash2 } from 'lucide-react';
import { useEffect } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
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
import { Textarea } from '@/components/ui/textarea';

export type TipoEventoOpcion = {
    value: string;
    label: string;
    color: string;
};

export type EventoEditable = {
    id: number;
    tipo: string;
    titulo: string;
    descripcion: string | null;
    fecha_inicio_iso: string | null;
    fecha_fin_iso: string | null;
    todo_el_dia: boolean;
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    obraId: number;
    tipos: TipoEventoOpcion[];
    evento: EventoEditable | null; // null = crear, EventoEditable = editar
    fechaInicial?: string | null; // para pre-llenar al crear desde click en día
};

export default function DialogoEvento({
    open,
    onOpenChange,
    obraId,
    tipos,
    evento,
    fechaInicial,
}: Props) {
    const esEdicion = evento !== null;

    const form = useForm({
        tipo: 'hito',
        titulo: '',
        descripcion: '',
        fecha_inicio: '',
        fecha_fin: '',
        todo_el_dia: true,
    });

    useEffect(() => {
        if (open) {
            if (evento) {
                form.setData({
                    tipo: evento.tipo,
                    titulo: evento.titulo,
                    descripcion: evento.descripcion ?? '',
                    fecha_inicio: evento.fecha_inicio_iso ?? '',
                    fecha_fin: evento.fecha_fin_iso ?? '',
                    todo_el_dia: evento.todo_el_dia,
                });
            } else {
                form.reset();
                form.setData({
                    tipo: 'hito',
                    titulo: '',
                    descripcion: '',
                    fecha_inicio:
                        fechaInicial ?? new Date().toISOString().slice(0, 10),
                    fecha_fin: '',
                    todo_el_dia: true,
                });
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, evento?.id, fechaInicial]);

    const guardar = (e: React.FormEvent) => {
        e.preventDefault();
        if (esEdicion && evento) {
            form.patch(`/obras/${obraId}/calendario/${evento.id}`, {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        } else {
            form.post(`/obras/${obraId}/calendario`, {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });
        }
    };

    const eliminar = () => {
        if (!evento) return;
        if (!confirm('¿Eliminar este evento?')) return;
        router.delete(`/obras/${obraId}/calendario/${evento.id}`, {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    const agregarAGoogleCalendar = () => {
        if (!evento) return;
        const inicio = form.data.fecha_inicio;
        if (!inicio) return;

        const fmt = (iso: string) => iso.replace(/-/g, ''); // YYYYMMDD
        const inicioFmt = fmt(inicio);
        let fechas: string;

        if (form.data.todo_el_dia) {
            // Google espera fin exclusivo: sumar 1 día al fin (o al inicio si no hay fin)
            const fin = new Date((form.data.fecha_fin || inicio) + 'T00:00:00');
            fin.setDate(fin.getDate() + 1);
            const finFmt =
                `${fin.getFullYear()}` +
                `${String(fin.getMonth() + 1).padStart(2, '0')}` +
                `${String(fin.getDate()).padStart(2, '0')}`;
            fechas = `${inicioFmt}/${finFmt}`;
        } else {
            const finFmt = fmt(form.data.fecha_fin || inicio);
            fechas = `${inicioFmt}T090000/${finFmt}T100000`;
        }

        const params = new URLSearchParams({
            action: 'TEMPLATE',
            text: form.data.titulo || 'Evento RNFC',
            dates: fechas,
        });
        if (form.data.descripcion) params.set('details', form.data.descripcion);

        window.open(
            `https://calendar.google.com/calendar/render?${params.toString()}`,
            '_blank',
            'noopener,noreferrer',
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <form onSubmit={guardar}>
                    <DialogHeader>
                        <DialogTitle>
                            {esEdicion ? 'Editar evento' : 'Nuevo evento'}
                        </DialogTitle>
                    </DialogHeader>

                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="tipo">Tipo *</Label>
                            <Select
                                value={form.data.tipo}
                                onValueChange={(v) => form.setData('tipo', v)}
                            >
                                <SelectTrigger id="tipo">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {tipos.map((t) => (
                                        <SelectItem key={t.value} value={t.value}>
                                            <span className="flex items-center gap-2">
                                                <span
                                                    className="inline-block size-2.5 rounded-full"
                                                    style={{
                                                        backgroundColor: t.color,
                                                    }}
                                                />
                                                {t.label}
                                            </span>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.tipo} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="titulo">Título *</Label>
                            <Input
                                id="titulo"
                                value={form.data.titulo}
                                onChange={(e) =>
                                    form.setData('titulo', e.target.value)
                                }
                                placeholder="Ej. Entrega de planos as-built"
                                autoFocus
                            />
                            <InputError message={form.errors.titulo} />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="grid gap-2">
                                <Label htmlFor="fecha_inicio">Inicio *</Label>
                                <Input
                                    id="fecha_inicio"
                                    type="date"
                                    value={form.data.fecha_inicio}
                                    onChange={(e) =>
                                        form.setData('fecha_inicio', e.target.value)
                                    }
                                />
                                <InputError message={form.errors.fecha_inicio} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="fecha_fin">
                                    Fin (opcional)
                                </Label>
                                <Input
                                    id="fecha_fin"
                                    type="date"
                                    value={form.data.fecha_fin}
                                    onChange={(e) =>
                                        form.setData('fecha_fin', e.target.value)
                                    }
                                />
                                <InputError message={form.errors.fecha_fin} />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="descripcion">
                                Descripción (opcional)
                            </Label>
                            <Textarea
                                id="descripcion"
                                rows={3}
                                value={form.data.descripcion}
                                onChange={(e) =>
                                    form.setData('descripcion', e.target.value)
                                }
                            />
                            <InputError message={form.errors.descripcion} />
                        </div>
                    </div>

                    <DialogFooter className="sm:justify-between">
                        <div className="flex flex-wrap gap-1">
                            {esEdicion && (
                                <>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        onClick={eliminar}
                                        disabled={form.processing}
                                    >
                                        <Trash2 className="size-4 text-destructive" />
                                        Eliminar
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        onClick={agregarAGoogleCalendar}
                                        disabled={form.processing || !form.data.fecha_inicio}
                                        title="Abre Google Calendar con los datos del evento ya cargados"
                                    >
                                        <CalendarPlus className="size-4 text-primary" />
                                        Agregar a Google Calendar
                                    </Button>
                                </>
                            )}
                        </div>
                        <div className="flex gap-2">
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
                                {esEdicion ? 'Guardar cambios' : 'Crear'}
                            </Button>
                        </div>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
