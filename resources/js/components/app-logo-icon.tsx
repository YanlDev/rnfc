import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon({
    className,
    alt = 'RNFC Consultor de Obras',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img
            src="/brand/rnfc-logo.png"
            alt={alt}
            className={className}
            {...props}
        />
    );
}
