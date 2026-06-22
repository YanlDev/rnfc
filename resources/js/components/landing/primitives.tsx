import { useEffect, useRef, useState } from 'react';
import type { ElementType, ReactNode } from 'react';
import { cn } from '@/lib/utils';

/**
 * Revela su contenido al entrar en viewport (IntersectionObserver).
 * Respeta prefers-reduced-motion vía el CSS de .reveal.
 */
export function Reveal({
    children,
    className,
    delay = 0,
    as: Tag = 'div',
}: {
    children: ReactNode;
    className?: string;
    delay?: number;
    as?: ElementType;
}) {
    const ref = useRef<HTMLElement | null>(null);
    // Si no hay IntersectionObserver, se muestra de entrada (sin animación).
    const [visible, setVisible] = useState(
        () =>
            typeof window !== 'undefined' &&
            !('IntersectionObserver' in window),
    );

    useEffect(() => {
        const el = ref.current;

        if (!el || !('IntersectionObserver' in window)) {
            return;
        }

        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) {
                        setVisible(true);
                        io.unobserve(e.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
        );

        io.observe(el);

        return () => io.disconnect();
    }, []);

    return (
        <Tag
            ref={ref}
            className={cn('reveal', visible && 'in', className)}
            style={delay ? { '--reveal-delay': `${delay}ms` } : undefined}
        >
            {children}
        </Tag>
    );
}

export function Eyebrow({
    children,
    center = false,
    tone = 'dark',
    className,
}: {
    children: ReactNode;
    center?: boolean;
    tone?: 'dark' | 'light';
    className?: string;
}) {
    return (
        <div
            className={cn(
                'eyebrow',
                center && 'eyebrow-center',
                tone === 'dark' ? 'text-slate-300/70' : 'text-blue-700',
                className,
            )}
        >
            {children}
        </div>
    );
}

/**
 * Sección del landing. tone controla el esquema de color (premium híbrido:
 * secciones claras intercaladas con secciones oscuras de impacto).
 */
export function Section({
    id,
    tone = 'light',
    className,
    children,
}: {
    id?: string;
    tone?: 'light' | 'dark' | 'darker';
    className?: string;
    children: ReactNode;
}) {
    const tones: Record<string, string> = {
        light: 'bg-white text-slate-900',
        dark: 'bg-[#071426] text-slate-100',
        darker: 'bg-[#05101e] text-slate-100',
    };

    return (
        <section
            id={id}
            className={cn(
                'relative scroll-mt-20 overflow-hidden',
                tones[tone],
                className,
            )}
        >
            {children}
        </section>
    );
}
