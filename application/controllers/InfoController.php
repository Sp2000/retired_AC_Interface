<?php
/**
 * Annual Checklist Interface
 *
 * Class InfoController
 * Defines the info pages
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class InfoController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
    
    public function init ()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function aboutAction ()
    {
        $this->view->title = $this->view->translate('Info_about');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function acAction ()
    {
        $this->view->title = sprintf($this->view
            ->translate('Info_annual_checklist'), $this->view->app->version);
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function databasesAction ()
    {
        $this->view->title = $this->view->translate('Source_databases');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $defaultSortCol = 'source';
        $sortCol = $this->_getParam('sort', $defaultSortCol);
        $this->view->sort = $sortCol;
        
        $dbTable = new ACI_Model_Table_Databases();
        $this->view->results =
            $dbTable->getAll(
                array_merge(
                    array(ACI_Model_Info::getRightColumnName($sortCol)),
                    array(ACI_Model_Info::getRightColumnName($defaultSortCol))
            ));
    }
    
    public function hierarchyAction ()
    {
        $this->view->title = $this->view->translate('Management_hierarchy');
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function copyrightAction ()
    {
        $this->view->title = $this->view
            ->translate('Copyright_reproduction_sale');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function citeAction ()
    {
        $this->view->title = $this->view->translate('Cite_work');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function websitesAction ()
    {
        $this->view->title = $this->view->translate('Web_sites');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function contactAction ()
    {
        $this->view->title = $this->view->translate('Contact_us');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function acknowledgementsAction ()
    {
        $this->view->title = $this->view->translate('Acknowledgements');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('about');
    }
}