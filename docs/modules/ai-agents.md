# Módulo: Agentes de IA (`laravel/ai`)

Integración preparada con el paquete oficial **Laravel AI** para invocar modelos de lenguaje con salida estructurada.

## Estado actual

| Elemento | Estado |
|----------|--------|
| Paquete `laravel/ai` | Instalado y configurado |
| `config/ai.php` | Presente |
| Tablas `agent_conversations*` | Migradas |
| Carpeta `app/Ai/Agents/` | **No creada aún** — sin agentes implementados |
| Rutas en `routes/api.php` | Sin endpoints de IA |

## Paquete

- **Nombre**: `laravel/ai` ^0.6.8
- **Config**: `config/ai.php`
- **Proveedor por defecto**: Gemini (`GEMINI_API_KEY`)
- **Proveedor alternativo configurado**: OpenAI (`OPENAI_API_KEY`) — no usado actualmente

## Tablas de conversación

Migración: `database/migrations/2026_05_17_021935_create_agent_conversations_table.php`

| Tabla | Propósito |
|-------|-----------|
| `agent_conversations` | Metadatos de conversación |
| `agent_conversation_messages` | Historial con attachments, tool_calls, usage |

Preparadas para persistir historial multi-turno cuando se implementen agentes.

## Agregar un agente

1. Crear `app/Ai/Agents/` y la clase del agente implementando `Agent` (y opcionalmente `HasStructuredOutput`, `HasTools`).
2. Atributos `#[Provider(Lab::...)]` y `#[Timeout(n)]`.
3. Invocar desde controlador con `->prompt()` o flujos conversacionales de `laravel/ai`.
4. Registrar ruta en `routes/api.php` bajo `auth:api`.
5. Documentar en este archivo y en [integration.md](../integration.md).
6. Crear tests Feature/Unit que cubran la invocación y la validación de entrada.

### Ejemplo de estructura (referencia)

```php
// app/Ai/Agents/ExampleAgent.php
$response = (new ExampleAgent)->prompt(
    prompt: 'Instrucción al modelo',
    attachments: [$request->file('file')], // opcional
);

$response->toArray();
$response->usage->toArray();
```

Consultar documentación del paquete vía Laravel Boost `search-docs` con queries como `structured output`, `attachments`, `agent`.

## Variables de entorno

| Variable | Requerida | Descripción |
|----------|-----------|-------------|
| `GEMINI_API_KEY` | Sí (si se usa Gemini) | API key del proveedor |
| `OPENAI_API_KEY` | No | Alternativa no activa por defecto |

## Relaciones

- **Config**: claves de proveedor en `.env`
- **Frontend**: consumidor futuro vía nuevas rutas API y páginas en `argos.frontend`
