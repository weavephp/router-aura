<?php
declare(strict_types = 1);
/**
 * Weave Router Adaptor for Aura.Router.
 */
namespace Weave\Router\Aura;

use Psr\Http\Message\ServerRequestInterface as Request;
use Aura\Router\RouterContainer;

/**
 * Weave Router Adaptor for Aura.Router.
 */
class Aura implements \Weave\Router\RouterAdaptorInterface
{
	/**
	 * The Aura.Router routerContainer instance.
	 *
	 * @var RouterContainer
	 */
	protected $routerContainer;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->routerContainer = new RouterContainer();
	}

	/**
	 * Using the provided callable, configure Aura.Router's routes.
	 *
	 * The routeProvider is called with Aura.Router's Map instance as the parameter.
	 *
	 * @param callable $routeProvider The method to use to configure the routes.
	 *
	 * @return null
	 */
	public function configureRoutes(callable $routeProvider)
	{
		$routeProvider($this->routerContainer->getMap());
	}

	/**
	 * Route the supplied request.
	 *
	 * @param Request &$request The PSR7 request to attempt to route.
	 *
	 * @return false|string|callable
	 */
	public function route(Request &$request)
	{
		$matcher = $this->routerContainer->getMatcher();
		$route = $matcher->match($request);
		if (!$route) {
			return false;
		}

		foreach ($route->attributes as $key => $val) {
			$request = $request->withAttribute($key, $val);
		}
		$request = $request->withAttribute('route.name', $route->name);

		return $route->handler;
	}
}
