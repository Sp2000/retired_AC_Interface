<?php
/**
 * Annual Checklist Interface
 *
 * Class SearchController
 * Defines the search actions
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class SearchController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
        
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_logger->debug($this->_getAllParams());
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    protected function _getSearchForm()
    {
        return new ACI_Form_Search();
    }
    
    public function commonAction()
    {
        $this->view->title = $this->view->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $form = $this->_getSearchForm();
        if($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_renderResultsPage();
        }
        else {
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function scientificAction()
    {
        $fetch = $this->_getParam('fetch', false);
        if($fetch) {
            $this->view->layout()->disableLayout();
            $this->_sendRankData($fetch);
            return;
        }
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        // TODO: implement search query
        $this->_renderFormPage(
            $this->view->title,
            new ACI_Form_Dojo_SearchScientific()
        );
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');
        // TODO: implement search query
        $form = $this->_getSearchForm();
        if($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            //$this->_renderResultsPage();
            $this->_renderFormPage($this->view->title, $form);
        }
        else {
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function allAction()
    {
        $this->view->title = $this->view->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $formHeader =
            sprintf(
                $this->view->translate('Search_fixed_edition'),
                '<span class="red">' .
                $this->view->translate('Annual_Checklist') . '</span>'
            );
        $form = $this->_getSearchForm();
        if($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_renderResultsPage();
        }
        else {
            $this->_renderFormPage($formHeader, $form);
        }
    }
    
    protected function _renderFormPage($formHeader, $form = null)
    {
        $this->view->formHeader = $formHeader;
        $form = $form instanceof Zend_Form ? $form : $this->_getSearchForm();
        if($key = $form->getElement('key')) {
            $key->setValue($this->_getParam('key', ''));
        }
        $this->view->contentClass = 'search-' . $this->view->action;
        $form->setAction(
            $this->view->baseUrl() . '/' . $this->view->controller . '/' .
            $this->view->action
        );
        $this->view->form = $form;
        $this->renderScript('search/form.phtml');
    }
    
    protected function _renderResultsPage()
    {
        $items = (int)$this->_getParam('items',
            ACI_Model_Search::ITEMS_PER_PAGE);
        
        $this->view->urlParams = array(
            'key' => $this->_getParam('key'),
            'match' => $this->_getParam('match'),
            'items' => $items,
            'sort' => $this->_getParam('sort', 'name')
        );
        
        // Get the paginator
        $this->view->paginator = $this->_getPaginator(
            $this->_getSearchQuery($this->_getParam('action')),
            $this->_getParam('page', 1),
            $items
        );
        
        $this->view->paginator->urlParams = $this->view->urlParams;
        
        $this->_logger->debug($this->view->paginator->getCurrentItems());
        $this->view->data = $this->_createTableFromResults();
        
        // Build items per page form
        $form = new ACI_Form_ItemsPerPage();

        $form->getElement('key')->setValue($this->_getParam('key'));
        $form->getElement('match')->setValue($this->_getParam('match'));
        $form->getElement('items')->setValue($items);
        
        $form->setAction(
            $this->view->baseUrl() . '/search/' . $this->_getParam('action')
        );
        
        $this->view->search = $this->_getParam('search');
        $this->view->form = $form;
        
        // Results table differs depending on the action
        $this->view->results = $this->view->render('search/results/' .
            $this->_getParam('action') . '.phtml');
        
        // Render the results layout
        $this->renderScript('search/results/layout.phtml');
    }
    
    /**
     * Builds the result table
     *
     * @return $resultTable
     */
    protected function _createTableFromResults()
    {
        $resultTable = array();
        $i = 0;
        
        foreach($this->view->paginator as $row)
        {
            if($row['rank'] >= ACI_Model_Taxa::RANK_SPECIES)
            {
                $resultTable[$i]['link'] = $this->view->translate('Show_details');
                $resultTable[$i]['url'] =
                    '/details/species/id/' . $row['accepted_species_id'] .
                    '/search/' . $this->view->action .
                    '/key/' . $this->_getParam('key');
                if(!$row['is_accepted_name']) {
                    if($row['status'] == ACI_Model_Taxa::STATUS_COMMON_NAME) {
                        $resultTable[$i]['url'] .= '/common/' . $row['taxa_id'];
                    }
                    else {
                        $resultTable[$i]['url'] .= '/taxa/' . $row['taxa_id'];
                    }
                }
            }
            else
            {
                $resultTable[$i]['link'] = $this->view->translate('Show_tree');
                $resultTable[$i]['url'] = '/browse/tree/id/' . $row['taxa_id'];
            }
            $resultTable[$i]['name'] = $this->_getSuffix(
                $this->_getSpanTaxonomicName(
                    $this->_getSpanSearchWord(
                        $row['name']
                    ),
                    $row['status'],
                    $row['rank']
                ),
                $row['status'],
                $row['status'] == ACI_Model_Taxa::STATUS_COMMON_NAME ?
                    $row['language'] : $row['author']
            );
            $resultTable[$i]['rank'] = $this->view->translate(
                ACI_Model_Taxa::getRankString($row['rank'])
            );
            if($this->_getParam('action') == 'all')
            {
                $resultTable[$i]['status'] = $this->view->translate(
                    ACI_Model_Taxa::getStatusString($row['status'])
                );
            }
            else {
                $resultTable[$i]['status'] = '%s';
            }
            
            $resultTable[$i]['group'] = $row['kingdom'];
            
            if(!$row['is_accepted_name']) {
                $resultTable[$i]['status'] =
                    sprintf($resultTable[$i]['status'],
                        '<span class="taxonomicName">' .
                        $row['accepted_species_name'] . '</span> ' .
                        $row['accepted_species_author']
                    );
            }
            
            $resultTable[$i]['dbLogo'] = '/images/databases/' .
                $row['db_thumb'];
            $resultTable[$i]['dbLabel'] = $row['db_name'];
            $resultTable[$i]['dbUrl'] =
                '/details/database/id/' . $row['db_id'] .
                '/search/' . $this->view->action .
                '/key/' . $this->_getParam('key');
            $i++;
        }
        return $resultTable;
    }
    
    protected function _getSuffix($source, $status, $suffix)
    {
        switch($status && $suffix != "") {
            case ACI_Model_Taxa::STATUS_COMMON_NAME:
                $source .= ' (' . $suffix . ')';
            break;
            default:
                $source .= '  ' . $suffix;
            break;
        }
        return $source;
    }

    protected function _getSpanTaxonomicName($source, $status, $rank)
    {
        if($status != ACI_Model_Taxa::STATUS_COMMON_NAME &&
            $rank >= ACI_Model_Taxa::RANK_SPECIES) {
            $source = '<span class="taxonomicName">' . $source . '</span>';
        }
        return $source;
    }
    
    protected function _getSpanSearchWord($source)
    {
        return preg_replace(
            '/(' . $this->_getParam('key') . ')/i',
            "<span class=\"field_header\">$1</span>",
            $source
        );
    }
    
    /**
     * Builds the paginator
     *
     * @param Zend_Db_Select $query
     * @param int $page
     * @param int $items
     *
     * @return Zend_Paginator
     */
    protected function _getPaginator(Zend_Db_Select $query, $page, $items)
    {
    	$this->_logger->debug($query);
        $paginator = new Zend_Paginator(
            new Zend_Paginator_Adapter_DbSelect($query));
                
        $paginator->setItemCountPerPage((int)$items);
        $paginator->setCurrentPageNumber((int)$page);
        return $paginator;
    }
    
    /**
     * Returns the corresponding search query based on the requested action
     *
     * @return Zend_Db_Select
     */
    protected function _getSearchQuery($action)
    {
        $select = new ACI_Model_Search($this->_db);
        
        switch($action) {
            case 'common':
                $query = $select->commonNames(
                    $this->_getParam('key'), $this->_getParam('match'),
                    $this->_getParam('sort')
                );
                break;
            case 'all':
            default:
                $query = $select->all(
                    $this->_getParam('key'), $this->_getParam('match'),
                    $this->_getParam('sort')
                );
                break;
        }
        return $query;
    }
    
    /**
     * Returns an array with all taxa names by rank on a dojo-suitable format
     * Used to populate the scientific search combo boxes
     *
     * @return void
     */
    protected function _sendRankData($rank)
    {
        $name = $this->_getParam('name', '*');
        $this->_logger->debug($name);
        
        $search = new ACI_Model_Search($this->_db);
        $res = array_merge(
            array(),
            $search->getRankEntries(
                $rank,
                str_replace('*', '', $name)
            )
        );
        $this->_logger->debug($res);
        $this->view->data = new Zend_Dojo_Data('name', $res, $rank);
        $this->renderScript('search/data.phtml');
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}