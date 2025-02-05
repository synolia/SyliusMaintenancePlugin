[![License](https://badgen.net/github/license/synolia/SyliusMaintenancePlugin)](https://github.com/synolia/SyliusMaintenancePlugin/blob/master/LICENSE)
[![CI - Analysis](https://github.com/synolia/SyliusMaintenancePlugin/actions/workflows/analysis.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusMaintenancePlugin/actions/workflows/analysis.yaml)
[![CI - Sylius](https://github.com/synolia/SyliusMaintenancePlugin/actions/workflows/sylius.yaml/badge.svg?branch=master)](https://github.com/synolia/SyliusMaintenancePlugin/actions/workflows/sylius.yaml)
[![Version](https://badgen.net/github/tag/synolia/SyliusMaintenancePlugin?label=Version)](https://packagist.org/packages/synolia/sylius-maintenance-plugin)
[![Total Downloads](https://poser.pugx.org/synolia/sylius-maintenance-plugin/downloads)](https://packagist.org/packages/synolia/sylius-maintenance-plugin)

<p align="center">
    <a href="https://sylius.com" target="_blank">
        <img src="https://demo.sylius.com/assets/shop/img/logo.png" />
    </a>
</p>

<h1 align="center">Sylius Maintenance Plugin</h1>

## Features

### When your website is under maintenance, and you want to :

* Do not allow access to your website and display the message "the website is under maintenance" on the frontpage.
* Allow access to your website to some Ips addresses or secret token
* Activate and deactivate these behaviors by commands
* Activate and deactivate behaviors in your Sylius Back-office
* Custom your message in your Sylius Back-office
* Allow access to search bots to avoid negative impact on SEO

![Alt text](images/maintenance.png "maintenance_configure")

## Requirements

|        | Version |
|:-------|:--------|
| PHP    | ^8.2    |
| Sylius | ^1.12   |

## Installation

1. Add the bundle and dependencies in your composer.json :

    ``` shell   
    composer require synolia/sylius-maintenance-plugin
    ```

2. Import routing in your `config/routes.yaml` file:

    ``` yaml   
    synolia_maintenance:
        resource: "@SynoliaSyliusMaintenancePlugin/Resources/config/admin_routing.yaml"
        prefix: '/%sylius_admin.path_name%'
    ```

3. Clear cache

    ``` shell
    php bin/console cache:clear
    ```

## Usage

- To turn your website under maintenance, please create a file **maintenance.yaml** at the root of your project.
- If you want to allow access for some Ips, please add these Ip into **maintenance.yaml**   
  For example :

    ``` yaml   
    ips: [172.16.254.1, 255.255.255.255, 192.0.0.255]
    ```

### You can turn your website under maintenance by console commands :

1. Enable the plugin

    ``` shell
    php bin/console maintenance:enable
    ```
2. Enable the plugin and add one or multiple ips addresses separated with a space

    ``` shell
    php bin/console maintenance:enable 172.16.254.1 255.255.255.255 192.0.0.255
    ```
3.Enable the plugin and disable admin access

    ``` shell
    php bin/console maintenance:enable --disable-admin
    ```
4.Disable the plugin

    ``` shell
    php bin/console maintenance:disable
    ```

5.Remove configuration file using CLI

By default, **maintenance.yaml** configuration file remains when running `maintenance:disable` or via admin panel using toggle disable
Nevertheless passing option `[-c|--clear]` to command line above will reset previous saved configuration

### You can also turn your website under maintenance in Back Office :

- Enable/disable the plugin
- Allow access for one or multiple ips addresses (optional)
- Allow access for secret token (session and request) (optional)
- Create your custom message (optional)
- Grant access to search bots during maintenance (optional)
- Grant access to admins during maintenance (optional)

### If you want to put the **maintenance.yaml** in a directory, please add your directory in .env:

For example :

``` yaml 
 SYNOLIA_MAINTENANCE_DIR=var/maintenance
```

### If you want to add cache on the **maintenance.yaml**:

``` yaml 
# .env
SYNOLIA_MAINTENANCE_CACHE=30 # ttl in seconds
```

And in project code (for example with redis)

``` yaml 
# config/packages/prod/cache.yaml
framework:
    cache:
        ...
        pools:
            ...
            synolia_maintenance.cache:
                adapter: cache.adapter.redis
```

### Precisions for access token

Once token is generated, disallowing maintenance will be available thought request as well.
So you can use it as query parameter `?synolia_maintenance_token={$token}` or in headers `HTTP_SYNOLIA_MAINTENANCE_TOKEN: token` for a particular request to bypass maintenance mode.

## Development

See [How to contribute](CONTRIBUTING.md).

## License

This library is under the [EUPL-1.2 license](LICENSE).

## Credits

Developed by [Synolia](https://synolia.com/).
