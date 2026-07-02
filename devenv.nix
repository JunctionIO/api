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
    pkgs.jq
  ];

  services.postgres = {
    enable = !config.container.isBuilding;
    listen_addresses = "0.0.0.0";
    initialDatabases = [{ name = "junction"; user = "junction"; pass = "junction"; }];
  };

  services.rabbitmq = {
    enable = !config.container.isBuilding;
  };

  processes.web.exec = "stdbuf -oL php -S 0.0.0.0:8000 -t www 2>/dev/null | jq --unbuffered -R 'try fromjson catch .'";

  containers.api = {
    name = "api";
    entrypoint = [ "/app/docker/entrypoint.sh" ];
    copyToRoot = pkgs.buildEnv {
      name = "api-app";
      paths = [
        (pkgs.runCommand "app" { } ''
          mkdir -p $out/app/{src,www,vendor,docker}
          cp -r ${./src}/. $out/app/src/
          cp -r ${./www}/. $out/app/www/
          cp -r ${./vendor}/. $out/app/vendor/
          cp ${./docker/entrypoint.sh} $out/app/docker/entrypoint.sh
          chmod +x $out/app/docker/entrypoint.sh
        '')
      ];
    };
  };

  enterShell = ''
    set +x
    set -a; [ -f .env ] && source .env; set +a
    export PATH="$PWD/vendor/bin:$PATH"
  '';
}
