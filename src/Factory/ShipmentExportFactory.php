<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Factory;

use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ShipmentExportFactory
{
	/** @var RequestStack */
	private $requestStack;

	/** @var ServiceRegistryInterface */
	private $serviceRegistry;

	public function __construct(
		RequestStack $requestStack,
		ServiceRegistryInterface $serviceRegistry
	) {
		$this->requestStack = $requestStack;
		$this->serviceRegistry = $serviceRegistry;
	}

	public function getExporter(): ?Object
	{
		$request = $this->requestStack->getCurrentRequest();
		if ($request === null) {
			return null;
		}

		$exporterName = $request->get('exporterName');

		if ($this->serviceRegistry->has($exporterName) === false) {
			throw new  NotFoundException(sprintf(
				'Exporter with %s exporterName could not be found',
				$exporterName
			));
		}

		return $this->serviceRegistry->get($exporterName);
	}
}
