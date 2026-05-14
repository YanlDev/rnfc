# Browsershot (PDF de certificados) — Setup en VPS

El sistema usa **Spatie Browsershot** para generar los PDFs de certificados.
Browsershot ejecuta Chrome headless vía Puppeteer (Node.js), por eso el VPS
necesita: Node.js + Puppeteer + Chromium.

## Instalación en Ubuntu / Debian

```bash
# 1) Node.js (LTS)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# 2) Dependencias del sistema que Chromium necesita
sudo apt-get install -y \
    libnss3 libatk-bridge2.0-0 libdrm2 libgbm1 libgtk-3-0 \
    libasound2 libxcomposite1 libxdamage1 libxrandr2 \
    libxkbcommon0 libxshmfence1 libpangocairo-1.0-0 \
    fonts-liberation libappindicator3-1 xdg-utils

# 3) Instalar puppeteer + Chromium globalmente
sudo npm install --global --unsafe-perm puppeteer
```

Verifica que quedó instalado:

```bash
which node       # /usr/bin/node
which npm        # /usr/bin/npm
node -e "console.log(require('puppeteer').executablePath())"
```

## Configurar paths en `.env` (opcional pero recomendado)

Si `node` y el ejecutable de Chromium están en rutas no estándar, agrégalas:

```env
NODE_PATH=/usr/bin/node
NPM_PATH=/usr/bin/npm
CHROME_PATH=/root/.cache/puppeteer/chrome/linux-XXX/chrome-linux64/chrome
```

Si están en `PATH` y Puppeteer está globalmente accesible, puedes dejar las
variables vacías — Browsershot las detecta solo.

## Permisos

Asegúrate que el usuario `www-data` (o el que ejecuta PHP-FPM) pueda leer
node, npm y el Chromium descargado por Puppeteer. Generalmente con
`--unsafe-perm` en el npm install global queda bien.

## Verificar localmente

```bash
php artisan tinker
>>> Spatie\Browsershot\Browsershot::html('<h1>Hola</h1>')->pdf();
```

Si no lanza excepción y devuelve binario PDF, está OK.
