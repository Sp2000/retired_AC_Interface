<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class BHLController
 * Handles retrieval/display of data from BHL Europe
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class BhlEController extends AController {

	public function indexAction() {
		$genus = $this->_request->getParam('genus');
		$species = $this->_request->getParam('species');
		$searchTerm = "{$genus}%20{$species}";
		$this->view->searchTerm = $searchTerm;
		$this->view->bhl = $this->_getBHLResponse($searchTerm);
		$this->view->portalUrlPattern = Bootstrap::instance()->getOption('bhl.portal_urlpattern');
		$this->view->imageUrlPattern = Bootstrap::instance()->getOption('bhl.image_urlpattern');
	}
	
	public function synopsisAction() 
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();		
		$this->_response->setHeader('Content-Type', 'text/plain', true);
		$genus = $this->_request->getParam('genus');
		$species = $this->_request->getParam('species');	
		$searchTerm = "{$genus}%20{$species}";
		$response = $this->_getBHLResponse($searchTerm);
		$this->_response->setBody($response->numFound);
		$this->_response->sendResponse();
		exit;
	}
	
	/**
	 *
	 * @param string $searchTerm The search term to query BHL with
	 */
	private function _getBHLResponse($searchTerm) {
		
		$session = $this->getHelper('SessionHandler');
		$previousSerchTerm = $session->get('BHL_SEARCH_TERM');
		if($previousSerchTerm == $searchTerm) {
			//return unserialize($session->get('BHL_RESPONSE'));
		}
		
		$session->set('BHL_SEARCH_TERM', $searchTerm);
		
		$urlPattern = Bootstrap::instance()->getOption('bhl.solr_urlpattern');
		$url = sprintf($urlPattern, $searchTerm);
			
		$data = file_get_contents($url);
		$response = simplexml_load_string($data);
		
		$numFound = (int) $response->result['numFound'];
		
		$references = array();
		foreach($response->result->doc as $doc) {
			$reference = new stdClass();
			$reference->pid = self::_xpathGet($doc->xpath("str[@name='PID']"));
			$reference->title = self::_xpathGet($doc->xpath("arr[@name='mods_title']/str"));
			$reference->publisher = self::_xpathGet($doc->xpath("arr[@name='mods_publisher']/str"));
			$reference->year = self::_xpathGet($doc->xpath("arr[@name='mods_date_issued']/str"));
			$authorElements = $doc->xpath("arr[@name='mods_name']/str");
			echo '<pre>';
			print_r($authorElements);
			echo '</pre>';
			$author = array_reduce($authorElements, array('BHLEController', '_reduce'), null);
			echo '<pre>';
			print_r($author);
			echo '</pre>';
			die();
			$reference->author = $author;
			$references[] = $reference;
		}
		
		$obj = new stdClass();
		$obj->numFound = $numFound;
		$obj->references = $references;
		
		$session->set('BHL_SEARCH_TERM', serialize($obj));
		
		return $obj;
		
	}
	
	private static function _xpathGet($xpath) {
		return (string) $xpath[0];
	}
	
	private static function _reduce($thusfar, SimpleXMLElement $sxe) {
		$chunk = (string) $sxe;
		
		// hack
		if(trim($chunk) === '0') {
			$thusfar = '';
		} 
		
		if($thusfar === null) {
			$thusfar = $chunk;
		}
		else {
			$thusfar .= '; ' . $chunk;
		}
		
		return $thusfar;
	}

}
