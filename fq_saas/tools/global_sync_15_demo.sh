#!/usr/bin/env bash
set -euo pipefail

BASE_DIR="/var/www/crm.flowquest.pl"
MAP_FILE="$BASE_DIR/modules/fq_saas/tools/tenant_sync_map.tsv"
LOGO_SRC_DIR="$BASE_DIR/demo-logos"
DEFAULT_LOGO_FALLBACK="/var/www/crm.flowquest.pl/uploads/tenants/go/company/flowquest-logo-light.png"
REPORT_DIR="$BASE_DIR/modules/reports"
NOW_UTC="$(date -u +"%Y-%m-%d %H:%M:%S UTC")"
TS="$(date -u +"%Y%m%d_%H%M%S")"
REPORT_MD="$REPORT_DIR/tenant_global_sync_${TS}.md"
REPORT_LATEST="$BASE_DIR/modules/FQ_GLOBAL_SYNC_LAST.md"

mkdir -p "$REPORT_DIR"

CORE_MODULES=(einvoice form_sync menu_setup theme_style)

mysql_q() {
  mysql -N -e "$1"
}

table_like() {
  local db="$1"
  local pattern="$2"
  mysql_q "SHOW TABLES IN ${db} LIKE '${pattern}';" | head -n1 || true
}

ensure_option() {
  local db="$1" tbl="$2" name="$3" value="$4"
  local esc
  esc="$(printf "%s" "$value" | sed "s/'/''/g")"
  mysql -D "$db" -e "INSERT INTO ${tbl} (name,value,autoload) VALUES ('${name}','${esc}',1)
    ON DUPLICATE KEY UPDATE value=VALUES(value);"
}

ensure_module_active() {
  local db="$1" tbl="$2" module="$3"
  [ -z "$module" ] && return 0
  [ "$module" = "-" ] && return 0
  [ "$module" = "core" ] && return 0
  mysql -D "$db" -e "INSERT INTO ${tbl} (module_name,installed_version,active) VALUES ('${module}','1.0.0',1)
    ON DUPLICATE KEY UPDATE active=1;"
}

dedupe_options() {
  local db="$1" tbl="$2"
  mysql -D "$db" -e "DELETE o1 FROM ${tbl} o1
    JOIN ${tbl} o2 ON o1.name=o2.name AND o1.id<o2.id
    WHERE o1.name IN ('company_logo','company_logo_dark');"
}

dedupe_modules() {
  local db="$1" tbl="$2"
  mysql -D "$db" -e "DELETE m1 FROM ${tbl} m1
    JOIN ${tbl} m2 ON m1.module_name=m2.module_name AND m1.id<m2.id;"
}

{
  echo "# Global Sync 15 Demo"
  echo
  echo "Data: ${NOW_UTC}"
  echo
  echo "## Założenia"
  echo "- Brak zmian w core."
  echo "- Synchronizacja ustawień tenantów: branding + moduły bazowe."
  echo "- Źródło mapowania: \`modules/fq_saas/tools/tenant_sync_map.tsv\`."
  echo
  echo "## Wyniki"
  echo
  echo "| Slug | HTTP | Logo | company_logo_dark | Tabela options | Tabela modules | Core modules | Branżowy | Dodatkowe | Status |"
  echo "|---|---:|---|---|---|---|---|---|---|---|"
} > "$REPORT_MD"

