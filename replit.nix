{ pkgs }: {
  deps = [
    (pkgs.php.withExtensions ({ enabled, all }:
      enabled ++ (with all; [ mbstring pgsql ])))
  ];
}
