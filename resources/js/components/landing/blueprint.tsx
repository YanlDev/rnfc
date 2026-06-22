import { animate, createTimeline, stagger, svg } from 'animejs';
import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { cn } from '@/lib/utils';

/**
 * Trazo de ingeniería vial: una carretera serpenteante en planta que se dibuja
 * poco a poco con anime.js (v4), con anotaciones de plano (progresivas, cotas,
 * radio, hitos de planificación, norte). Los bordes de la calzada se calculan
 * midiendo la curva real (normales por muestreo) para que las anotaciones
 * queden pegadas al trazo y no se superpongan. Respeta prefers-reduced-motion.
 */
const MONO = "'Geist Mono', ui-monospace, monospace";

// Eje de la carretera (planta).
const EJE =
    'M45,72 C120,18 178,150 262,138 C346,126 352,236 256,250 C176,261 150,206 70,246';

const HALF = 11; // semiancho de calzada (px)
const N = 180; // muestras

// Progresivas (lado +normal). Índices separados para no chocar entre sí.
const ESTACIONES: [number, string][] = [
    [0, '0+000'],
    [60, '0+200'],
    [120, '0+400'],
    [178, '0+600'],
];

// Hitos de planificación (lado +normal, índices intermedios).
const HITOS: [number, string][] = [
    [30, 'H1 · Mov. tierras'],
    [95, 'H2 · Pavimento'],
    [152, 'H3 · Entrega'],
];

const AI = 32; // ancla cota de ancho (lado -normal)
const RI = 92; // ancla radio de curva (lado -normal)

type Pt = [number, number];
type Anno = { x: number; y: number; lx: number; ly: number; t: string };

type Geo = {
    left: string;
    right: string;
    ticks: string[];
    stations: { x: number; y: number; t: string }[];
    hitos: Anno[];
    ancho: { x: number; y: number };
    radio: Anno;
};

const toPath = (pts: Pt[]) =>
    pts
        .map((p, i) => `${i ? 'L' : 'M'}${p[0].toFixed(1)},${p[1].toFixed(1)}`)
        .join(' ');

export function Blueprint({ className }: { className?: string }) {
    const root = useRef<SVGSVGElement | null>(null);
    const eje = useRef<SVGPathElement | null>(null);
    const [geo, setGeo] = useState<Geo | null>(null);

    // Medición: muestrea el eje y deriva bordes y anclas de anotación.
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
            ([i]) =>
                `M${left[i][0].toFixed(1)},${left[i][1].toFixed(1)} L${right[i][0].toFixed(1)},${right[i][1].toFixed(1)}`,
        );

        const stations = ESTACIONES.map(([i, t]) => ({
            x: cx[i] + nx[i] * (HALF + 13),
            y: cy[i] + ny[i] * (HALF + 13),
            t,
        }));

        const hitos: Anno[] = HITOS.map(([i, t]) => ({
            lx: cx[i] + nx[i] * HALF,
            ly: cy[i] + ny[i] * HALF,
            x: cx[i] + nx[i] * (HALF + 16),
            y: cy[i] + ny[i] * (HALF + 16),
            t,
        }));

        setGeo({
            left: toPath(left),
            right: toPath(right),
            ticks,
            stations,
            hitos,
            ancho: {
                x: cx[AI] - nx[AI] * (HALF + 14),
                y: cy[AI] - ny[AI] * (HALF + 14),
            },
            radio: {
                lx: cx[RI] - nx[RI] * HALF,
                ly: cy[RI] - ny[RI] * HALF,
                x: cx[RI] - nx[RI] * (HALF + 34),
                y: cy[RI] - ny[RI] * (HALF + 34),
                t: 'R = 50.00 m',
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
                    delay: stagger(40),
                },
                1200,
            )
            .add(
                '.road-hito',
                {
                    opacity: [0, 1],
                    scale: [0.5, 1],
                    duration: 460,
                    delay: stagger(110),
                    ease: 'outBack',
                },
                1450,
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
            aria-label="Planta de trazo vial con progresivas, cotas e hitos"
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
                <path d="M414,18 L414,44" stroke="#64748b" strokeWidth="1" />
                <path d="M414,16 l-4,8 l8,0 Z" fill="#93c5fd" />
                <text
                    x="414"
                    y="56"
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
                    <text
                        className="road-anno"
                        x={geo.ancho.x.toFixed(1)}
                        y={geo.ancho.y.toFixed(1)}
                        fontSize="8.5"
                        fontFamily={MONO}
                        fill="#cfe0ff"
                        textAnchor="middle"
                        style={{ opacity: 0 }}
                    >
                        B = 7.20 m
                    </text>

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
                            y={(geo.radio.y - 5).toFixed(1)}
                            fontSize="8.5"
                            fontFamily={MONO}
                            fill="#38bdf8"
                            textAnchor="middle"
                        >
                            {geo.radio.t}
                        </text>
                    </g>

                    {/* hitos de planificación */}
                    {geo.hitos.map((h, i) => (
                        <g
                            key={`h${i}`}
                            className="road-hito"
                            style={{
                                transformBox: 'fill-box',
                                transformOrigin: 'center',
                                opacity: 0,
                            }}
                        >
                            <path
                                d={`M${h.lx.toFixed(1)},${h.ly.toFixed(1)} L${h.x.toFixed(1)},${h.y.toFixed(1)}`}
                                stroke="#34d399"
                                strokeWidth="1"
                                strokeDasharray="3 3"
                            />
                            <polygon
                                points={`${h.x.toFixed(1)},${(h.y - 5).toFixed(1)} ${(h.x + 5).toFixed(1)},${h.y.toFixed(1)} ${h.x.toFixed(1)},${(h.y + 5).toFixed(1)} ${(h.x - 5).toFixed(1)},${h.y.toFixed(1)}`}
                                fill="rgba(52,211,153,0.18)"
                                stroke="#34d399"
                                strokeWidth="1.2"
                            />
                            <text
                                x={h.x.toFixed(1)}
                                y={(h.y + 16).toFixed(1)}
                                fontSize="7.5"
                                fontFamily={MONO}
                                fill="#34d399"
                                textAnchor="middle"
                            >
                                {h.t}
                            </text>
                        </g>
                    ))}
                </>
            )}
        </svg>
    );
}
