import { Form, Head } from '@inertiajs/react';
import { Lock, ShieldCheck, Users } from 'lucide-react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

type InvitacionObra = {
    email: string;
    obra: string;
    rol: string;
};

type InvitacionGlobal = {
    email: string;
    rol: string;
};

export default function Register({
    invitacion = null,
    invitacionGlobal = null,
}: {
    invitacion?: InvitacionObra | null;
    invitacionGlobal?: InvitacionGlobal | null;
}) {
    const invitacionActiva = invitacion ?? invitacionGlobal ?? null;

    // === SIN INVITACIÓN: bloqueo de acceso ===
    if (!invitacionActiva) {
        return (
            <>
                <Head title="Acceso restringido" />
                <div className="flex flex-col items-center gap-5 text-center">
                    <div className="flex size-14 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-950/30">
                        <Lock className="size-7" />
                    </div>
                    <div>
                        <h2 className="text-lg font-semibold">
                            Acceso solo por invitación
                        </h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Esta plataforma es de uso restringido. Para crear una cuenta
                            necesitas haber recibido una <strong>invitación por correo</strong>
                            {' '}desde el administrador.
                        </p>
                        <p className="mt-3 text-sm text-muted-foreground">
                            Si ya tienes invitación, abre el enlace que aparece en el
                            correo y vuelve a intentarlo.
                        </p>
                    </div>
                    <div className="w-full border-t border-border pt-5 text-sm text-muted-foreground">
                        ¿Ya tienes una cuenta?{' '}
                        <TextLink href={login()}>Inicia sesión</TextLink>
                    </div>
                </div>
            </>
        );
    }

    const esGlobal = invitacionGlobal !== null;

    // === CON INVITACIÓN: formulario con email pre-llenado ===
    return (
        <>
            <Head title="Crear cuenta" />

            <div className="mb-5 flex items-start gap-3 rounded-lg border border-primary/20 bg-primary/5 p-4">
                {esGlobal ? (
                    <Users className="mt-0.5 size-5 shrink-0 text-primary" />
                ) : (
                    <ShieldCheck className="mt-0.5 size-5 shrink-0 text-primary" />
                )}
                <div className="space-y-1 text-sm">
                    <div className="font-semibold text-foreground">
                        Aceptando invitación
                    </div>
                    {esGlobal ? (
                        <div className="text-muted-foreground">
                            Rol: <strong className="text-foreground">{invitacionActiva.rol}</strong>
                        </div>
                    ) : (
                        <div className="text-muted-foreground">
                            Obra: <strong className="text-foreground">{invitacionActiva.obra}</strong>
                            {' '}· Rol: <strong className="text-foreground">{invitacionActiva.rol}</strong>
                        </div>
                    )}
                </div>
            </div>

            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre completo</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Nombres y apellidos"
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Correo electrónico</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="correo@ejemplo.com"
                                    defaultValue={invitacionActiva.email}
                                    readOnly
                                    className="bg-muted/40"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Debes registrarte con el correo al que se envió la invitación.
                                </p>
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Contraseña</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Contraseña"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirmar contraseña
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Repite la contraseña"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={5}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                Crear cuenta
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            ¿Ya tienes una cuenta?{' '}
                            <TextLink href={login()} tabIndex={6}>
                                Inicia sesión
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    title: 'Crea tu cuenta',
    description: 'Completa tus datos para registrarte en la plataforma',
};
