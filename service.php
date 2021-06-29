<?php

use Apretaste\Money;
use Apretaste\Notifications;
use Framework\Database;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;

class Service
{
	/**
	 * Get active amulets and the inventary
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _main(Request $request, Response &$response)
	{
		// get the list of amulets
		$amulets = Database::query("
			SELECT A.amulet_id, A.expires, A.active, B.name, B.description, B.icon
			FROM _amulets_person A
			LEFT JOIN _amulets B
			ON A.amulet_id = B.id
			WHERE A.person_id = {$request->person->id}
			AND (A.expires > CURRENT_TIMESTAMP OR A.expires IS NULL)
			AND B.active = 1 AND core <= 3
			ORDER BY A.expires DESC");

		// separate active from inventary
		$active = $inventary = [];
		foreach ($amulets as $amulet) {
			// get the counter in the format HH:MM:SS
			$amulet->countdown = '';
			if ($amulet->expires) {
				$today = new DateTime();
				$future = new DateTime($amulet->expires);
				$diff = date_diff($today, $future);
				$amulet->countdown = ($diff->d * 24 + $diff->h) . ':' . $diff->i . ':' . $diff->s;
			}

			// clasify the amulet
			if ($amulet->active) {
				$active[] = $amulet;
			} else {
				$inventary[] = $amulet;
			}
		}

		// get content for the view
		$content = [
			'credits' => $request->person->credit,
			'active' => $active,
			'inventary' => $inventary
		];

		// send data to the view
		$response->setTemplate('home.ejs', $content);
	}

	/**
	 * Equip an amulet from the inventory
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _equip(Request $request, Response &$response)
	{
		// check if you have an empty slot
		$equippedAmuletsCount = Database::query("
			SELECT COUNT(id) AS cnt
			FROM _amulets_person
			WHERE person_id = {$request->person->id}
			AND (expires > CURRENT_TIMESTAMP OR expires IS NULL)
			AND active = 1")[0]->cnt;

		// return back the list if there are not empty slots
		if ($equippedAmuletsCount >= 3) {
			$this->_main($request, $response);
			return;
		}

		// equip the amulet
		Database::query("
			UPDATE _amulets_person
			SET active = 1
			WHERE amulet_id = {$request->input->data->id}
			AND person_id = {$request->person->id}");

		$amulet = Database::query("SELECT `name` FROM _amulets WHERE id={$request->input->data->id}")[0]->name;

		// user log
		Notifications::log($request->person->id, "Equipaste el amuleto $amulet");

		// get back to the list of amulets
		$this->_main($request, $response);
	}

	/**
	 * Unequip an amulet and send it back to the inventory
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _unequip(Request $request, Response &$response)
	{
		// unequip the amulet
		Database::query("
			UPDATE _amulets_person
			SET active = 0
			WHERE amulet_id = {$request->input->data->id}
			AND person_id = {$request->person->id}");

		// get back to the list of amulets
		$this->_main($request, $response);
	}

	/**
	 * Display the amulets for sale
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _store(Request $request, Response &$response)
	{
		// get the list of amulets
		$amulets = Database::query("
			SELECT *
			FROM _amulets
			WHERE active = 1 AND core <= 3
			AND id NOT IN (
				SELECT amulet_id
				FROM _amulets_person
				WHERE person_id={$request->person->id}
				AND (expires > CURRENT_TIMESTAMP OR expires IS NULL)
			)");

		Challenges::complete('visit-druida', $request->person->id);

		// send data to the view
		$response->setTemplate('store.ejs', ['amulets' => $amulets]);
	}

	/**
	 * Pay for an item and add the items to the database
	 *
	 * @param Request $request
	 * @param Response $response
	 * @author salvipascual
	 */
	public function _pay(Request $request, Response $response)
	{
		// get the amulet to purchase
		$code = $request->input->data->code;
		$amulet = Database::query("SELECT id, duration FROM _amulets WHERE code='$code'")[0];

		// check if the user already have thay amulet
		$isAmuletInInventory = Database::query("
			SELECT COUNT(id) AS cnt
			FROM _amulets_person
			WHERE person_id = {$request->person->id}
			AND amulet_id = {$amulet->id}
			AND (expires > CURRENT_TIMESTAMP OR expires IS NULL)")[0]->cnt;

		// do not purchase if you already have the amulet
		if ($isAmuletInInventory > 0) {
			return $response->setTemplate('message.ejs', [
				'header' => 'Ya tienes el amuleto',
				'icon' => 'sentiment_neutral',
				'text' => 'El Druida te mira con cara seria y te dice: Ya tienes ese amuleto, ¿Por que quieres gastar tus créditos?. Escoge otro o vuelve cuando pierda su efectividad.',
				'button' => ['href' => 'AMULETOS STORE', 'caption' => 'Escoger otro']
			]);
		}

		// process the payment
		try {
			Money::purchase($request->person->id, $code);
		} catch (Exception $e) {
			return $response->setTemplate('message.ejs', [
				'header' => 'Error inesperado',
				'icon' => 'sentiment_very_dissatisfied',
				'text' => 'Hemos encontrado un error procesando su canje. Por favor intente nuevamente, si el problema persiste, escríbanos al soporte.',
				'button' => ['href' => 'AMULETOS STORE', 'caption' => 'Reintentar']]);
		}

		// calculate expiration date
		if ($amulet->duration <= 0) {
			$expires = 'NULL';
		} else {
			$expires = "'" . date('Y-m-d H:i:s', strtotime("+{$amulet->duration} hours")) . "'";
		}

		// add the amulet to the table
		Database::query("
			INSERT INTO _amulets_person(person_id, amulet_id, expires)
			VALUES ({$request->person->id}, {$amulet->id}, $expires)");

		// possitive response
		return $response->setTemplate('message.ejs', [
			'header' => 'Canje realizado',
			'icon' => 'sentiment_very_satisfied',
			'text' => 'Su canje se ha realizado satisfactoriamente. Active el amuleto para aprovechar sus poderes. Recuerde que algunos amuletos pierden su fuerza incluso estando inactivos.',
			'button' => ['href' => 'AMULETOS', 'caption' => 'Mis amuletos']
		]);
	}
}
