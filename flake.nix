{
  description = "qbb";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-22.11";
  };

  outputs = { self, nixpkgs }:
  let
    pname = "qbb";
    description = "quick bundle builder";
    system = "x86_64-linux";
    pkgs = import nixpkgs {
      inherit system;
    };
    lib = nixpkgs.lib;
    bundle = pkgs.runCommand "qbb-bundle" {} ''
      mkdir -p $out
      cp ${./common.php} $out/common.php
      cp ${./server.php} $out/server.php
      cp ${./client.php} $out/client.php
    '';
  in
  {
    defaultPackage.${system} = bundle;

    nixosModules.default = { config, lib, pkgs, ... }: {
      options.services.qbb = with lib.types; {
        enable = lib.mkEnableOption description;

        configFile = lib.mkOption {
          type = path;
        };

        listenPort = lib.mkOption {
          type = ints.u16;
        };
      };

      config = lib.mkIf config.services.qbb.enable {
        systemd.services = {
          qbb-client = {
            description = "${description}-client";
            environment.QBB_CONFIG_FILE = config.services.qbb.configFile;
            serviceConfig = {
              Type = "oneshot";
              ExecStart = "${pkgs.php}/bin/php ${bundle}/client.php";
            };
          };

        qbb-server = {
            description = "${description}-server";
            wantedBy = ["multi-user.target"];
            after = ["network-online.target"];
            environment.QBB_CONFIG_FILE = config.services.qbb.configFile;
            serviceConfig = {
              DynamicUser = "yes";
              ExecStart = "${pkgs.php}/bin/php -S 0.0.0.0:${toString config.services.qbb.listenPort} ${bundle}/server.php";
            };
          };
        };
      };
    };

    devShell.${system} = with pkgs; mkShell {
      buildInputs = [
        php
      ];
    };
  };
}
