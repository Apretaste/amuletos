<?php

use Framework\Database;
use Apretaste\Request;
use Apretaste\Response;


class Service {
	/**
	 * Get active amulets and the inventary
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _main(Request $request, Response &$response) {
		// get the list of amulets
		$amulets = Database::query("
			SELECT A.amulet_id, A.expires, A.active, B.name, B.description, B.icon
			FROM _amulets_person A
			LEFT JOIN _amulets B
			ON A.amulet_id = B.id
			WHERE A.person_id = {$request->person->id}
			AND (A.expires > CURRENT_TIMESTAMP OR A.expires IS NULL)
			AND B.active = 1
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
				$amulet->countdown = $diff->d * 24 + $diff->h.':'.$diff->i.':'.$diff->s;
			}

			// clasify the amulet
			if ($amulet->active) $active[] = $amulet;
			else $inventary[] = $amulet;
		}

		// get content for the view
		$content = [
				'credits'   => $request->person->credit,
				'active'    => $active,
				'inventary' => $inventary];

		// send data to the view
		$response->setTemplate('home.ejs', $content);
	}

	/**
	 * Equip an amulet from the inventory
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws \FeedException
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _equip(Request $request, Response &$response) {
		// check if you have an empty slot
		$equippedAmuletsCount = Database::query("
			SELECT COUNT(id) AS cnt
			FROM _amulets_person
			WHERE person_id = {$request->person->id}
			AND (expires > CURRENT_TIMESTAMP OR expires IS NULL)
			AND active = 1")[0]->cnt;

		// return back the list if there are not empty slots
		if ($equippedAmuletsCount >= 3) return $this->_main($request, $response);

		// equip the amulet
		Database::query("
			UPDATE _amulets_person
			SET active = 1
			WHERE amulet_id = {$request->input->data->id}
			AND person_id = {$request->person->id}");

		// get back to the list of amulets
		$this->_main($request, $response);
	}

	/**
	 * Unequip an amulet and send it back to the inventory
	 *
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws \FeedException
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _unequip(Request $request, Response &$response) {
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
	 * @param Request  $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _store(Request $request, Response &$response) {
		// get the list of amulets
		$amulets = Database::query("
			SELECT *
			FROM _amulets
			WHERE active = 1
			AND id NOT IN (
				SELECT amulet_id
				FROM _amulets_person
				WHERE person_id={$request->person->id}
				AND (expires > CURRENT_TIMESTAMP OR expires IS NULL)
			)");

		// get content for the view
		$content = [
				'credits' => $request->person->credit,
				'amulets' => $amulets];

		// send data to the view
		$response->setTemplate('store.ejs', $content);
	}

}
