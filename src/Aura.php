<?php
/**
 * Weave Router Adaptor for Aura.Router.
 */
namespace Weave\Router\Aura;

use Psr\Http\Message\ServerRequestInterface as Request;
use Aura\Router\RouterContainer;
use Psr\Log\LoggerInterface;

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
	 *
	 * @param LoggerInterface $logger An optional PSR3 logger instance.
	 */
	public function __construct(LoggerInterface $logger = null)
	{
		$this->routerContainer = new RouterContainer();

		// Aura Router wants a factory for the logger but then
		// only uses it once to create a shared logger so here
		// we just provide a callable that fakes being a factory
		// but which simply returns the existing provided logger.
		if ($logger !== null) {
			$this->routerContainer->setLoggerFactory(
				function () use ($logger) {
					return $logger;
				}
			);
		}
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

		foreach ($route->extras as $key => $val) {
			$request = $request->withAttribute($key, $val);
		}
		foreach ($route->attributes as $key => $val) {
			$request = $request->withAttribute($key, $val);
		}
		$request = $request->withAttribute('route.name', $route->name);

		return $route->handler;
	}
}
