<?php
namespace App\Traits;

// Laravel
use Illuminate\Http\Request;

trait ListAction
{
	/**
	 * Helper : action
	 * helper use to perform action on selected IDs on table
	 *
	 * @return \Illuminate\Http\Request
	 */
	public function action(Request $request)
	{
		// arguments
		$arguments = func_get_args();
		$request = $arguments[0]; // assume the request object is the first parameter
		
		if(!($request instanceof Request)) {
			throw new \BadMethodCallException('The request object must the the first argument passed into this method');
		}

		// get selected items
		$items = $request->input('item');
		$ids = !is_null($items) ? array_keys($items) : [];

		// cycle through valid action and see which one was triggered
		$response = null;
		foreach($this->valid_actions as $action) {
			if(!is_null($request->input($action))) { // note: can't use ->has(..) here since it returns false on empty string
				$action = $this->snakeToCamelCase($action);
				$method = 'action' . $action;

				// perform a dynamic method call with variable number of arguments
				$arguments[] = $ids;
				$response = call_user_func_array([$this, $method], $arguments);
			}
		}

		// response
		return $response;
	}

	/**
	 * snakeToCamelCase
	 * Transform snake case to camel case
	 *
	 * @param string $string
	 * @param boolean $capitalizeFirstCharacter
	 * @return string
	 */
	public function snakeToCamelCase($string, $capitalizeFirstCharacter = true) 
	{
		$str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
		if(!$capitalizeFirstCharacter) {
			$str = lcfirst($str);
		}
		return $str;
	}
}