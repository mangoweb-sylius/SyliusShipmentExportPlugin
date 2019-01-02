<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterShipmentExporersPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container): void
	{
		if ($container->has('mango_sylius.shipment_exporter') === false) {
			return;
		}

		$registry = $container->getDefinition('mango_sylius.shipment_exporter');

		$exporterRegistry = $container->findTaggedServiceIds('mango_sylius.shipment_exporter_type');
		$exporters = [];

		foreach ($exporterRegistry as $id => $attributes) {
			if (!isset($attributes[0]['type']) || !isset($attributes[0]['label'])) {
				throw new \InvalidArgumentException('Tagged shipping exporter configuration type needs to have `type` and `label` attributes.');
			}

			$type = $attributes[0]['type'];
			$exporters[$type] = $attributes[0]['label'];
			$registry->addMethodCall('register', [$type, new Reference($id)]);
		}

		$container->setParameter('mango_sylius.Shipment_exporters', $exporters);
	}
}
