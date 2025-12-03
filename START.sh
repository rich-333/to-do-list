#!/bin/bash
#
# INSTRUCCIONES DE INICIO - Lista de Tareas
#
# Para empezar a usar tu nueva lista de tareas, sigue estos pasos:
#

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║          LISTA DE TAREAS - GUÍA DE INICIO RÁPIDO             ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""

# Paso 1: Instalar dependencias
echo "1️⃣  INSTALAR DEPENDENCIAS"
echo "   Ejecuta:"
echo "   $ npm install"
echo ""

# Paso 2: Compilar assets
echo "2️⃣  COMPILAR ASSETS (desarrollo)"
echo "   Ejecuta:"
echo "   $ npm run dev"
echo "   Mantenén esta ventana abierta mientras desarrollas"
echo ""

# Paso 3: Iniciar servidor
echo "3️⃣  INICIAR SERVIDOR LARAVEL"
echo "   En otra ventana, ejecuta:"
echo "   $ php artisan serve"
echo ""

# Paso 4: Acceder a la página
echo "4️⃣  ACCEDER A LA APLICACIÓN"
echo "   Abre tu navegador en:"
echo "   🔗 http://localhost:8000/tasks"
echo ""

# Paso 5: Ver demo
echo "5️⃣  VER DEMO CON DATOS DE EJEMPLO"
echo "   Accede a:"
echo "   🔗 http://localhost:8000/task-list-preview"
echo ""

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                      COMANDOS ÚTILES                        ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""
echo "Desarrollo:"
echo "  npm run dev          → Compilar con hot reload"
echo "  npm run build        → Compilar para producción"
echo ""
echo "Laravel:"
echo "  php artisan serve    → Iniciar servidor"
echo "  php artisan migrate  → Ejecutar migraciones"
echo "  php artisan tinker   → Consola interactiva"
echo ""
echo "Base de Datos:"
echo "  php artisan migrate:fresh     → Resetear BD"
echo "  php artisan db:seed           → Poblar con datos"
echo ""

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                    DOCUMENTACIÓN                             ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""
echo "Lee estos archivos en orden:"
echo "  1. RESUMEN_FINAL.md          (Resumen de la implementación)"
echo "  2. QUICKSTART.md             (Guía rápida)"
echo "  3. VISUAL_PREVIEW.md         (Cómo se ve visualmente)"
echo "  4. docs/TASK_LIST_GUIDE.md   (Guía completa)"
echo ""

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║                    ¿NECESITAS AYUDA?                         ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""
echo "Problemas comunes:"
echo "  • No carga la página"
echo "    → Verifica que estés autenticado"
echo "    → Ejecuta: npm run dev"
echo ""
echo "  • No se guarda la tarea"
echo "    → Abre la consola (F12) y busca errores"
echo "    → Revisa storage/logs/laravel.log"
echo ""
echo "  • CSS no se aplica"
echo "    → Ejecuta: npm run dev"
echo "    → Limpia caché: Ctrl+F5"
echo ""

echo "╔═══════════════════════════════════════════════════════════════╗"
echo "║           ✅ ¡LISTO PARA EMPEZAR A USAR!                     ║"
echo "╚═══════════════════════════════════════════════════════════════╝"
echo ""
