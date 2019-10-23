<?php

class Service
{
	/**
	 * Get active amulets and the inventary
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _main(Request $request, Response $response)
	{
		// get the list of amulets
		$amulets = Connection::query("
			SELECT A.amulet_id, A.expires, A.active, B.name, B.description, B.icon
			FROM _amulets_person A
			LEFT JOIN _amulets B
			ON A.amulet_id = B.id
			WHERE A.person_id = {$request->person->id}
			AND (A.expires > CURRENT_TIMESTAMP OR A.expires IS NULL)
			AND B.active = 1
			ORDER BY A.expires DESC", true, 'utf8');

		// separate active from inventary
		$active = $inventary = [];
		foreach($amulets as $amulet) {
			// get the counter right
			if($amulet->expires) {
				$today = new DateTime();
				$future = new DateTime($amulet->expires);
				$diff = date_diff($today, $future);
				$amulet->expires = $diff->d * 24 + $diff->h .':'. $diff->i .':'. $diff->s;
			}

			// clasify the amulet
			if($amulet->active) $active[] = $amulet;
			else $inventary[] = $amulet;
		}

		// get content for the view
		$content = [
			"credits" => $request->person->credit,
			"active" => $active,
			"inventary" => $inventary];

		// send data to the view
		$response->setTemplate("home.ejs", $content);
	}

	/**
	 * Equip an amulet from the inventory
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _equip(Request $request, Response $response)
	{
		// check if you have an empty slot
		$equippedAmuletsCount = Connection::query("
			SELECT COUNT(id) AS cnt
			FROM _amulets_person
			WHERE person_id = {$request->person->id}
			AND active = 1")[0]->cnt;

		// return back the list if there are not empty slots
		if($equippedAmuletsCount >= 3) return $this->_main($request, $response);

		// equip the amulet
		Connection::query("
			UPDATE _amulets_person 
			SET active = 1
			WHERE amulet_id = {$request->input->data->id} 
			AND person_id = {$request->person->id}");

		// get back to the list of amulets
		return $this->_main($request, $response);
	}

	/**
	 * Unequip an amulet and send it back to the inventory
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _unequip(Request $request, Response $response)
	{
		// unequip the amulet
		Connection::query("
			UPDATE _amulets_person 
			SET active = 0
			WHERE amulet_id = {$request->input->data->id} 
			AND person_id = {$request->person->id}");

		// get back to the list of amulets
		return $this->_main($request, $response);
	}

	/**
	 * Display the amulets for sale
	 *
	 * @author salvipascual
	 * @param Request  $request
	 * @param Response $response
	 */
	public function _store(Request $request, Response $response)
	{
		// get the list of amulets
		$amulets = Connection::query("
			SELECT *
			FROM _amulets
			WHERE id NOT IN (
				SELECT amulet_id 
				FROM _amulets_person 
				WHERE person_id={$request->person->id}
			)", true, 'utf8');

		// get content for the view
		$content = [
			"credits" => $request->person->credit,
			"amulets" => $amulets];

		// send data to the view
		$response->setTemplate("store.ejs", $content);
	}

	/**
	 * Pay for an item and add the items to the database
	 *
	 * @param Request
	 * @param Response
	 * @throws Exception
	 */
	public function _pay(Request $request, Response $response)
	{
		// get the amulet to purchase
		$code = $request->input->data->code;
		$amulet = Connection::query("SELECT id, duration FROM _amulets WHERE code='$code'")[0];

		// process the payment
		try {
			MoneyNew::buy($request->person->id, $code);
		} catch (Exception $e) {
			return $response->setTemplate('message.ejs', [
				"header"=>"Error inesperado",
				"icon"=>"sentiment_very_dissatisfied",
				"text" => "Hemos encontrado un error procesando su canje. Por favor intente nuevamente, si el problema persiste, escríbanos al soporte.",
				"button" => ["href"=>"AMULETOS STORE", "caption"=>"Reintentar"]]);
		}

		// calculate expiration date
		if($amulet->duration <= 0) $expires = "NULL";
		else $expires = "'".date("Y-m-d H:m:s", strtotime(date()."+ {$amulet->duration} hours"))."'";

		// add the amulet to the table
		Connection::query("
			INSERT INTO _amulets_person(person_id, amulet_id, expires)
			VALUES ({$request->person->id}, {$amulet->id}, $expires)");

		// possitive response
		return $response->setTemplate('message.ejs', [  
			"header"=>"Canje realizado",
			"icon"=>"sentiment_very_satisfied",
			"text" => "Su canje se ha realizado satisfactoriamente. Active el amuleto para aprovechar sus poderes. Recuerde que algunos amuletos pierden su fuerza incluso estando inactivos.",
			"button" => ["href"=>"AMULETOS", "caption"=>"Mis amuletos"]]);
	}
}