import { animate, createTimeline, stagger, svg } from 'animejs';
import { useEffect, useRef } from 'react';
import { cn } from '@/lib/utils';

/**
 * Trazo de ingeniería: un edificio en construcción con grúa torre, en vista
 * isométrica, que se "dibuja" con anime.js (v4). Caras sólidas sutiles +
 * line-art para que se lea claramente como una obra. Respeta
 * prefers-reduced-motion.
 */
const COS = Math.cos(Math.PI / 6); // 30°
const SIN = 0.5;
const S = 42; // escala de planta
const FH = 34; // altura de entrepiso
const OX = 150;
const OY = 210;
const NZ = 4; // pisos (0..4)

/** Proyección isométrica de un nudo (gx,gy,gz) a coordenadas de pantalla. */
function P(gx: number, gy: number, gz: number): [number, number] {
    return [OX + (gx - gy) * COS * S, OY + (gx + gy) * SIN * S - gz * FH];
}

const seg = (a: [number, number], b: [number, number]) =>
    `M${a[0].toFixed(1)},${a[1].toFixed(1)} L${b[0].toFixed(1)},${b[1].toFixed(1)}`;

const poly = (pts: [number, number][]) =>
    pts.map((p) => `${p[0].toFixed(1)},${p[1].toFixed(1)}`).join(' ');

// Esquinas visibles del prisma (esquina cercana = (2,2)).
const X0 = P(2, 0, 0);
const Y0 = P(0, 2, 0);
const C0 = P(2, 2, 0);
const A4 = P(0, 0, NZ);
const X4 = P(2, 0, NZ);
const Y4 = P(0, 2, NZ);
const C4 = P(2, 2, NZ);

// Aristas del volumen (silueta).
const EDGES: string[] = [
    seg(P(2, 2, 0), C4), // arista vertical cercana
    seg(P(2, 0, 0), X4),
    seg(P(0, 2, 0), Y4),
    seg(A4, X4),
    seg(X4, C4),
    seg(C4, Y4),
    seg(Y4, A4),
    seg(X0, C0),
    seg(C0, Y0),
];

// Caras (relleno translúcido) para que se lea como sólido.
const CARA_DER = poly([X0, C0, C4, X4]);
const CARA_IZQ = poly([Y0, C0, C4, Y4]);
const CARA_TOP = poly([A4, X4, C4, Y4]);

// Pisos y montantes (rejilla de fachada -> se lee como edificio).
const GRID: string[] = [];

for (let gz = 1; gz < NZ; gz++) {
    GRID.push(seg(P(2, 0, gz), P(2, 2, gz))); // horizontal cara derecha
    GRID.push(seg(P(0, 2, gz), P(2, 2, gz))); // horizontal cara izquierda
}

GRID.push(seg(P(2, 1, 0), P(2, 1, NZ))); // montante cara derecha
GRID.push(seg(P(1, 2, 0), P(1, 2, NZ))); // montante cara izquierda

// Grúa torre (en coordenadas de pantalla, sobre el edificio).
const GRUA: string[] = [
    seg([315, 300], [315, 52]), // mástil
    seg([315, 52], [315, 30]), // cabeza
    seg([180, 52], [362, 52]), // pluma
    seg([315, 30], [180, 52]), // tirante delantero
    seg([315, 30], [362, 52]), // tirante trasero
    seg([210, 52], [210, 138]), // cable
    seg([204, 138], [210, 146]), // gancho
    seg([216, 138], [210, 146]),
];

export function Blueprint({ className }: { className?: string }) {
    const root = useRef<SVGSVGElement | null>(null);

    useEffect(() => {
        const el = root.current;

        if (!el) {
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const bob = animate('.bd-load', {
            translateY: [0, 7],
            loop: true,
            alternate: true,
            ease: 'inOutSine',
            duration: 1700,
            autoplay: false,
        });

        const tl = createTimeline({
            defaults: { ease: 'inOutSine' },
            onComplete: () => bob.play(),
        });

        tl.add(
            svg.createDrawable('.bd-ground'),
            { draw: ['0 0', '0 1'], duration: 600 },
            0,
        )
            .add(
                svg.createDrawable('.bd-edge'),
                { draw: ['0 0', '0 1'], duration: 520, delay: stagger(55) },
                250,
            )
            .add('.bd-face', { opacity: [0, 1], duration: 600 }, 700)
            .add(
                svg.createDrawable('.bd-grid'),
                { draw: ['0 0', '0 1'], duration: 420, delay: stagger(45) },
                900,
            )
            .add(
                svg.createDrawable('.bd-crane'),
                { draw: ['0 0', '0 1'], duration: 480, delay: stagger(75) },
                1350,
            )
            .add(
                '.bd-cw',
                {
                    opacity: [0, 1],
                    scale: [0.6, 1],
                    duration: 450,
                    ease: 'outBack',
                },
                1700,
            )
            .add('.bd-load', { opacity: [0, 1], duration: 400 }, 1900);

        return () => {
            bob.pause();
            tl.pause();
        };
    }, []);

    return (
        <svg
            ref={root}
            viewBox="0 0 410 330"
            className={cn('h-auto w-full', className)}
            fill="none"
            role="img"
            aria-label="Edificio en construcción con grúa torre, vista isométrica"
        >
            {/* suelo */}
            <path
                className="bd-ground"
                d="M30,300 L388,300"
                stroke="#60a5fa"
                strokeWidth="1.6"
                strokeLinecap="round"
            />

            {/* caras del volumen */}
            <g className="bd-face" style={{ opacity: 0 }}>
                <polygon points={CARA_TOP} fill="rgba(96,165,250,0.05)" />
                <polygon points={CARA_IZQ} fill="rgba(96,165,250,0.10)" />
                <polygon points={CARA_DER} fill="rgba(96,165,250,0.05)" />
            </g>

            {/* rejilla de fachada (pisos / montantes) */}
            <g stroke="#7fa8e0" strokeWidth="1" strokeLinecap="round">
                {GRID.map((d, i) => (
                    <path key={`g${i}`} className="bd-grid" d={d} />
                ))}
            </g>

            {/* aristas del edificio */}
            <g
                stroke="#cfe0ff"
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
            >
                {EDGES.map((d, i) => (
                    <path key={`e${i}`} className="bd-edge" d={d} />
                ))}
            </g>

            {/* grúa torre */}
            <g
                stroke="#d4af37"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
            >
                {GRUA.map((d, i) => (
                    <path key={`cr${i}`} className="bd-crane" d={d} />
                ))}
            </g>

            {/* contrapeso */}
            <rect
                className="bd-cw"
                x="350"
                y="52"
                width="13"
                height="11"
                fill="#d4af37"
                style={{
                    transformBox: 'fill-box',
                    transformOrigin: 'center',
                    opacity: 0,
                }}
            />

            {/* carga colgante (sube y baja) */}
            <g
                className="bd-load"
                fill="rgba(212,175,55,0.18)"
                stroke="#d4af37"
                strokeWidth="1.4"
                style={{ opacity: 0 }}
            >
                <rect x="201" y="146" width="18" height="11" rx="1" />
            </g>
        </svg>
    );
}
