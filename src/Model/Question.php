<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Model;

class Question implements QuestionInterface
{
	/** @var string */
	private $code;

	/** @var string */
	private $label;

	/** @var string|null */
	private $defaultValue;

	/** @var string|null */
	private $regex;

	public function __construct(string $code, string $label, ?string $defaultValue, ?string $regex)
	{
		$this->code = $code;
		$this->label = $label;
		$this->defaultValue = $defaultValue;
		$this->regex = $regex;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function setCode(string $code): void
	{
		$this->code = $code;
	}

	public function getLabel(): string
	{
		return $this->label;
	}

	public function setLabel(string $label): void
	{
		$this->label = $label;
	}

	public function getRegex(): ?string
	{
		return $this->regex;
	}

	public function setRegex(?string $regex): void
	{
		$this->regex = $regex;
	}

	public function getDefaultValue(): ?string
	{
		return $this->defaultValue;
	}

	public function setDefaultValue(?string $defaultValue): void
	{
		$this->defaultValue = $defaultValue;
	}
}
