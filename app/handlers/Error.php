<?php
 
namespace App\Handlers;
 
use Monolog\Logger;

final class Error extends \Slim\Handlers\Error
{
    protected $logger;

    protected $container;

    protected $code;

    protected $message;
 
    public function __construct(Logger $logger, $container) {
        $this->logger = $logger;
        $this->container = $container;
    }
 
    public function __invoke($request, $response, \Exception $exception) {

    	// Log the message
    	$this->logger->error("WEB - ".$exception->getMessage(), ["code" => $exception->getCode()]);

		// Return Exception data according to type
    	$data = [
	      'code'    => $exception->getCode(),
	      'message' => $exception->getMessage(),
		];

        $vars = [
            'page' => [
            'title' => $this->container->global['longProjectName'].' - Database not found!',
            'description' => 'Cannot connect to the database',
            'basename' => 'errordb'
            ],
        ]; 

		return $this->container->view->render($response->withStatus($data['code']), '503.html',$vars);
    
	}
}
