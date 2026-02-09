#!/bin/bash

# Error handling
set -e
trap 'echo "Error occurred at line $LINENO. Exiting."; cleanup_remote; exit 1' ERR

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Parse arguments
db_only=false
files_only=false
user=""
host=""

print_usage() {
  echo "Usage: $0 [OPTIONS] USER HOST"
  echo ""
  echo "Options:"
  echo "  --db              Sync only database"
  echo "  --files           Sync only files"
  echo "  --help            Show this help message"
  echo ""
  echo "Examples:"
  echo "  $0 user example.com"
  echo "  $0 --db user example.com"
  echo "  $0 --files user example.com"
}

for arg in "$@"; do
  case $arg in
    --db)
      db_only=true
      shift
      ;;
    --files)
      files_only=true
      shift
      ;;
    --help)
      print_usage
      exit 0
      ;;
    *)
      if [ -z "$user" ]; then
        user=$arg
      elif [ -z "$host" ]; then
        host=$arg
      fi
      ;;
  esac
done

# Validate arguments
if [ -z "$user" ] || [ -z "$host" ]; then
  echo -e "${RED}Error: USER and HOST are required${NC}"
  print_usage
  exit 1
fi

# If neither flag is set, sync both
if [ "$db_only" = false ] && [ "$files_only" = false ]; then
  sync_db=true
  sync_files=true
else
  sync_db=$db_only
  sync_files=$files_only
fi

address="${user}@${host}"
remote_path="/home/${user}/${host}"
local_sync_dir=".lando-sync"

# Function to print colored messages
log_info() {
  echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
  echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
  echo -e "${RED}[ERROR]${NC} $1"
}

# Function to cleanup remote files
cleanup_remote() {
  log_info "Cleaning up remote archives..."
  ssh -q -o StrictHostKeychecking=no ${address} "rm -rf ${remote_path}/.lando-sync" 2>/dev/null || true
}

# Function to cleanup local files
cleanup_local() {
  log_info "Cleaning up local archives..."
  rm -rf ${local_sync_dir}
}

# Check SSH connection
log_info "Testing SSH connection to ${address}..."
if ! ssh -q -o ConnectTimeout=10 -o BatchMode=yes ${address} exit 2>/dev/null; then
  log_error "Cannot connect to ${address}. Please check your SSH connection."
  exit 1
fi

log_info "Start syncing from server: ${address}"

# Check if remote directory exists
log_info "Checking remote directory structure..."
if ! ssh -q ${address} "[ -d ${remote_path} ]"; then
  log_error "Remote directory ${remote_path} does not exist!"
  exit 1
fi

# Check if Drush is available (only if syncing database)
if [ "$sync_db" = true ]; then
  if ! ssh -q ${address} "[ -f ${remote_path}/vendor/bin/drush ]"; then
    log_error "Drush not found at ${remote_path}/vendor/bin/drush"
    exit 1
  fi
fi

# Create archives on remote server
log_info "Creating archives on remote server..."
ssh -q -o loglevel=ERROR ${address} bash <<EOF
  set -e
  cd ${remote_path}

  mkdir -p .lando-sync

  $(if [ "$sync_db" = true ]; then echo "
    echo 'Creating database archive...'
    ./vendor/bin/drush archive:dump --db --destination=${remote_path}/.lando-sync/${host}-db.tar.gz --overwrite
    echo \"Database archive size: \$(du -h ${remote_path}/.lando-sync/${host}-db.tar.gz | cut -f1)\"
  "; fi)
  
  $(if [ "$sync_files" = true ]; then echo "
    echo 'Creating files archive...'
    cd ${remote_path}/web/sites/default
    zip -q -6 -r ${remote_path}/.lando-sync/${host}-files.zip files
    echo \"Files archive size: \$(du -h ${remote_path}/.lando-sync/${host}-files.zip | cut -f1)\"
  "; fi)
EOF

# Create local sync directory
mkdir -p ${local_sync_dir}

# Copy archives using rsync
if [ "$sync_db" = true ]; then
  log_info "Downloading database archive..."
  if ! rsync -az --progress ${address}:${remote_path}/.lando-sync/${host}-db.tar.gz ${local_sync_dir}/; then
    log_error "Failed to download database archive"
    cleanup_remote
    exit 1
  fi
fi

if [ "$sync_files" = true ]; then
  log_info "Downloading files archive..."
  if ! rsync -az --progress ${address}:${remote_path}/.lando-sync/${host}-files.zip ${local_sync_dir}/; then
    log_error "Failed to download files archive"
    cleanup_remote
    exit 1
  fi
fi

# Cleanup remote
cleanup_remote

# Extract and import
if [ "$sync_db" = true ]; then
  log_info "Extracting database archive..."
  tar -xzf ${local_sync_dir}/${host}-db.tar.gz -C ${local_sync_dir}
  
  if [ -f ${local_sync_dir}/database/database.sql ]; then
    log_info "Importing database..."
    lando drush sql-drop -y --quiet
    lando ssh -c "\$(drush sql-connect) < ${local_sync_dir}/database/database.sql"
    
    # Get database stats
    table_count=$(lando drush sqlq "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()" 2>/dev/null || echo "unknown")
    log_info "Database imported successfully (${table_count} tables)"
  else
    log_error "Database SQL file not found in archive!"
    cleanup_local
    exit 1
  fi
fi

if [ "$sync_files" = true ]; then
  log_info "Extracting files archive..."
  unzip -q ${local_sync_dir}/${host}-files.zip -d ${local_sync_dir}
  
  if [ -d ${local_sync_dir}/files ]; then
    log_info "Moving files..."
    rm -rf web/sites/default/files
    mv ${local_sync_dir}/files web/sites/default/
    
    # Get file stats
    file_count=$(find web/sites/default/files -type f | wc -l)
    total_size=$(du -sh web/sites/default/files | cut -f1)
    log_info "Files synced successfully (${file_count} files, ${total_size} total)"
  else
    log_error "Files directory not found in archive!"
    cleanup_local
    exit 1
  fi
fi

# Cleanup local
cleanup_local

# Run cache rebuild if database was synced
if [ "$sync_db" = true ]; then
  log_info "Rebuilding cache..."
  lando drush cr --quiet || log_warn "Cache rebuild failed"
fi

log_info "✓ Sync completed successfully!"

# Show summary
echo ""
echo "Summary:"
[ "$sync_db" = true ] && echo "  ✓ Database synced"
[ "$sync_files" = true ] && echo "  ✓ Files synced"
echo ""
