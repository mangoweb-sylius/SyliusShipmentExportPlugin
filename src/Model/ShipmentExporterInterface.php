<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Model;

use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentExporterInterface
{
	public function getShippingMethodsCodes(): array;

	public function getRow(ShipmentInterface $shipment, array $questionsArray): array;

	public function getDelimiter(): string;

	public function getQuestionsArray(): ?array;
}
