<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Model;

interface QuestionInterface
{
	public function getCode(): string;

	public function setCode(string $code): void;

	public function getLabel(): string;

	public function setLabel(string $label): void;

	public function getRegex(): ?string;

	public function setRegex(?string $reqex): void;

	public function getDefaultValue(): ?string;

	public function setDefaultValue(?string $defaultValue): void;
}
