#!/usr/bin/env bash

set -euo pipefail

RUN_ONCE=false
if [[ "${1:-}" == "--run-once" ]]; then
  RUN_ONCE=true
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SCRIPT_PATH="$ROOT_DIR/scripts/$(basename "$0")"
# fleetbase-local: keep packages/ synced to vendor via composer path repo
WATCH_TARGETS=(
  "$ROOT_DIR/packages"
)

HOST_UID="$(id -u)"
HOST_GID="$(id -g)"
COMMON_FLAGS=(--no-interaction --prefer-dist --optimize-autoloader)
REQUIRED_EXTENSIONS=(ext-intl ext-bcmath ext-gd)

COMPOSER_CMD=()
COMPOSER_FLAGS=("${COMMON_FLAGS[@]}")
COMPOSER_LABEL="composer"
COMPOSER_MODE=""

have_compose_cli() {
  if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    COMPOSE_CLI=(docker compose)
    return 0
  fi

  if command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CLI=(docker-compose)
    return 0
  fi

  COMPOSE_CLI=()
  return 1
}

resolve_composer_command() {
  COMPOSER_FLAGS=("${COMMON_FLAGS[@]}")

  if command -v composer >/dev/null 2>&1; then
    COMPOSER_CMD=(composer)
    COMPOSER_MODE="host"
    COMPOSER_LABEL="local composer"
    return 0
  fi

  if have_compose_cli; then
    local app_container
    if app_container="$(${COMPOSE_CLI[@]} ps -q application 2>/dev/null)" && [[ -n "$app_container" ]]; then
      COMPOSER_CMD=("${COMPOSE_CLI[@]}" exec -T application composer)
      COMPOSER_MODE="compose"
      COMPOSER_LABEL="docker compose exec application"
      return 0
    fi
  fi

  if command -v docker >/dev/null 2>&1; then
    COMPOSER_CMD=(
      docker run --rm
      -v "$ROOT_DIR:/workspace"
      -w /workspace/api
      --user "$HOST_UID:$HOST_GID"
      composer:2
    )
    COMPOSER_MODE="docker-run"
    COMPOSER_LABEL="dockerized composer"
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
      COMPOSER_FLAGS+=("--ignore-platform-req=$ext")
    done
    return 0
  fi

  COMPOSER_CMD=()
  COMPOSER_MODE=""
  return 1
}

run_composer() {
  resolve_composer_command || true

  if [[ ${#COMPOSER_CMD[@]} -eq 0 ]]; then
    echo "[watch-packages] composer is not installed locally and docker is unavailable; skipping auto-install."
    return 0
  fi

  echo "[watch-packages] running composer install via $COMPOSER_LABEL"

  (
    set +e
    cd "$ROOT_DIR/api"
    "${COMPOSER_CMD[@]}" install "${COMPOSER_FLAGS[@]}"
  )
  status=$?

  if [[ $status -ne 0 ]]; then
    echo "[watch-packages] composer install failed (exit $status); watcher will retry on next change."
    return $status
  fi

  echo "[watch-packages] composer install completed"
}

run_composer || true

if $RUN_ONCE; then
  exit 0
fi

if command -v fswatch >/dev/null 2>&1; then
  echo "[watch-packages] watching packages/ with fswatch"
  fswatch -0 "${WATCH_TARGETS[@]}" | while read -r -d '' event; do
    echo "[watch-packages] change detected: $event"
    run_composer || true
  done
elif command -v entr >/dev/null 2>&1; then
  echo "[watch-packages] watching packages/ with entr"
  while true; do
    find "${WATCH_TARGETS[@]}" -type f | entr -d "$SCRIPT_PATH" --run-once
  done
else
  cat <<'EOF'
[watch-packages] install `fswatch` (e.g. `brew install fswatch`) or `entr` to enable automatic composer installs.
EOF
  exit 1
fi
