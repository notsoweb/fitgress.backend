#!/usr/bin/env bash
# Reindexa el repositorio en Codebase Memory MCP tras un git commit exitoso.
# Ejecuta en segundo plano para no bloquear al agente.

set -euo pipefail

input=$(cat)

command=$(
  echo "$input" | jq -r '
    .command
    // .tool_input.command
    // .toolInput.command
    // empty
  ' 2>/dev/null || true
)

exit_code=$(
  echo "$input" | jq -r '
    .exit_code
    // .exitCode
    // .tool_output.exitCode
    // .toolOutput.exitCode
    // "0"
  ' 2>/dev/null || echo "0"
)

# Solo commits exitosos
if [[ "$exit_code" != "0" ]]; then
  exit 0
fi

# git commit (no commit-msg hooks ni otros subcomandos)
if ! [[ "$command" =~ git[[:space:]]+commit ]]; then
  exit 0
fi

if ! command -v codebase-memory-mcp >/dev/null 2>&1; then
  exit 0
fi

repo_path="$(pwd)"
payload=$(jq -nc --arg path "$repo_path" '{repo_path: $path, mode: "fast"}')
log_file="${TMPDIR:-/tmp}/codebase-memory-reindex.log"

(
  echo "=== $(date -Iseconds) reindex $repo_path ==="
  codebase-memory-mcp cli index_repository "$payload"
) >>"$log_file" 2>&1 &

disown 2>/dev/null || true

exit 0
