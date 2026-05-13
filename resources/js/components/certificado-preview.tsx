import { QRCodeSVG } from 'qrcode.react';

type Maybe = string | null | undefined;

export type CertificadoPreviewData = {
    codigo?: Maybe;
    tipo?: Maybe;
    tipo_titulo?: Maybe;
    tipo_label?: Maybe;
    beneficiario_nombre?: Maybe;
    beneficiario_documento?: Maybe;
    beneficiario_profesion?: Maybe;
    obra?:
        | { codigo?: Maybe; nombre?: Maybe; entidad_contratante?: Maybe }
        | null;
    obra_nombre_libre?: Maybe;
    obra_entidad_libre?: Maybe;
    cargo?: Maybe;
    fecha_inicio?: Maybe;
    fecha_fin?: Maybe;
    descripcion?: Maybe;
    lugar_emision?: Maybe;
    emisor_nombre?: Maybe;
    emisor_cargo?: Maybe;
    emisor_cip?: Maybe;
    fecha_emision?: Maybe;
    hash_verificacion?: Maybe;
    vigente?: boolean | null;
    url_verificacion?: Maybe;
};

export type BrandingUrls = {
    firma?: Maybe;
    iso1?: Maybe;
    iso2?: Maybe;
    iso3?: Maybe;
};

const MESES = [
    'enero',
    'febrero',
    'marzo',
    'abril',
    'mayo',
    'junio',
    'julio',
    'agosto',
    'setiembre',
    'octubre',
    'noviembre',
    'diciembre',
];

function fechaLarga(iso?: string | null): string {
    if (!iso) return '';
    const [a, m, d] = iso.split('-').map(Number);
    if (!a || !m || !d) return iso ?? '';
    return `${d} de ${MESES[m - 1]} de ${a}`;
}

function CuerpoTexto({ c }: { c: CertificadoPreviewData }) {
    const cargo = (c.cargo || c.tipo_label || '').toUpperCase();
    const obra = (c.obra?.nombre || c.obra_nombre_libre || '').toUpperCase();
    const entidad = c.obra?.entidad_contratante || c.obra_entidad_libre;
    const inicio = fechaLarga(c.fecha_inicio);
    const fin = fechaLarga(c.fecha_fin);

    const cierre =
        ' demostrando durante su permanencia puntualidad, responsabilidad, honestidad y dedicación en las labores que le fueron encomendadas.';

    if (
        c.tipo === 'trabajador' ||
        c.tipo === 'residente' ||
        c.tipo === 'supervisor' ||
        c.tipo === 'especialista'
    ) {
        return (
            <p>
                Por haber laborado en nuestra empresa como{' '}
                <strong>{cargo || '—'}</strong>
                {obra ? ',' : '.'}
                {obra && (
                    <>
                        {' '}en la ejecución de la obra <strong>{obra}</strong>
                        {entidad && (
                            <>
                                , ejecutada para <strong>{entidad}</strong>
                            </>
                        )}
                        ,
                    </>
                )}
                {inicio && fin && (
                    <>
                        {' '}durante el período comprendido entre el{' '}
                        <strong>{inicio}</strong> y el <strong>{fin}</strong>,
                    </>
                )}
                {cierre}
            </p>
        );
    }
    if (c.tipo === 'capacitacion') {
        return (
            <p>
                Por haber participado satisfactoriamente en la capacitación denominada{' '}
                <strong>{c.cargo || '—'}</strong>
                {inicio && fin && (
                    <>
                        , realizada entre el <strong>{inicio}</strong> y el{' '}
                        <strong>{fin}</strong>,
                    </>
                )}
                {' '}cumpliendo con la totalidad del programa académico establecido.
            </p>
        );
    }
    if (c.tipo === 'participacion') {
        return (
            <p>
                Por su participación
                {c.cargo && (
                    <>
                        {' '}en calidad de <strong>{c.cargo}</strong>
                    </>
                )}
                {obra && (
                    <>
                        {' '}en el proyecto <strong>{obra}</strong>,
                    </>
                )}
                {' '}demostrando profesionalismo y compromiso durante toda su intervención.
            </p>
        );
    }
    if (c.tipo === 'servicios_profesionales') {
        return (
            <p>
                Por haber prestado servicios profesionales
                {c.cargo && (
                    <>
                        {' '}en calidad de <strong>{c.cargo}</strong>
                    </>
                )}
                {obra && (
                    <>
                        {' '}para la obra <strong>{obra}</strong>
                    </>
                )}
                {inicio && fin && (
                    <>
                        , entre el <strong>{inicio}</strong> y el{' '}
                        <strong>{fin}</strong>
                    </>
                )}
                , cumpliendo a cabalidad con los términos del encargo recibido.
            </p>
        );
    }
    return (
        <p className="italic text-muted-foreground">
            Selecciona un tipo de certificado para ver el texto.
        </p>
    );
}

