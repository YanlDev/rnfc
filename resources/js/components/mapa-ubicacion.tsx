import 'leaflet/dist/leaflet.css';

import L from 'leaflet';
import { LocateFixed, Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
    MapContainer,
    Marker,
    TileLayer,
    useMap,
    useMapEvents,
} from 'react-leaflet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

// Fix de los íconos de marker por defecto (Leaflet en Vite no resuelve las URLs).
const iconUrl =
    'data:image/svg+xml;utf8,' +
    encodeURIComponent(
        `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="48" viewBox="0 0 32 48">
            <path d="M16 0C7.2 0 0 7.2 0 16c0 11 16 32 16 32s16-21 16-32C32 7.2 24.8 0 16 0z" fill="#145694"/>
            <circle cx="16" cy="16" r="6" fill="#ffd21c"/>
        </svg>`,
    );

const customIcon = L.icon({
    iconUrl,
    iconSize: [32, 48],
    iconAnchor: [16, 48],
    popupAnchor: [0, -48],
});

type LatLng = { lat: number; lng: number };

type Props = {
    latitud?: number | null;
    longitud?: number | null;
    onCambio?: (coords: LatLng | null) => void;
    /** Sólo lectura: muestra el marcador sin permitir editar. */
    soloLectura?: boolean;
    altura?: string;
};

// Centro por defecto: Lima, Perú.
const CENTRO_DEFECTO: LatLng = { lat: -12.0464, lng: -77.0428 };

function ClickHandler({
    onClick,
    deshabilitado,
}: {
    onClick: (c: LatLng) => void;
    deshabilitado: boolean;
}) {
    useMapEvents({
        click(e) {
            if (deshabilitado) return;
            onClick({ lat: e.latlng.lat, lng: e.latlng.lng });
        },
    });
    return null;
}

function RecenterControl({ posicion }: { posicion: LatLng | null }) {
    const map = useMap();
    useEffect(() => {
        if (posicion) {
            map.flyTo([posicion.lat, posicion.lng], Math.max(map.getZoom(), 14));
        }
    }, [posicion, map]);
    return null;
}

type ResultadoBusqueda = {
    display_name: string;
    lat: string;
    lon: string;
};

export default function MapaUbicacion({
    latitud,
    longitud,
    onCambio,
    soloLectura = false,
    altura = '320px',
}: Props) {
    const tieneCoords =
        latitud !== null && latitud !== undefined && longitud !== null && longitud !== undefined;
    const inicial: LatLng = tieneCoords
        ? { lat: latitud as number, lng: longitud as number }
        : CENTRO_DEFECTO;

    const [marcador, setMarcador] = useState<LatLng | null>(
        tieneCoords ? inicial : null,
    );
    const [consulta, setConsulta] = useState('');
    const [buscando, setBuscando] = useState(false);
    const [resultados, setResultados] = useState<ResultadoBusqueda[]>([]);
    const debounceRef = useRef<number | null>(null);

    // Sincroniza marker si las props cambian externamente (p.ej., reset de form).
    useEffect(() => {
        if (latitud !== null && latitud !== undefined && longitud !== null && longitud !== undefined) {
            setMarcador({ lat: latitud, lng: longitud });
        } else {
            setMarcador(null);
        }
    }, [latitud, longitud]);

    const actualizar = (coords: LatLng | null) => {
        setMarcador(coords);
        onCambio?.(coords);
    };

    const usarMiUbicacion = () => {
        if (!navigator.geolocation) {
            alert('Tu navegador no soporta geolocalización.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) =>
                actualizar({
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                }),
            (err) => alert('No se pudo obtener tu ubicación: ' + err.message),
            { enableHighAccuracy: true, timeout: 10000 },
        );
    };

    const buscar = (q: string) => {
        if (debounceRef.current) window.clearTimeout(debounceRef.current);
        if (!q.trim()) {
            setResultados([]);
            return;
        }
        debounceRef.current = window.setTimeout(async () => {
            try {
                setBuscando(true);
                const url =
                    'https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=pe&q=' +
                    encodeURIComponent(q);
                const r = await fetch(url, {
                    headers: { 'Accept-Language': 'es' },
                });
                const data = (await r.json()) as ResultadoBusqueda[];
                setResultados(data);
            } catch {
                setResultados([]);
            } finally {
                setBuscando(false);
            }
        }, 400);
    };

    const seleccionarResultado = (r: ResultadoBusqueda) => {
        const lat = parseFloat(r.lat);
        const lng = parseFloat(r.lon);
        actualizar({ lat, lng });
        setConsulta(r.display_name);
        setResultados([]);
    };

    return (
        <div className="space-y-2">
            {!soloLectura && (
                <div className="flex flex-col gap-2 sm:flex-row">
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Buscar dirección o lugar en Perú…"
                            value={consulta}
                            onChange={(e) => {
                                setConsulta(e.target.value);
                                buscar(e.target.value);
                            }}
                            className="pl-9"
                        />
                        {resultados.length > 0 && (
                            <ul className="absolute top-full right-0 left-0 z-[1000] mt-1 max-h-60 overflow-auto rounded-md border border-border bg-popover shadow-lg">
                                {resultados.map((r, i) => (
                                    <li key={i}>
                                        <button
                                            type="button"
                                            onClick={() => seleccionarResultado(r)}
                                            className="block w-full px-3 py-2 text-left text-sm hover:bg-muted"
                                        >
                                            {r.display_name}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                        {buscando && (
                            <div className="absolute top-1/2 right-3 -translate-y-1/2 text-xs text-muted-foreground">
                                Buscando…
                            </div>
                        )}
                    </div>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={usarMiUbicacion}
                    >
                        <LocateFixed className="size-4" />
                        Mi ubicación
                    </Button>
                    {marcador && (
                        <Button
                            type="button"
                            variant="ghost"
                            onClick={() => {
                                actualizar(null);
                                setConsulta('');
                            }}
                            title="Quitar ubicación"
                        >
                            <X className="size-4" />
                        </Button>
                    )}
                </div>
            )}

            <div
                className="overflow-hidden rounded-md border border-border"
                style={{ height: altura }}
            >
                <MapContainer
                    center={[inicial.lat, inicial.lng]}
                    zoom={tieneCoords ? 15 : 6}
                    style={{ height: '100%', width: '100%' }}
                    scrollWheelZoom={!soloLectura}
                >
                    <TileLayer
                        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    />
                    <ClickHandler
                        onClick={actualizar}
                        deshabilitado={soloLectura}
                    />
                    <RecenterControl posicion={marcador} />
                    {marcador && (
                        <Marker
                            position={[marcador.lat, marcador.lng]}
                            icon={customIcon}
                            draggable={!soloLectura}
                            eventHandlers={{
                                dragend: (e) => {
                                    const m = e.target as L.Marker;
                                    const p = m.getLatLng();
                                    actualizar({ lat: p.lat, lng: p.lng });
                                },
                            }}
                        />
                    )}
                </MapContainer>
            </div>

            {marcador ? (
                <div className="text-xs text-muted-foreground">
                    Coordenadas:{' '}
                    <code className="font-mono text-foreground">
                        {marcador.lat.toFixed(6)}, {marcador.lng.toFixed(6)}
                    </code>
                </div>
            ) : (
                !soloLectura && (
                    <div className="text-xs text-muted-foreground">
                        Haz clic en el mapa, búscalo arriba o usa tu ubicación
                        para fijar la chincheta.
                    </div>
                )
            )}
        </div>
    );
}
