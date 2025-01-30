<?php
namespace App\Controllers;

use App\Models\User;

class StaticPagesController extends Controller {

	private function generateStaticPage($resp, $tit, $desc, $dir, $bn, $hn = null, $an = null) {

		$vars = [
			'page' => [
				'title' => $tit,
				'description' => $desc,
				'basename' => $bn,
				'helpname' => $hn,
				'analname' => $an
			],
		];  

		if(!isset($hn)) {
			$this->view->render($resp, $dir.$bn.'.html', $vars);
		} else {
			if(!isset($an)) $this->view->render($resp, $dir.$hn.'.html', $vars);
			else $this->view->render($resp, $dir.$an.'.html', $vars);
		}

	}

	// HOMEPAGE

	public function home($request, $response, $args) {

		$this->generateStaticPage($response, 'Homepage - '.$this->global['longProjectName'], 'Welcome to '.$this->global['longProjectName'], '', 'home');
	
	}

	// COOKIES

	public function cookies($request, $response, $args) {

		$this->generateStaticPage($response, 'Cookies Policy - '.$this->global['longProjectName'], 'Cookies Policy for '.$this->global['longProjectName'], '', 'cookies');
	
	}

	// HELP PAGES

	public function help($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Method', 'In this page you will find out all the help to use the tool', '', 'help');
	
	}

	public function method($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Method', 'In this page you will find out all the help to use the tool', 'templates/help/', 'help', 'method');
	
	}

	public function inputs($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Inputs', 'Help page for inputs', 'templates/help/', 'help', 'inputs');
	
	}

	public function outputs($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Outputs', 'Help page for outputs', 'templates/help/', 'help', 'outputs');
	
	}

	public function curves($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Curves', 'Help page for Analysis Curves', 'templates/help/', 'help', 'analysis', 'curves');
	
	}

	public function stiffness($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Stiffness', 'Help page for Analysis Stiffness', 'templates/help/', 'help', 'analysis', 'stiffness');
	
	}

	public function pcazip($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis PCAZip', 'Help page for Analysis PCAZip', 'templates/help/', 'help', 'analysis', 'pcazip');
	
	}

	public function contacts($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Contacts', 'Help page for Analysis Contacts', 'templates/help/', 'help', 'analysis', 'contacts');
	
	}

	public function bending($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Bending', 'Help page for Analysis Bending', 'templates/help/', 'help', 'analysis', 'bending');
	
	}

	public function circular($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Circular', 'Help page for Analysis Circular', 'templates/help/', 'help', 'analysis', 'circular');
	
	}

	public function energy($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Elastic Energy', 'Help page for Analysis Elastic Energy', 'templates/help/', 'help', 'analysis', 'energy');
	
	}

	public function endtoend($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis End-to-end', 'Help page for Analysis End-to-end', 'templates/help/', 'help', 'analysis', 'endtoend');
	
	}

	public function sasa($request, $response, $args) {

		$this->generateStaticPage($response, 'Help - '.$this->global['longProjectName'].' Analysis Sasa', 'Help page for Analysis Sasa', 'templates/help/', 'help', 'analysis', 'sasa');
	
	}	
	
}
