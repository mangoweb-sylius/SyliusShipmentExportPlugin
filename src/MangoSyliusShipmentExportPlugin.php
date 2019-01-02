<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MangoSyliusShipmentExportPlugin extends Bundle
{
	use SyliusPluginTrait;

	public function build(ContainerBuilder $container): void
	{
		$container->addCompilerPass(new DependencyInjection\Compiler\RegisterShipmentExporersPass());
	}
}
