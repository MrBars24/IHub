<?php
namespace App\Traits;

trait TimestampableTrait
{
	/// Section: Methods

	/**
	 * Dynamically format a timestamp.
	 *
	 * @param $key
	 * @return mixed
	 */
	public function formatTimestampable($key)
	{
		$dates = $this->getTimestampAttributes();

		$formats = array(
			'full' => function($value) {
				return $value->format('Y-m-d H:i:s');
			},
			'date' => function($value) {
				return $value->format('Y-m-d');
			},
			'time' => function($value) {
				return $value->format('H:i:s');
			},
			'friendly' => function($value) {
				return $value->format('d F Y');
			},
			'relative' => function($value) {
				return relativeDate($value);
			},
		);

		// match combo and run date macro
		foreach($dates as $i => $dateProp) {
			foreach($formats as $filter => $macro) {
				// match key
				if($key == $dateProp . '_' . $filter && !is_null($this->$dateProp)) {
					return $macro($this->$dateProp);
				}
			}
		}
		return null;
	}
}