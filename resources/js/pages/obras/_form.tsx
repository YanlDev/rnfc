import InputError from '@/components/input-error';
import MapaUbicacion from '@/components/mapa-ubicacion';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

export type ObraFormData = {
    codigo: string;
    nombre: string;
    descripcion: string;
    ubicacion: string;
    latitud: string;
    longitud: string;
    entidad_contratante: string;
    monto_contractual: string;
    fecha_inicio: string;
    fecha_fin_prevista: string;
    fecha_fin_real: string;
    estado: string;
};

export type EstadoOpcion = { value: string; label: string };

type Props = {
    data: ObraFormData;
    errors: Partial<Record<keyof ObraFormData, string>>;
    setData: <K extends keyof ObraFormData>(k: K, v: ObraFormData[K]) => void;
    estados: EstadoOpcion[];
};

export default function ObraFormFields({ data, errors, setData, estados }: Props) {
    return (
        <div className="flex flex-col gap-6">
            <Card>
                <CardHeader>
                    <CardTitle>Identificación</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-4 md:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="codigo">Código *</Label>
                        <Input
                            id="codigo"
                            value={data.codigo}
                            onChange={(e) => setData('codigo', e.target.value)}
                            placeholder="OBR-2026-0001"
                        />
                        <InputError message={errors.codigo} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="estado">Estado *</Label>
                        <Select
                            value={data.estado}
                            onValueChange={(v) => setData('estado', v)}
                        >
                            <SelectTrigger id="estado">
                                <SelectValue placeholder="Selecciona el estado" />
                            </SelectTrigger>
                            <SelectContent>
                                {estados.map((e) => (
                                    <SelectItem key={e.value} value={e.value}>
                                        {e.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.estado} />
                    </div>
                    <div className="grid gap-2 md:col-span-2">
                        <Label htmlFor="nombre">Nombre de la obra *</Label>
                        <Input
                            id="nombre"
                            value={data.nombre}
                            onChange={(e) => setData('nombre', e.target.value)}
                            placeholder="Mejoramiento de pistas y veredas — Av. Los Olivos"
                        />
                        <InputError message={errors.nombre} />
                    </div>
                    <div className="grid gap-2 md:col-span-2">
                        <Label htmlFor="descripcion">Descripción</Label>
                        <Textarea
                            id="descripcion"
                            rows={3}
                            value={data.descripcion}
                            onChange={(e) => setData('descripcion', e.target.value)}
                            placeholder="Alcance, metas físicas o cualquier nota relevante."
                        />
                        <InputError message={errors.descripcion} />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Ubicación y contratante</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-4 md:grid-cols-2">
                    <div className="grid gap-2 md:col-span-2">
                        <Label htmlFor="ubicacion">Dirección / referencia</Label>
                        <Input
                            id="ubicacion"
                            value={data.ubicacion}
                            onChange={(e) => setData('ubicacion', e.target.value)}
                            placeholder="Distrito, provincia, departamento o referencia textual"
                        />
                        <InputError message={errors.ubicacion} />
                    </div>
                    <div className="grid gap-2 md:col-span-2">
                        <Label>Ubicación en mapa</Label>
                        <MapaUbicacion
                            latitud={data.latitud ? parseFloat(data.latitud) : null}
                            longitud={data.longitud ? parseFloat(data.longitud) : null}
                            onCambio={(c) => {
                                setData('latitud', c ? String(c.lat) : '');
                                setData('longitud', c ? String(c.lng) : '');
                            }}
                        />
                        <InputError message={errors.latitud} />
                        <InputError message={errors.longitud} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="entidad_contratante">Entidad contratante</Label>
                        <Input
                            id="entidad_contratante"
                            value={data.entidad_contratante}
                            onChange={(e) =>
                                setData('entidad_contratante', e.target.value)
                            }
                            placeholder="Municipalidad Provincial de…"
                        />
                        <InputError message={errors.entidad_contratante} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="monto_contractual">Monto contractual (S/)</Label>
                        <Input
                            id="monto_contractual"
                            type="number"
                            step="0.01"
                            min="0"
                            value={data.monto_contractual}
                            onChange={(e) =>
                                setData('monto_contractual', e.target.value)
                            }
                            placeholder="0.00"
                        />
                        <InputError message={errors.monto_contractual} />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Cronograma</CardTitle>
                </CardHeader>
                <CardContent className="grid gap-4 md:grid-cols-3">
                    <div className="grid gap-2">
                        <Label htmlFor="fecha_inicio">Fecha de inicio</Label>
                        <Input
                            id="fecha_inicio"
                            type="date"
                            value={data.fecha_inicio}
                            onChange={(e) => setData('fecha_inicio', e.target.value)}
                        />
                        <InputError message={errors.fecha_inicio} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="fecha_fin_prevista">Fin previsto</Label>
                        <Input
                            id="fecha_fin_prevista"
                            type="date"
                            value={data.fecha_fin_prevista}
                            onChange={(e) =>
                                setData('fecha_fin_prevista', e.target.value)
                            }
                        />
                        <InputError message={errors.fecha_fin_prevista} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="fecha_fin_real">Fin real</Label>
                        <Input
                            id="fecha_fin_real"
                            type="date"
                            value={data.fecha_fin_real}
                            onChange={(e) => setData('fecha_fin_real', e.target.value)}
                        />
                        <InputError message={errors.fecha_fin_real} />
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
