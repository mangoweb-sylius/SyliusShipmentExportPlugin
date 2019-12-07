<p align="center">
    <a href="https://www.mangoweb.cz/en/" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/38423357?s=200&v=4"/>
    </a>
</p>
<h1 align="center">
Shipment Export Plugin
<br />
    <a href="https://packagist.org/packages/mangoweb-sylius/sylius-shipment-export-plugin" title="License" target="_blank">
        <img src="https://img.shields.io/packagist/l/mangoweb-sylius/sylius-shipment-export-plugin.svg" />
    </a>
    <a href="https://packagist.org/packages/mangoweb-sylius/sylius-shipment-export-plugin" title="Version" target="_blank">
        <img src="https://img.shields.io/packagist/v/mangoweb-sylius/sylius-shipment-export-plugin.svg" />
    </a>
    <a href="http://travis-ci.org/mangoweb-sylius/SyliusShipmentExportPlugin" title="Build status" target="_blank">
        <img src="https://img.shields.io/travis/mangoweb-sylius/SyliusShipmentExportPlugin/master.svg" />
    </a>
</h1>

## Features

* See list of all unshipped orders
* Mark more orders at once as shipped
* Download CSV for submitting batch shipments with Geis
* Download CSV for submitting batch shipments with Czech Post
* You can easily extend the module to support custom CSV format for other shipping providers


<p align="center">
	<img src="https://raw.githubusercontent.com/mangoweb-sylius/SyliusShipmentExportPlugin/master/doc/menu.png"/>
</p>


<p align="center">
	<img src="https://raw.githubusercontent.com/mangoweb-sylius/SyliusShipmentExportPlugin/master/doc/list.png"/>
</p>

## Installation

1. Run `$ composer require mangoweb-sylius/sylius-shipment-export-plugin`.
2. Register `\MangoSylius\ShipmentExportPlugin\MangoSyliusShipmentExportPlugin` in your Kernel.
3. Import `@MangoSyliusShipmentExportPlugin/Resources/config/routing.yml` in the routing.yml.

```
mango_sylius_shipment_export_plugin:
    resource: "@MangoSyliusShipmentExportPlugin/Resources/config/routing.yml"
    prefix: /admin
```

### Usage

You can use predefined CSV type for shipment providers Geis and Czech Post) or write your own exporter.

Your custom exporter has to implement `MangoSylius\ShipmentExportPlugin\Model\ShipmentExporterInterface`
and must be defined as service. Check out our sample implementations.


Predefined shipping providers:

* Czech post
```
MangoSylius\ShipmentExportPlugin\Model\CeskaPostaShipmentExporter:
    public: true
    arguments:
        $currencyConverter: '@sylius.currency_converter'    
    tags:
        - name: mango_sylius.shipment_exporter_type
          type: 'ceska_posta'
          label: 'Česká pošta'
```

* Geis
```
MangoSylius\ShipmentExportPlugin\Model\GeisShipmentExporter:
    public: true
    arguments:
        $currencyConverter: '@sylius.currency_converter'
    tags:
        - name: mango_sylius.shipment_exporter_type
          type: 'geis'
          label: 'Geis'
```

## Development

### Usage

- Develop your plugin in `/src`
- See `bin/` for useful commands

### Testing

After your changes you must ensure that the tests are still passing.

```bash
$ composer install
$ bin/phpstan.sh
$ bin/ecs.sh
```

License
-------
This library is under the MIT license.

Credits
-------
Developed by [manGoweb](https://www.mangoweb.eu/).
