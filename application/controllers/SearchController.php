<?php
require_once 'AController.php';
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
class SearchController extends AController
{
    public function commonAction()
    {
        $this->view->title = $this->view->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $form = $this->_getSearchForm();
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function scientificAction()
    {
        // Search hint query request
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            exit(
                $this->getHelper('Query')->fetchTaxaByRank(
                    $fetch, $this->_getParam('q'), $this->_getParam('p')
                )
            );
        }
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        // Results page
        if ($this->_hasParam('match') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $str = '';
            foreach ($form->getInputElements() as $el) {
                if ($el != 'match') {
                    $str .= ' ' . $this->_getParam($el);
                }
            }
            $this->view->searchString = trim($str);
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        // Form page
        } else {
            if (!$formIsValid && $this->_hasParam('match')) {
                $this->view->formError = true;
                $this->_setSessionFromParams($form->getInputElements());
            }
            if ($this->_getParam('submit', 1)) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');
        $form = $this->_getSearchForm();
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
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
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($formHeader, $form);
        }
    }
    
    public function exportAction()
    {
        $query = $this->getHelper('Query')->getLatestQuery();
        if ($this->_hasParam('export') && $query) {
            $this->view->layout()->disableLayout();
            $controller = $this->getHelper('Query')->getLatestQueryController();
            $action = $this->getHelper('Query')->getLatestQueryAction();
            $this->getHelper('Export')->csv(
                $controller,
                $action,
                $this->getHelper('Query')->getLatestSelect(),
                'CoL_data.csv'
            );
        }
        $this->getHelper('Query')->getLatestSelect();
        $this->view->form = $this->getHelper('FormLoader')->getExportForm();
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}