tail -n +2 "$MAP_FILE" | while IFS=$'\t' read -r slug logo_source industry_module extra_modules; do
  db="ps_${slug}"
  options_tbl="$(table_like "$db" "%tbloptions")"
  modules_tbl="$(table_like "$db" "%tblmodules")"
  status="OK"
  logo_status="unchanged"
  dark_status="unchanged"

  http_code="$(curl -k -L -s -o /dev/null -w "%{http_code}" "https://${slug}.flowquest.pl/admin/authentication?demo_account=owner" || true)"
  [ -z "$http_code" ] && http_code="000"
  if [ "$http_code" != "200" ] && [ "$status" = "OK" ]; then
    status="PARTIAL"
  fi

  if [ -z "$options_tbl" ] || [ -z "$modules_tbl" ]; then
    status="MISSING_TABLES"
    echo "| ${slug} | ${http_code} | - | - | ${options_tbl:-none} | ${modules_tbl:-none} | - | - | - | ${status} |" >> "$REPORT_MD"
    continue
  fi

  # Branding
  if [ "$logo_source" = "-" ]; then
    logo_source=""
  fi
  if [ "$extra_modules" = "-" ]; then
    extra_modules=""
  fi

  if [ -n "$logo_source" ] && [ -f "$LOGO_SRC_DIR/$logo_source" ]; then
    dst_dir="$BASE_DIR/uploads/tenants/${slug}/company"
    shared_dst_dir="$BASE_DIR/uploads/company"
    dst_file="fq_${slug}_logo.png"
    mkdir -p "$dst_dir"
    mkdir -p "$shared_dst_dir"
    cp -f "$LOGO_SRC_DIR/$logo_source" "$dst_dir/$dst_file"
    cp -f "$LOGO_SRC_DIR/$logo_source" "$shared_dst_dir/$dst_file"
    chown www-data:www-data "$dst_dir/$dst_file" "$shared_dst_dir/$dst_file"
    chmod 0644 "$dst_dir/$dst_file" "$shared_dst_dir/$dst_file"
    ensure_option "$db" "$options_tbl" "company_logo" "$dst_file"
    ensure_option "$db" "$options_tbl" "company_logo_dark" "$dst_file"
    logo_status="$dst_file"
    dark_status="$dst_file"
  elif [ -n "$logo_source" ]; then
    logo_status="missing_source:${logo_source}"
    dark_status="missing_source:${logo_source}"
    status="PARTIAL"
  else
    existing_logo="$(mysql -N -D "$db" -e "SELECT value FROM ${options_tbl} WHERE name='company_logo' ORDER BY id DESC LIMIT 1;" || true)"
    static_logo="fq_${slug}_logo.png"
    if [ -n "$existing_logo" ]; then
      ensure_option "$db" "$options_tbl" "company_logo_dark" "$existing_logo"
      logo_status="$existing_logo"
      dark_status="$existing_logo"
    elif [ -f "$BASE_DIR/uploads/company/$static_logo" ]; then
      dst_dir="$BASE_DIR/uploads/tenants/${slug}/company"
      mkdir -p "$dst_dir"
      cp -f "$BASE_DIR/uploads/company/$static_logo" "$dst_dir/$static_logo"
      chown www-data:www-data "$dst_dir/$static_logo"
      chmod 0644 "$dst_dir/$static_logo"
      ensure_option "$db" "$options_tbl" "company_logo" "$static_logo"
      ensure_option "$db" "$options_tbl" "company_logo_dark" "$static_logo"
      logo_status="$static_logo"
      dark_status="$static_logo"
    elif [ "$slug" = "demo" ] && [ -f "$DEFAULT_LOGO_FALLBACK" ]; then
      dst_dir="$BASE_DIR/uploads/tenants/${slug}/company"
      dst_file="fq_${slug}_logo.png"
      mkdir -p "$dst_dir"
      cp -f "$DEFAULT_LOGO_FALLBACK" "$dst_dir/$dst_file"
      chown www-data:www-data "$dst_dir/$dst_file"
      chmod 0644 "$dst_dir/$dst_file"
      ensure_option "$db" "$options_tbl" "company_logo" "$dst_file"
      ensure_option "$db" "$options_tbl" "company_logo_dark" "$dst_file"
      logo_status="$dst_file"
      dark_status="$dst_file"
    else
      logo_status="missing_logo"
      dark_status="missing_logo"
      status="PARTIAL"
    fi
  fi

  # Core modules
  for m in "${CORE_MODULES[@]}"; do
    ensure_module_active "$db" "$modules_tbl" "$m"
  done

  # Industry module + extra modules
  ensure_module_active "$db" "$modules_tbl" "$industry_module"
  IFS=',' read -r -a extras <<< "$extra_modules"
  for m in "${extras[@]}"; do
    m_trim="$(echo "$m" | xargs)"
    [ -n "$m_trim" ] && ensure_module_active "$db" "$modules_tbl" "$m_trim"
  done

  dedupe_options "$db" "$options_tbl"
  dedupe_modules "$db" "$modules_tbl"

  active_core="$(mysql -N -D "$db" -e "SELECT GROUP_CONCAT(DISTINCT module_name ORDER BY module_name SEPARATOR ',') FROM ${modules_tbl} WHERE active=1 AND module_name IN ('einvoice','form_sync','menu_setup','theme_style');" || true)"
  [ -z "$active_core" ] && active_core="-"

  echo "| ${slug} | ${http_code} | ${logo_status} | ${dark_status} | ${options_tbl} | ${modules_tbl} | ${active_core} | ${industry_module:--} | ${extra_modules:--} | ${status} |" >> "$REPORT_MD"
done

{
  echo
  echo "## Podsumowanie"
  ok_count="$(awk -F'|' '/^\| [a-z0-9]+ / {gsub(/ /,"",$11); if($11=="OK") c++} END{print c+0}' "$REPORT_MD")"
  part_count="$(awk -F'|' '/^\| [a-z0-9]+ / {gsub(/ /,"",$11); if($11=="PARTIAL") c++} END{print c+0}' "$REPORT_MD")"
  miss_count="$(awk -F'|' '/^\| [a-z0-9]+ / {gsub(/ /,"",$11); if($11=="MISSING_TABLES") c++} END{print c+0}' "$REPORT_MD")"
  echo "- OK: ${ok_count}"
  echo "- PARTIAL: ${part_count}"
  echo "- MISSING_TABLES: ${miss_count}"
} >> "$REPORT_MD"

cp -f "$REPORT_MD" "$REPORT_LATEST"
chown www-data:www-data "$REPORT_MD" "$REPORT_LATEST"
chmod 0644 "$REPORT_MD" "$REPORT_LATEST"

echo "Report: $REPORT_MD"
echo "Latest: $REPORT_LATEST"
