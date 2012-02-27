<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_Search
 * Search dojo-enabled form
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_Search extends Zend_Dojo_Form
{
    protected $_action;
    
    public function __construct($action)
    {
        $this->_action = (string)$action;
        parent::__construct();
    }
    
    public function init ()
    {
        $this->setAttribs(
            array(
                'id' => 'searchForm',
                'name' => 'searchForm'
            )
        );
        $this->setMethod(Zend_Form::METHOD_POST);
        $translator = Zend_Registry::get('Zend_Translate');
        
        $key = $this->createElement('TextBox', 'key');
                
        $key->setLabel($translator->translate('Search_for') . ':')
            ->addErrorMessage(null);
            
        $this->addErrorMessage('Error_key_too_short');
        
        $match = $this->createElement('CheckBox', 'match')->setValue(1)
            ->setLabel('Match_whole_words_only');
/*        $match = $this->createElement('radio','match')->setValue(2)
          ->addMultiOption(2,'Match_starts_with')
          ->addMultiOption(1,'Match_whole_words_only')
          ->addMultiOption(0,'Match_all');*/
        
        $match->getDecorator('label')->setOption('placement', 'append');
        $submit = $this->createElement('SubmitButton', 'search')
            ->setLabel($translator->translate('Search'));
            
        $this->addElement($key)->addElement($match)->addElement($submit);
    
        if ($this->_action == "all" && $this->_moduleEnabled("fuzzy_search"))
        {
            $fuzzy = $this->createElement('CheckBox', 'fuzzy')->setValue(0)
                ->setLabel('Use_fuzzy_search');
            $fuzzy->getDecorator('label')->setOption('placement', 'append');
            $this->addElement($fuzzy);
        }
        
        $this->addDisplayGroup(array('key'), 'keyGroup');
        $this->addDisplayGroup(array('match', 'fuzzy'), 'matchGroup');
        $this->addDisplayGroup(array('search'), 'submitGroup');

        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'search-form')),
                    'Form'
            )
        );
        
        /*$this->setDecorators(
        	array(
        		'FormElements',
                array(
                	'HtmlTag',
        			array(
        				'tag' => 'div', 'id' => 'map_canvas'
        			),
        			'Form'
        		)
        	)
        );*/
        
        $this->setAttrib('onsubmit', 'submitSearchForm');
    }
    
    public function getInputElements()
    {
        return ($this->_action == "all") ? array('key', 'match', 'fuzzy') : array('key', 'match');
    }
    
    /**
     * Validates the form
     * @see library/Zend/Zend_Form#isValid($data)
     * @param array $value
     * @return boolean
     */
    public function isValid($data)
    {
        // Form not submited
        if (!isset($data['match'])) {
            return true;
        }
        if (!isset($data['key'])) {
            $this->markAsError();
            return false;
        }
        $validator = new Eti_Validate_AlphaNumStringLength(2);
        $valid = $validator->isValid($data['key']);
        if (!$valid) {
            $this->markAsError();
            return false;
        }
        return true;
    }
    
    public function render(Zend_View_Interface $view = null)
    {
        if (null === $view) {
            $view = $this->getView();
        }
        $loader = $view->getPluginLoader('helper');
        if ($loader->getPaths('Zend_Dojo_View_Helper')) {
            $loader->removePrefixPath('Zend_Dojo_View_Helper');
        }
        return parent::render($view);
    }
    
    public function getErrorMessage()
    {
        $em = $this->getErrorMessages();
        return $em ?
            Zend_Registry::get('Zend_Translate')->translate(current($em)) :
            null;
    }

    protected function _moduleEnabled ($module)
    {
        return Bootstrap::instance()->getOption('module.' . $module);
    }
}