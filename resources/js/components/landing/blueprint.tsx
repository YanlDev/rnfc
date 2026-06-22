import { animate, createTimeline, stagger, svg } from 'animejs';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { cn } from '@/lib/utils';

/**
 * Trazo de ingeniería vial: una carretera serpenteante en planta que se dibuja
 * poco a poco con anime.js (v4), con anotaciones de plano (progresivas, cotas,
 * radio, norte, escala). Los bordes de la calzada se calculan midiendo la curva
 * real (normales por muestreo) para que las cotas queden pegadas al trazo.
 * Respeta prefers-reduced-motion.
 */
const MONO = "'Geist Mono', ui-monospace, monospace";

// Eje de la carretera (planta).
const EJE =
    'M45,72 C120,18 178,150 262,138 C346,126 352,236 256,250 C176,261 150,206 70,246';

const HALF = 11; // semiancho de calzada (px)
const N = 180; // muestras
const ESTACIONES = [0, 45, 90, 135, 180]; // índices de muestra para progresivas

type Pt = [number, number];

type Geo = {
    left: string;
    right: string;
    ticks: string[];
    stations: { x: number; y: number; t: string }[];
    ancho: { x: number; y: number };
    radio: { x: number; y: number; lx: number; ly: number };
};

const toPath = (pts: Pt[]) =>
    pts
        .map((p, i) => `${i ? 'L' : 'M'}${p[0].toFixed(1)},${p[1].toFixed(1)}`)
        .join(' ');

