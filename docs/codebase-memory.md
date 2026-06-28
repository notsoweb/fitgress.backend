# Codebase Memory MCP

Herramienta de búsqueda y análisis del código indexado. **Usar en primera instancia** antes de grep, glob o lectura masiva de archivos.

## Proyecto indexado

| Campo | Valor |
|-------|-------|
| Nombre MCP | `var-www-mdev-argos.backend` |
| Ruta | `/var/www/mdev/argos.backend` |

Verificar con `list_projects` si el índice está disponible.

## Flujo recomendado

```
1. get_architecture     → panorama de módulos, hotspots y carpetas
2. search_graph         → localizar clases, métodos, rutas por nombre o lenguaje natural
3. search_code          → patrones de texto con contexto estructural
4. get_code_snippet     → leer implementación de un símbolo concreto
5. trace_path           → seguir llamadas entre funciones
```

## Herramientas principales

| Herramienta | Cuándo usarla |
|-------------|---------------|
| `get_architecture` | Entender estructura, clusters y puntos calientes al iniciar una tarea |
| `search_graph` | Buscar definiciones (`AuthController`, `login`, rutas) |
| `search_code` | Buscar strings, imports o patrones en archivos concretos |
| `get_code_snippet` | Leer el cuerpo de un método/clase ya localizado |
| `query_graph` | Consultas de relaciones (quién llama a quién, dependencias) |
| `index_status` | Comprobar si el repositorio está indexado y actualizado |

## Parámetros útiles

### search_graph

```json
{
  "project": "var-www-mdev-argos.backend",
  "query": "passkey authentication",
  "label": "Method",
  "file_pattern": "app/Http/**"
}
```

### search_code

```json
{
  "project": "var-www-mdev-argos.backend",
  "pattern": "ApiResponse::OK",
  "path_filter": "^app/Http/",
  "mode": "compact",
  "limit": 15
}
```

## Cuándo NO usar solo el MCP

- Cambios en `.env`, migraciones nuevas o código no indexado aún → leer archivos directamente.
- Documentación en `docs/` → leer el markdown correspondiente.
- Documentación de paquetes Laravel → usar Laravel Boost `search-docs`.

## Mantener el índice actualizado

Tras cambios estructurales grandes (nuevos módulos, refactor de carpetas), ejecutar `index_repository` manualmente o reiniciar el servidor MCP.

### Reindexado automático al commit

Cada proyecto tiene un hook de Cursor (`.cursor/hooks/reindex-on-commit.sh`) que, tras un `git commit` exitoso, ejecuta en segundo plano:

```bash
codebase-memory-mcp cli index_repository '{"repo_path":"<ruta>","mode":"fast"}'
```

- Modo `fast` con indexado incremental (solo archivos cambiados).
- Log: `/tmp/codebase-memory-reindex.log`
- Requiere `codebase-memory-mcp` en `$PATH`.
