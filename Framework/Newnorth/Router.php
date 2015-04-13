<?
namespace Framework\Newnorth;

class Router {
	/* Static methods */

	static private function Reroute($Parameters) {
		if(count(array_diff($GLOBALS['Parameters'], $Parameters)) === 0) {
			throw new RuntimeException(
				'Rerouting loop encountered.',
				[
					'Route parameters' => $Parameters,
				]
			);
		}
		else {
			throw new RerouteException($Parameters);
		}
	}

	static public function ErrorPage() {
		header('HTTP/1.0 500 Internal Server Error');

		Router::Reroute($GLOBALS['Config']->ErrorHandling['Pages']['Error']);
	}

	static public function NotFoundPage() {
		header('HTTP/1.0 404 Not Found');

		Router::Reroute($GLOBALS['Config']->ErrorHandling['Pages']['NotFound']);
	}

	static public function Redirect($Location) {
		if(is_array($Location)) {
			header('Location: '.$GLOBALS['Routing']->GetUrl($Location));
		}
		else {
			header('Location: '.$Location);
		}

		exit();
	}
}
?>