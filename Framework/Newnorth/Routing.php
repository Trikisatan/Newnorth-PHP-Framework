<?
namespace Framework\Newnorth;

class Routing {
	/* Instance variables */

	public $FilePath;

	public $Data;

	public $Routes;

	/* Magic methods */

	public function __construct($FilePath = 'Routes.ini') {
		$this->FilePath = $FilePath;
	}

	/* Instance methods */

	public function Initialize() {
		$this->Data = ParseIniFile($this->FilePath);

		foreach($this->Data as $RouteName => $RouteParameters) {
			if(!isset($RouteParameters['Pattern'])) {
				throw new ConfigException(
					'Pattern not set for route.',
					[
						'File' => $FilePath,
						'Route name' => $RouteName,
						'Route parameters' => $RouteData,
					]
				);
			}
			else {
				$this->Routes[$RouteName] = new Route(
					$RouteName,
					$RouteParameters['Pattern'],
					isset($RouteParameters['Requirements']) ? $RouteParameters['Requirements'] : [],
					isset($RouteParameters['Translations']) ? $RouteParameters['Translations'] : [],
					isset($RouteParameters['Defaults']) ? $RouteParameters['Defaults'] : []
				);
			}
		}
	}

	public function GetUrl(array $Parameters) {
		// Typecast all parameters to strings to avoid comparison problems.
		foreach($Parameters as $Key => $Value) {
			$Parameters[$Key] = (string)$Value;
		}

		if(!isset($Parameters['Page'])) {
			if(isset($GLOBALS['Parameters']['Page'])) {
				$Parameters['Page'] = $GLOBALS['Parameters']['Page'];
			}
		}

		if(isset($Parameters['Locale'])) {
			$Locale = $Parameters['Locale'];
		}
		else if(isset($GLOBALS['Parameters']['Locale'])) {
			$Locale = $GLOBALS['Parameters']['Locale'];
		}
		else {
			$Locale = '';
		}

		foreach($this->Routes as $Route) {
			$RouteParameters = array_slice($Parameters, 0);

			$Route->SetDefaults($RouteParameters);

			foreach($GLOBALS['Parameters'] as $Key => $Value) {
				if(is_int($Key)) {
					continue;
				}

				if(isset($RouteParameters[$Key])) {
					continue;
				}

				$RouteParameters[$Key.'?'] = $Value;
			}

			if($Route->ReversedMatch($RouteParameters, $Locale, $Url)) {
				return $Url;
			}
		}

		throw new RuntimeException(
			'Unable to generate URL.',
			[
				'Parameters' => $Parameters,
			]
		);
	}
}
?>