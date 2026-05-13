import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <AppLogoIcon className="h-9 w-auto" />
            <div className="ml-1 grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-semibold text-primary">
                    RNFC
                </span>
                <span className="truncate text-[10px] tracking-wide text-muted-foreground uppercase">
                    Consultor de Obras
                </span>
            </div>
        </>
    );
}
