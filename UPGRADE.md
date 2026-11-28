# UPGRADE FROM `v2.2.X` TO `v2.3.0`

* Symfony 6.4 is no longer supported: the minimum required version is now Symfony 7.4.
  Composer keeps projects on Symfony 6.4 on `v2.2.x`.
* PHP 8.2 is now supported (with Sylius 2.2 and Symfony 7.4).

# UPGRADE FROM `v2.1.2` TO `v2.2.0`

* Minimum requirements have been raised to PHP 8.3, Sylius 2.2 and Symfony 6.4 or 7.4.
  Symfony 7.0 to 7.3 are no longer supported. Composer keeps projects on an older stack on `v2.1.x`.
* Symfony 8 (with Sylius 2.3 and PHP 8.4) is now supported.

## Behaviour changes

These changes fix bugs and need no action:

* During maintenance, a URL that does not exist now shows the maintenance page (503) instead of a 404.
* On a multi-channel store, a request whose host matches no channel now shows the maintenance page (503) instead of an error (500).
  The Symfony profiler stays reachable on such hosts.
* When no token is configured, an empty token (`?synolia_maintenance_token=`, an empty `synolia_maintenance_token` header
  or an empty token in session) no longer bypasses the maintenance.
