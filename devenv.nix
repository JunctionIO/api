{ pkgs, lib, config, ... }:
let
  php = pkgs.php84.withExtensions ({ enabled, all }: enabled ++ [
    all.pdo_pgsql
  ]);
in {
  packages = [
    php
  ] ++ lib.optionals (!config.container.isBuilding) [
    php.packages.composer
  ];

  services.postgres = {
    enable = !config.container.isBuilding;
    listen_addresses = "0.0.0.0";
    initialDatabases = [{ name = "junction"; user = "junction"; pass = "junction"; }];
  };

  processes.web.exec = "php -S 0.0.0.0:8000 -t www";

  enterShell = ''
    set +x
    set -a; [ -f .env ] && source .env; set +a
    export PATH="$PWD/vendor/bin:$PATH"
  '';
}
