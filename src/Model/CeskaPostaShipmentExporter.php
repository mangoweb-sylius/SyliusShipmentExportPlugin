<?php

declare(strict_types=1);

namespace MangoSylius\ShipmentExportPlugin\Model;

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShipmentInterface;

class CeskaPostaShipmentExporter implements ShipmentExporterInterface
{
	public function getShippingMethodsCodes(): array
	{
		return ['ceska-posta-do-ruky', 'ceska-posta-na-postu'];
	}

	public function getDobirkaCode(): ?string
	{
		return 'dobirka';
	}

	public function getRow(ShipmentInterface $shipment, array $questionsArray): array
	{
		/**
		 * Formát: UTF-8
		 *
		 * 1  - Příjmení/Název
		 * 2  - Jméno
		 * 3  - Ulice
		 * 4  - č.popisné - Přeádáváme jako součást ulice
		 * 5  - č.orientační - Přeádáváme jako součást ulice
		 * 6  - Obec
		 * 7  - PSČ
		 * 8  - Stát
		 *
		 * 9  - Typ subjektu - Zatím vždy F
		 * 10 - IČ - Zatím prázdné
		 * 11 - DIČ - Zatím prázdné
		 *
		 * 12 - Email
		 * 13 - Telefon/Mobil
		 *
		 * 14 - Hmotnost
		 * 15 - Udaná cena
		 *
		 * 16 - Hodnota dobírky
		 * 17 - VS symbol (VS poukázka)
		 *
		 * 18 - Typ Zásilky - např.: DR (Do ruky), NP (Na poštu)
		 * 19 - Měna (ISO)
		 *
		 * 20 - Předáno ID Objednávky (není nutno používat)
		 */
		$order = $shipment->getOrder();
		assert($order !== null);
		$address = $order->getShippingAddress();
		assert($address !== null);
		$customer = $order->getCustomer();
		assert($customer !== null);

		$shippingMethod = $shipment->getMethod();
		assert($shippingMethod !== null);
		$shippingMethodCode = $shippingMethod->getCode();
		$payment = $order->getPayments()->first();
		assert($payment instanceof PaymentInterface);
		$paymentMethod = $payment->getMethod();
		assert($paymentMethod !== null);
		$isCashOnDelivery = $paymentMethod->getCode() === $this->getDobirkaCode();

		$totalAmount = number_format(
			$order->getTotal() / 100,
			2,
			',',
			''
		);

		$zip = (string) preg_replace('/\s/', '', $address->getPostcode());
		$zipFormated = substr($zip, 0, 3) . ' ' . substr($zip, 3, 2);

		$method = $shippingMethodCode === 'ceska-posta-do-ruky'
			? 'DR'
			: 'NP';

		$weight = 0;
		foreach ($order->getItems() as $item) {
			/** @var OrderItemInterface $item */
			$variant = $item->getVariant();
			if ($variant !== null) {
				$weight += $variant->getWeight();
			}
		}

		return [
			/** 1  - Příjmení/Název */
			$address->getCompany() ?? $address->getLastName(),

			/** 2  - Jméno, může být součástí název */
			$address->getCompany() === null
				? $address->getFirstName()
				: '',

			/** 3  - Ulice */
			$shippingMethodCode === 'ceska-posta-na-postu'
				? ''
				: $address->getStreet(),

			/** 4  - č.popisné - Mohou se uvádět jako součást ulice */
			'',

			/** 5  - č.orientační */
			'',

			/** 6  - Obec */
			$shippingMethodCode === 'ceska-posta-na-postu'
				? ''
				: $address->getCity(),

			/** 7  - PSČ */
			$zipFormated,

			/** 8  - Stát */
			$address->getCountryCode(),

			/** 9  - Typ subjektu
			 *
			 * v souboru musí být uveden údaj P (pro právnickou osobu) nebo F (pro fyzickou osobu)
			 * a O (označení adresáta pro import s příznakem Obchodní psaní).
			 * Nebo zde také může být uvedena kombinace těchto označení adresáta, ve formátu např. O+P či F+O.
			 */
			'F',

			/** 10 - IČ */
			'',

			/** 11 - DIČ */
			'',

			/** 12 - Email */
			$customer->getEmail(),

			/** 13 - Telefon/Mobil */
			$address->getPhoneNumber(),

			/** 14 - Hmotnost */
			$weight,

			/**
			 * 15 - Udaná cena
			 *
			 * Musí být v CZK, nezáleží na Kódu měny.
			 */
			$totalAmount,

			/** 16 - Hodnota dobírky */
			$isCashOnDelivery ? $totalAmount : '',

			/** 17 - VS symbol (VS poukázka) */
			$order->getNumber(),

			/** 18 - Typ Zásilky - např.: DR (Do ruky), NP (Na poštu) */
			$method ?? '',

			/** 19 - Měna (ISO) */
			'CZK',

			/** 20 - ID objednávky */
			$order->getNumber(),

			/**
			 * 21 - Služby
			 *
			 * 7 = Balík
			 * 41 = Bezdokladová dobírka
			 */
			$isCashOnDelivery ? '7+41' : '7',
		];
	}

	public function getDelimiter(): string
	{
		return ';';
	}

	public function getQuestionsArray(): ?array
	{
		return null;
	}
}