export function Blueprint({ className }: { className?: string }) {
    const root = useRef<SVGSVGElement | null>(null);
    const eje = useRef<SVGPathElement | null>(null);
    const [geo, setGeo] = useState<Geo | null>(null);

    // Medición: muestrea el eje y deriva bordes, ticks y anclas de cotas.
    useLayoutEffect(() => {
        const path = eje.current;

        if (!path) {
            return;
        }

        const total = path.getTotalLength();
        const cx: number[] = [];
        const cy: number[] = [];
        const nx: number[] = [];
        const ny: number[] = [];

        for (let i = 0; i <= N; i++) {
            const len = (total * i) / N;
            const p = path.getPointAtLength(len);
            const q = path.getPointAtLength(Math.min(len + 1, total));
            let tx = q.x - p.x;
            let ty = q.y - p.y;
            const m = Math.hypot(tx, ty) || 1;
            tx /= m;
            ty /= m;
            cx.push(p.x);
            cy.push(p.y);
            nx.push(-ty);
            ny.push(tx);
        }

        const left: Pt[] = cx.map((x, i) => [
            x + nx[i] * HALF,
            cy[i] + ny[i] * HALF,
        ]);
        const right: Pt[] = cx.map((x, i) => [
            x - nx[i] * HALF,
            cy[i] - ny[i] * HALF,
        ]);

        const ticks = ESTACIONES.map(
            (i) =>
                `M${left[i][0].toFixed(1)},${left[i][1].toFixed(1)} L${right[i][0].toFixed(1)},${right[i][1].toFixed(1)}`,
        );

        const stations = ESTACIONES.map((i, k) => ({
            x: left[i][0] + nx[i] * 13,
            y: left[i][1] + ny[i] * 13,
            t: `0+${(k * 100).toString().padStart(3, '0')}`,
        }));

        const ai = 30; // ancla cota de ancho
        const ri = 90; // ancla radio (curva central)

        setGeo({
            left: toPath(left),
            right: toPath(right),
            ticks,
            stations,
            ancho: {
                x: right[ai][0] - nx[ai] * 16,
                y: right[ai][1] - ny[ai] * 16,
            },
            radio: {
                x: cx[ri] + nx[ri] * 40,
                y: cy[ri] + ny[ri] * 40,
                lx: cx[ri] + nx[ri] * HALF,
                ly: cy[ri] + ny[ri] * HALF,
            },
        });
    }, []);

    // Animación (una vez calculada la geometría).
    useEffect(() => {
        if (!geo || !root.current) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const march = animate('.road-center', {
            strokeDashoffset: [0, -40],
            loop: true,
            ease: 'linear',
            duration: 1000,
            autoplay: false,
        });

        const tl = createTimeline({
            defaults: { ease: 'inOutSine' },
            onComplete: () => march.play(),
        });

        tl.add(
            svg.createDrawable('.road-edge'),
            { draw: ['0 0', '0 1'], duration: 1500 },
            0,
        )
            .add('.road-center', { opacity: [0, 1], duration: 500 }, 950)
            .add(
                svg.createDrawable('.road-tick'),
                { draw: ['0 0', '0 1'], duration: 280, delay: stagger(55) },
                1050,
            )
            .add(
                '.road-anno',
                {
                    opacity: [0, 1],
                    translateY: [4, 0],
                    duration: 420,
                    delay: stagger(45),
                },
                1250,
            );

        return () => {
            march.pause();
            tl.pause();
        };
    }, [geo]);

    return (
        <svg
            ref={root}
            viewBox="0 0 440 300"
            className={cn('h-auto w-full', className)}
            fill="none"
            role="img"
            aria-label="Planta de trazo vial con progresivas y cotas"
        >
            {/* bloque de título */}
            <g className="road-anno" fill="#93c5fd" style={{ opacity: 0 }}>
                <text
                    x="20"
                    y="26"
                    fontSize="11"
                    fontWeight="700"
                    fontFamily={MONO}
                >
                    PLANTA — TRAZO VIAL
                </text>
                <text
                    x="20"
                    y="39"
                    fontSize="8.5"
                    fill="#64748b"
                    fontFamily={MONO}
                >
                    CARRETERA · ESC 1:2000
                </text>
            </g>

            {/* norte */}
            <g className="road-anno" style={{ opacity: 0 }}>
                <path d="M412,20 L412,46" stroke="#64748b" strokeWidth="1" />
                <path d="M412,18 l-4,8 l8,0 Z" fill="#93c5fd" />
                <text
                    x="412"
                    y="58"
                    fontSize="9"
                    fontWeight="700"
                    fontFamily={MONO}
                    fill="#93c5fd"
                    textAnchor="middle"
                >
                    N
                </text>
            </g>

            {/* eje de medición (oculto) */}
            <path ref={eje} d={EJE} stroke="none" fill="none" />

            {geo && (
                <>
                    {/* bordes de calzada */}
                    <g
                        stroke="#cfe0ff"
                        strokeWidth="1.7"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    >
                        <path className="road-edge" d={geo.left} />
                        <path className="road-edge" d={geo.right} />
                    </g>

                    {/* línea central (guiones que avanzan) */}
                    <path
                        className="road-center"
                        d={EJE}
                        stroke="#d4af37"
                        strokeWidth="1.6"
                        strokeDasharray="9 11"
                        strokeLinecap="round"
                        style={{ opacity: 0 }}
                    />

                    {/* progresivas (ticks + etiquetas) */}
                    <g stroke="#7fa8e0" strokeWidth="1">
                        {geo.ticks.map((d, i) => (
                            <path key={`t${i}`} className="road-tick" d={d} />
                        ))}
                    </g>
                    <g
                        className="road-anno"
                        fill="#94a3b8"
                        fontSize="8"
                        fontFamily={MONO}
                        style={{ opacity: 0 }}
                    >
                        {geo.stations.map((s, i) => (
                            <text
                                key={`s${i}`}
                                x={s.x.toFixed(1)}
                                y={s.y.toFixed(1)}
                                textAnchor="middle"
                            >
                                {s.t}
                            </text>
                        ))}
                    </g>

                    {/* cota de ancho de calzada */}
                    <g className="road-anno" style={{ opacity: 0 }}>
                        <text
                            x={geo.ancho.x.toFixed(1)}
                            y={geo.ancho.y.toFixed(1)}
                            fontSize="8.5"
                            fontFamily={MONO}
                            fill="#cfe0ff"
                            textAnchor="middle"
                        >
                            B = 7.20 m
                        </text>
                    </g>

                    {/* radio de curva */}
                    <g className="road-anno" style={{ opacity: 0 }}>
                        <path
                            d={`M${geo.radio.lx.toFixed(1)},${geo.radio.ly.toFixed(1)} L${geo.radio.x.toFixed(1)},${geo.radio.y.toFixed(1)}`}
                            stroke="#64748b"
                            strokeWidth="1"
                            strokeDasharray="3 3"
                        />
                        <text
                            x={geo.radio.x.toFixed(1)}
                            y={(geo.radio.y - 4).toFixed(1)}
                            fontSize="8.5"
                            fontFamily={MONO}
                            fill="#38bdf8"
                            textAnchor="middle"
                        >
                            R = 50.00 m
                        </text>
                    </g>
                </>
            )}
        </svg>
    );
}
