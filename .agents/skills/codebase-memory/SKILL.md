---
name: codebase-memory
description: Explora el código del backend Argos usando el grafo indexado de Codebase Memory MCP. Usar en primera instancia al investigar arquitectura, localizar clases, métodos, rutas o dependencias antes de grep o lectura masiva de archivos.
---

# Codebase Memory — Backend

## Proyecto MCP

`var-www-mdev-fitgress.backend`

## Flujo

1. `list_projects` / `index_status` — verificar índice
2. `get_architecture` — estructura, hotspots, clusters
3. `search_graph` — búsqueda semántica o por patrón
4. `search_code` — grep enriquecido con ranking estructural
5. `get_code_snippet` — leer código de un símbolo localizado
6. `trace_path` — seguir cadena de llamadas

## Ejemplos

### Encontrar un controlador

```json
{ "project": "var-www-mdev-fitgress.backend", "query": "passkey login", "label": "Method" }
```

### Buscar patrón en HTTP

```json
{
  "project": "var-www-mdev-fitgress.backend",
  "pattern": "HasMiddleware",
  "path_filter": "^app/Http/",
  "mode": "compact",
  "limit": 15
}
```

## Documentación

Detalle en [docs/codebase-memory.md](../../docs/codebase-memory.md).

## Prioridad

Usar **antes** de grep/glob cuando se buscan definiciones o relaciones entre código.
