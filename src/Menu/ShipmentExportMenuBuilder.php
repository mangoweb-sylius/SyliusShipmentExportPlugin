<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ShipmentExportMenuBuilder
{
	/** @var ParameterBagInterface */
	private $parameterBag;

	public function __construct(
		ParameterBagInterface $parameterBag
	) {
		$this->parameterBag = $parameterBag;
	}

	public function buildMenu(MenuBuilderEvent $event): void
	{
		$exporters = $this->parameterBag->get('mango_sylius.Shipment_exporters');

		foreach ($exporters as $key => $val) {
			$sales = $event->getMenu()->getChild('sales');
			assert($sales !== null);

			$sales->addChild('Shipment_exports_' . $key, [
				'route' => 'mango_sylius_admin_Shipment_export',
				'routeParameters' => ['exporterName' => $key],
			])->setName('Export shipment: ' . $val)
				->setLabelAttribute('icon', 'arrow up');
		}
	}
}
