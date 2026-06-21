# Módulo: Agentes de IA (`laravel/ai`)

Integración con el paquete oficial **Laravel AI** para invocar modelos de lenguaje con salida estructurada.

## Paquete

- **Nombre**: `laravel/ai` ^0.6.8
- **Config**: `config/ai.php`
- **Proveedor por defecto**: Gemini (`GEMINI_API_KEY`)
- **Proveedor alternativo configurado**: OpenAI (`OPENAI_API_KEY`) — no usado actualmente

## Agente registrado

### ProcessDocument

**Archivo**: `app/Ai/Agents/ProcessDocument.php`

| Atributo | Valor |
|----------|-------|
| Interfaces | `Agent`, `HasStructuredOutput` |
| Trait | `Promptable` |
| Proveedor | `#[Provider(Lab::Gemini)]` |
| Timeout | 512 segundos |
| Tools | Ninguno (solo salida estructurada) |

### Métodos principales

| Método | Descripción |
|--------|-------------|
| `instructions()` | System prompt extenso en español (análisis de licitaciones MX) |
| `schema(JsonSchema $schema)` | Define el JSON de salida con tipos estrictos |

### Invocación

```php
$response = (new ProcessDocument)->prompt(
    prompt: 'Analiza este documento PDF',
    attachments: [$request->file('file')],
);

$response->toArray();      // análisis estructurado
$response->usage->toArray(); // tokens consumidos
```

## Tablas de conversación (no usadas aún)

Migración: `database/migrations/2026_05_17_021935_create_agent_conversations_table.php`

| Tabla | Propósito |
|-------|-----------|
| `agent_conversations` | Metadatos de conversación |
| `agent_conversation_messages` | Historial con attachments, tool_calls, usage |

Preparadas para persistir historial multi-turno; `LiciaController` no las utiliza.

## Agregar un nuevo agente

1. Crear clase en `app/Ai/Agents/` implementando `Agent` (y opcionalmente `HasStructuredOutput`, `HasTools`).
2. Atributos `#[Provider(Lab::...)]` y `#[Timeout(n)]`.
3. Invocar desde controlador con `->prompt()` o flujos conversacionales de `laravel/ai`.
4. Documentar ruta en `routes/api.php`.

Consultar documentación del paquete vía Laravel Boost `search-docs` con queries como `structured output`, `attachments`, `agent`.

## Relaciones

- **LICIA**: único consumidor actual del agente `ProcessDocument`.
- **Config**: `GEMINI_API_KEY` obligatoria para el módulo LICIA en producción.
