<?php
namespace Bundle;

use Coretex\Router\Route;
use App\Controller\UserController;

$route = new Route();

$route->get("/", "main.temp.php");

$route->get("/id/{id}/{name}", [
	UserController::class , 'index'
]);

// $route->get("/welcome", "smain.temp.php");

$route->end();
