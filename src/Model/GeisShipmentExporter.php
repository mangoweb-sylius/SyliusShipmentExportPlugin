<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Model;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Currency\Converter\CurrencyConverter;
use Sylius\Component\Payment\Model\PaymentInterface;

class GeisShipmentExporter implements ShipmentExporterInterface
{
	/** @var CurrencyConverter */
	private $currencyConverter;

	public function __construct(
		CurrencyConverter $currencyConverter
	) {
		$this->currencyConverter = $currencyConverter;
	}

	private function convert(int $amount, string $sourceCurrencyCode, string $targetCurrencyCode): int
	{
		return $this->currencyConverter->convert($amount, $sourceCurrencyCode, $targetCurrencyCode);
	}

	public function getShippingMethodsCodes(): array
	{
		return ['geis-cz', 'geis-sk', 'geis-eu', 'geis-other'];
	}

	public function getRow(ShipmentInterface $shipment, array $questionsArray): array
	{
		$order = $shipment->getOrder();
		assert($order !== null);
		$address = $order->getShippingAddress();
		assert($address !== null);
		$customer = $order->getCustomer();
		assert($customer !== null);

		$days_offset = (int) $questionsArray['days_offset'];

		$payment = $order->getPayments()->first();
		assert($payment instanceof PaymentInterface);
		$paymentMethod = $payment->getMethod();
		assert($paymentMethod instanceof PaymentMethodInterface);
		assert($paymentMethod->getGatewayConfig() !== null);
		$isCashOnDelivery = $paymentMethod->getGatewayConfig()->getFactoryName() === 'offline';

		$currencyCode = $order->getCurrencyCode();
		assert($currencyCode !== null);
		if ($address->getCountryCode() === 'CZ') {
			$totalAmount = $this->convert($order->getTotal(), $currencyCode, 'CZK');
		} elseif ($address->getCountryCode() === 'SK') {
			$totalAmount = $this->convert($order->getTotal(), $currencyCode, 'EUR');
		} elseif ($address->getCountryCode() === 'PL') {
			$totalAmount = $this->convert($order->getTotal(), $currencyCode, 'PLN');
		} elseif ($address->getCountryCode() === 'HU') {
			$totalAmount = $this->convert($order->getTotal(), $currencyCode, 'HUF');
		} elseif ($address->getCountryCode() === 'RO') {
			$totalAmount = $this->convert($order->getTotal(), $currencyCode, 'RON');
		} else {
			$totalAmount = null;
		}

		if ($totalAmount !== null) {
			$totalAmount = number_format(
				$totalAmount / 100,
				0,
				'.',
				''
			);
		}

		$weight = 0;
		foreach ($order->getItems() as $item) {
			/** @var OrderItemInterface $item */
			$variant = $item->getVariant();
			if ($variant !== null) {
				$weight += $variant->getWeight();
			}
		}

		$array = [
			/* 1    Číslo dokladu */
			$order->getNumber(),

			/* 2    Příjemce - Název */
			$address->getCompany() ?? $address->getFullName(),

			/* 3    Příjemce - Stát */
			$address->getCountryCode(),

			/* 4    Příjemce - Město */
			$address->getCity(),

			/* 5    Příjemce - Ulice */
			$address->getStreet(),

			/* 6    Příjemce - PSČ */
			$address->getPostcode(),

			/* 7    Přijemce - kontaktní osoba */
			$address->getFullName(),

			/* 8    Příjemce - kontaktní email */
			$customer->getEmail(),

			/* 9    Přijemce - kontaktní telefon */
			$address->getPhoneNumber(),

			/* 10    Datum svozu */
			date('d.m.Y', time() + ($days_offset * 24 * 60 * 60)),

			/* 11    Reference */
			$order->getNumber(),

			/* 12    EXW */
			'',

			/* 13    Dobírka (COD) */
			$isCashOnDelivery ? '1' : '0',

			/* 14    Hodnota dobírky */
			$isCashOnDelivery
				? $totalAmount ?? 'unsuported'
				: '',

			/* 15    Variabilní symbol */
			$order->getNumber(),

			/* 16    Hmotnost */
			$weight,

			/* 17    Objem */
			'',

			/* 18    Počet */
			1,

			/* 19    Popis zboží */
			'',

			/* 20    Druh obalu (zkratka) */
			'',

			/**
			 * 21    Typ zásilky
			 *
			 * 0 - cargo, 1 - parcel (balík)
			 */
			1,

			/**
			 * 22. Poznámka pro příjemce (volitelné, nemusí být vyplněno, při použití VM viz bod 48)
			 */
			'',

			/**
			 * 23. Poznámka pro řidiče (volitelné, nemusí být vyplněno)
			 */
			'',

			/**
			 * 24. Připojištění (ano - 1, ne - 0)
			 */
			0,

			/**
			 * 25. Hodnota připojištění
			 * (o připojištění zásilky nad 500.000 CZK je třeba požádat na stránkách www.geis-group . cz o nadlimitní pojištění)
			 */
			0,

			/**
			 * 26. Avízo doručené zásilky (ano - 1, ne - 0)
			 */
			0,

			/**
			 * 27. Avízo doručené zásilky (telefonní číslo nebo E-mail)
			 */
			$customer->getEmail(),

			/**
			 * 28. Avízo poškozené zásilky (ano - 1, ne - 0)
			 */
			0,

			/**
			 * 29. Avízo poškozené zásilky (telefonní číslo nebo E-mail)
			 */
			$customer->getEmail(),

			/**
			 * 30. Avízo problémové zásilky (ano - 1, ne - 0)
			 */
			0,

			/**
			 * 31. Avízo problémové zásilky (telefonní číslo nebo E-mail)
			 */
			$customer->getEmail(),

			/**
			 * 32    Služba - B2C (soukromá adresa) (B2C) ( 0 nebo prázdné - ne, 1 - ano)
			 */
			1,

			/**
			 * 33    Služba - Doručení do 12:00 hodin (D12) ( 0 nebo prázdné - ne, 1 - ano)
			 */
			null,

			/**
			 * 34    Služba - Garantované doručení (GDO)( 0 nebo prázdné - ne, 1 - ano)
			 */
			null,

			/**
			 * 35    Služba - POD   (0 nebo prázdné - ne, 1 - ano)
			 */
			null,

			/**
			 * 36    Služba - email pro POD
			 */
			null,

			/**
			 * 37    Služba - SMS avízo   ( 0 nebo prázdné - ne, 1 - ano)
			 */
			null,

			/**
			 * 38    Služba - Telefonické avízo  ( 0 nebo prázdné - ne, 1 - ano)
			 */
			null,

			/**
			 * 39    Příjemce - číslo popisné
			 * číslo ulice je možné zadat do pole Příjemce - Ulice
			 */
			null,

			/**
			 * 40    Příjemce - číslo orientační
			 * číslo ulice je možné zadat do pole Příjemce - Ulice
			 */
			null,

			/**
			 * 41    CrossDock - Jméno (pouze pro CD)
			 */
			null,

			/**
			 * 42    CrossDock - Typ CD svozu (1 - vlastní, 2 - Geis)
			 */
			null,

			/**
			 * 43    CrossDock - Číslo auta - vl.dodání
			 */
			null,

			/**
			 * 44    CrossDock - Datum dodání (pouze pro CD)
			 */
			null,

			/**
			 * 45    Služba - Email příjemci (0 nebo prázdné - ne, 1 - ano)
			 */
			1,
		];

		for ($i = 0; $i < count($array); ++$i) {
			if ($array[$i] !== null) {
				$array[$i] = iconv('UTF-8', 'WINDOWS-1250', (string) $array[$i]);
			}
		}

		return $array;
	}

	public function getDelimiter(): string
	{
		return ';';
	}

	public function getQuestionsArray(): ?array
	{
		$question[] = new Question('days_offset', 'Zadejte počet dnů do svozu (0-9).', '0', '/^[0-9]?$/');

		return $question;
	}

	public function getHeaders(): ?array
	{
		return null;
	}
}
