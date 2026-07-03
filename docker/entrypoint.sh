#!/bin/sh
set -e

mkdir -p /tmp/php-ini

cat > /tmp/php-ini/custom.ini << EOF
memory_limit = ${PHP_MEMORY_LIMIT:-128M}
max_execution_time = ${PHP_MAX_EXECUTION_TIME:-30}
upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE:-2M}
post_max_size = ${PHP_POST_MAX_SIZE:-8M}
EOF

# NIX_PHP_INI_DIR (set by the container's entrypoint wrapper) points at
# the Nix-built php's own ini scan dir, where all the enabled extensions'
# .ini files live. Appending rather than replacing PHP_INI_SCAN_DIR keeps
# those extensions loaded - overwriting it entirely leaves php-fpm with
# none of them (e.g. filter_var() undefined), even though `php -m` looks
# fine when php is invoked directly (that goes through its own baked-in
# default scan dir, not this override).
export PHP_INI_SCAN_DIR="/tmp/php-ini:${NIX_PHP_INI_DIR:-}"

cat > /tmp/php-fpm.conf << EOF
[global]
error_log = /dev/stderr
daemonize = no

[www]
listen = 0.0.0.0:9000
; FPM clears the worker environment by default unless told not to -
; without this, every getenv()-based config value (JWT_SECRET, DB_*,
; QUEUE_*) silently reads back empty inside request handling, even
; though the container's own env clearly has them set.
clear_env = no
pm = ${PHP_FPM_PM:-dynamic}
pm.max_children = ${PHP_FPM_PM_MAX_CHILDREN:-5}
pm.start_servers = ${PHP_FPM_PM_START_SERVERS:-2}
pm.min_spare_servers = ${PHP_FPM_PM_MIN_SPARE_SERVERS:-1}
pm.max_spare_servers = ${PHP_FPM_PM_MAX_SPARE_SERVERS:-3}
access.log = /dev/stdout
catch_workers_output = yes
decorate_workers_output = no
EOF

exec php-fpm --nodaemonize --fpm-config /tmp/php-fpm.conf
