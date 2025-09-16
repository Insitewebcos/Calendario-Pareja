# ============================================
# ARCHIVO .env FINAL PARA SERVIDOR
# ============================================
# *** CREDENCIALES CORRECTAS CONFIRMADAS ***
# Subir como .env al servidor calendario.insiteweb.es
#

# ============================================
# ENTORNO
# ============================================
APP_NAME="Sistema de Calendario"
APP_VERSION="1.0.0"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE="Europe/Madrid"

# ============================================
# BASE DE DATOS DE PRODUCCIÓN (CORRECTAS)
# ============================================
DB_HOST=db5018588394.hosting-data.io
DB_PORT=3306
DB_NAME=dbs14744038
DB_USER=dbu1865109
DB_PASS=ZT%9cGVrEIorb1qy
DB_CHARSET=utf8mb4

# ============================================
# URLs DE PRODUCCIÓN
# ============================================
BASE_URL="http://calendario.insiteweb.es/"
SITE_URL="http://calendario.insiteweb.es/"
ASSETS_URL="http://calendario.insiteweb.es/assets/"

# ============================================
# SESIONES EN PRODUCCIÓN
# ============================================
SESSION_NAME=calendario_session
SESSION_TIMEOUT=3600
SESSION_SECURE=false

# ============================================
# SEGURIDAD EN PRODUCCIÓN
# ============================================
APP_SECRET_KEY=insiteweb-calendario-production-key-2025-supersecret-change-this
CSRF_SECRET=csrf-insiteweb-production-unique-token-2025-change-this

# ============================================
# LOGS Y DEBUG EN PRODUCCIÓN
# ============================================
LOG_LEVEL=error
LOG_ERRORS=true
DISPLAY_ERRORS=false

# ============================================
# FEATURES
# ============================================
CACHE_ENABLED=false
FEATURE_EXPORT_PDF=false
FEATURE_EMAIL_NOTIFICATIONS=false
FEATURE_USER_REGISTRATION=false