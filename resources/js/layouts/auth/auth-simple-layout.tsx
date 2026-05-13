import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-4">
                        <Link
                            href={home()}
                            className="group relative flex flex-col items-center gap-2 font-medium"
                        >
                            <span
                                aria-hidden
                                className="absolute inset-0 -z-10 mx-auto h-28 w-28 animate-brand-pulse rounded-full bg-[radial-gradient(closest-side,rgba(40,80,218,0.45),rgba(20,86,148,0.15)_55%,transparent_75%)] blur-2xl sm:h-32 sm:w-32"
                            />
                            <AppLogoIcon className="h-24 w-auto animate-brand-float drop-shadow-[0_10px_30px_rgba(20,86,148,0.45)] transition-transform duration-500 ease-out group-hover:scale-105 sm:h-28" />
                            <span className="sr-only">{title}</span>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-xl font-medium">{title}</h1>
                            <p className="text-center text-sm text-muted-foreground">
                                {description}
                            </p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