/**
 * Renderiza una hoja A4 vertical con el mismo diseño visual del PDF.
 */
export default function CertificadoPreview({
    certificado,
    branding,
    scale = 0.55,
}: {
    certificado: CertificadoPreviewData;
    branding?: BrandingUrls | null;
    scale?: number;
}) {
    const c = certificado;
    const codigo = c.codigo || 'RNFC-XXXX-XXXXXX';
    const urlVerificacion =
        c.url_verificacion ||
        `${window.location.origin}/verificar/${codigo}`;
    const fechaEmisionLarga = fechaLarga(c.fecha_emision) || '—';
    const titulo = c.tipo_titulo || 'CERTIFICADO';

    return (
        <div
            className="origin-top-left"
            style={{
                width: `${210 * scale}mm`,
                height: `${297 * scale}mm`,
            }}
        >
            <div
                style={{
                    transform: `scale(${scale})`,
                    transformOrigin: 'top left',
                    width: '210mm',
                    height: '297mm',
                    color: '#1f2937',
                    fontSize: '11pt',
                    lineHeight: 1.5,
                }}
                className="relative bg-white shadow-2xl"
            >
                {c.vigente === false && (
                    <div
                        className="pointer-events-none absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rotate-[-25deg] border-[6px] border-[rgba(193,39,45,0.18)] px-[12mm] py-[4mm] font-bold tracking-[6pt]"
                        style={{ fontSize: '80pt', color: 'rgba(193, 39, 45, 0.12)' }}
                    >
                        REVOCADO
                    </div>
                )}

                {/* Contenido sin marcos */}
                <div className="relative z-10 px-[22mm] pt-[22mm] pb-[18mm]">
                    {/* Header: logo izquierda · ISOs derecha */}
                    <div className="mb-[14mm] flex items-center">
                        <div className="w-1/2">
                            <img
                                src="/brand/rnfc-logo.png"
                                alt="RNFC"
                                className="h-[16mm] w-auto"
                            />
                        </div>
                        <div className="flex w-1/2 items-center justify-end gap-[2.5mm]">
                            {(['iso1', 'iso2', 'iso3'] as const).map((slot) => {
                                const url = branding?.[slot];
                                return url ? (
                                    <img
                                        key={slot}
                                        src={url}
                                        alt={slot.toUpperCase()}
                                        className="h-[13mm] w-[13mm] object-contain"
                                    />
                                ) : (
                                    <div
                                        key={slot}
                                        className="flex h-[13mm] w-[13mm] items-center justify-center border border-dashed text-center"
                                        style={{ color: '#9ca3af', borderColor: '#d1d5db', fontSize: '5.5pt', lineHeight: 1.1 }}
                                    >
                                        {slot.toUpperCase()}
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Código */}
                    <div
                        className="mb-[14mm] text-right"
                        style={{ fontSize: '8.5pt', color: '#6b7280', letterSpacing: '1pt' }}
                    >
                        N° de Certificado:{' '}
                        <strong
                            style={{
                                fontFamily: 'Courier New, monospace',
                                fontSize: '9.5pt',
                                color: '#1f2937',
                                letterSpacing: 0,
                            }}
                        >
                            {codigo}
                        </strong>
                    </div>

                    {/* Título */}
                    <h1
                        className="text-center font-bold uppercase"
                        style={{
                            fontSize: '36pt',
                            color: '#1f2937',
                            letterSpacing: '1.5pt',
                            marginBottom: '12mm',
                        }}
                    >
                        {titulo}
                    </h1>

                    {/* Otorgado a */}
                    <div
                        className="mb-[6mm] text-center uppercase"
                        style={{ fontSize: '10pt', color: '#6b7280', letterSpacing: '2.5pt' }}
                    >
                        Se otorga a
                    </div>

                    {/* Beneficiario en rojo */}
                    <div
                        className="mb-[3mm] text-center font-bold"
                        style={{ fontSize: '22pt', color: '#0b2545', letterSpacing: '1pt' }}
                    >
                        {(c.beneficiario_nombre || 'NOMBRE DEL BENEFICIARIO').toUpperCase()}
                    </div>

                    {/* DNI / profesión */}
                    {(c.beneficiario_documento || c.beneficiario_profesion) ? (
                        <div
                            className="mb-[10mm] text-center"
                            style={{ fontSize: '10pt', color: '#4b5563' }}
                        >
                            {c.beneficiario_documento && (
                                <>DNI N° {c.beneficiario_documento}</>
                            )}
                            {c.beneficiario_documento && c.beneficiario_profesion && ' · '}
                            {c.beneficiario_profesion}
                        </div>
                    ) : (
                        <div style={{ height: '8mm' }} />
                    )}

                    {/* Cuerpo */}
                    <div
                        className="mb-[6mm] text-justify [&_p]:mb-[3.5mm]"
                        style={{
                            fontSize: '11.5pt',
                            lineHeight: 1.85,
                            color: '#1f2937',
                        }}
                    >
                        <style>{`.preview-strong strong { color: #1f2937; font-weight: bold; }`}</style>
                        <div className="preview-strong">
                            <CuerpoTexto c={c} />
                            {c.descripcion && <p>{c.descripcion}</p>}
                            <p>
                                Se expide la presente a solicitud del interesado, para los fines que crea convenientes.
                            </p>
                        </div>
                    </div>

                    {/* Lugar y fecha */}
                    <div
                        className="mt-[8mm] mb-[10mm] text-right"
                        style={{ fontSize: '10pt', color: '#4b5563' }}
                    >
                        {c.lugar_emision || 'Puno, Perú'}, {fechaEmisionLarga}.
                    </div>

                    {/* Firma + QR */}
                    <div className="mt-[6mm] flex items-end gap-[6mm]">
                        <div className="flex-[65] text-center">
                            {branding?.firma ? (
                                <img
                                    src={branding.firma}
                                    alt="Firma"
                                    className="mx-auto -mb-[2mm] block max-h-[22mm] max-w-[60mm] object-contain"
                                />
                            ) : (
                                <div style={{ height: '20mm' }} />
                            )}
                            <div
                                className="mx-[18mm] mb-[1.5mm]"
                                style={{ borderTop: '1px solid #1f2937' }}
                            />
                            <div className="font-bold" style={{ fontSize: '10.5pt', color: '#1f2937' }}>
                                {c.emisor_nombre || 'Ing. Roger Neptali Flores Coaquira'}
                            </div>
                            <div
                                className="mt-[0.5mm]"
                                style={{ fontSize: '9pt', color: '#6b7280' }}
                            >
                                {c.emisor_cargo || 'Consultor de Obras'}
                                {c.emisor_cip && <> · CIP {c.emisor_cip}</>}
                            </div>
                        </div>
                        <div className="flex-[35] text-right">
                            <div className="ml-auto" style={{ width: '26mm', height: '26mm' }}>
                                <QRCodeSVG
                                    value={urlVerificacion}
                                    level="H"
                                    marginSize={1}
                                    style={{ width: '100%', height: '100%' }}
                                />
                            </div>
                            <div
                                className="mt-[1.5mm] uppercase"
                                style={{ fontSize: '7pt', color: '#6b7280', letterSpacing: '0.8pt' }}
                            >
                                Verifica autenticidad
                            </div>
                            <div
                                className="mt-[0.5mm]"
                                style={{
                                    fontSize: '6.5pt',
                                    color: '#9ca3af',
                                    fontFamily: 'Courier New, monospace',
                                }}
                            >
                                {urlVerificacion.replace(/^https?:\/\//, '')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
