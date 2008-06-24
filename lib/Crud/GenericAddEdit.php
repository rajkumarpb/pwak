<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK (PHP Web Application Kit) framework.
 *
 * PWAK is a php framework initially developed for the
 * {@link http://onlogistics.googlecode.com Onlogistics} ERP/Supply Chain
 * management web application.
 * It provides components and tools for developers to build complex web
 * applications faster and in a more reliable way.
 *
 * PHP version 5.1.0+
 * 
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @package   PWAK
 * @author    ATEOR dev team <dev@ateor.com>
 * @copyright 2003-2008 ATEOR <contact@ateor.com> 
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   SVN: $Id$
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once('HTML/QuickForm.php');

// doc {{{
/**
 * GenericAddEdit:
 *
 * {@inheritdoc}
 *
 * Classe de base des formulaires add/edit/delete.
 *
 * Le composant se compose d'une classe GenericAddEdit, cette classe, dans une
 * utilisation minimaliste, s'instancie avec 2 arguments: le nom de la classe
 * sujet, et le titre de la page à afficher (pour le form addedit).
 * Il suffit ensuite d'appeler la méthode render().
 * Il est possible pour des raisons de flexibilité, ou de spécifités de cetains
 * formulaire d'hériter de cette classe et de surcharger et définir un certain
 * nombre de méthodes:
 *
 * Méthodes surchageables:
 * ----------------------
 *     - onBeforeHandlePostData(): surchargez cette méthode pour les checks
 *       supplémentaires par exemple,
 *     - onAfterHandlePostData(): surchargez cette méthode pour effectuer
 *       d'autres actions après sauvegarde mais avant transaction,
 *     - onBeforeDelete(): surchargez cette méthode si vous devez effectuer
 *       des actions/vérifications avant suppression de l'objet (uniquement
 *       pour une action delete donc),
 *     - onAfterDelete(): surchargez cette méthode si vous devez effectuer
 *       des actions après la suppression de l'objet (uniquement pour une
 *       action delete ici aussi),
 *     - getAddEditMapping(): surchargez cette méthode pour spécifier
 *       explicitement le mapping à utiliser pour le formulaire add/edit, ou
 *       pour le récupérer de façon alternative (bdd, xml, etc...). Voir la
 *       section "Format du mapping" pour plus d'informations.
 *     - onBeforeDisplay(): surchargez cette méthode si vous devez effectuer
 *       des actions avant l'affichage du formulaire.
 *
 * Méthodes qui peuvent être définies dans les classes filles:
 * ----------------------------------------------------------
 *     - render<property>(): ou <property> correspond au nom d'une propriété de
 *       l'objet en cours d'édition/ajout. Si cette méthode est définie elle
 *       sera utilisée pour le "render" de l'élément correspondant du
 *       formulaire. Il est donc nécessaire que cette méthode créée l'élément
 *       HTML_QuickForm_Element et l'ajoute au form (propriété form) de la
 *       classe (voir ex avec héritage pour plus de détails),
 *     - handle<Property>(): ou <property> correspond au nom d'une propriété de
 *       l'objet en cours d'édition/ajout. Si cette méthode est définie elle
 *       sera appelée pour gérer le set<property> au moment de la gestion de
 *       l'envoi du formulaire et ce dans la transaction. Cette méthode est
 *       toujours appelée avec en paramètre la valeur $_POST correspondante,
 *     - handle<Property>Collection(): même chose mais pour les liens *..*.
 *
 * Format du mapping:
 * -----------------
 * Le mapping des propriétés add/edit est un tableau de la sorte:
 * <code>
 * array(
 *     'SectionName1'=>array(
 *         'PropertyName1'=>array(
 *             'label'=>'label du champs de formulaire',
 *             'options'=>array(
 *                 'style="width: 100%;"',
 *                 'onclick="maFonction();"',
 *                 [...]
 *             ),
 *             'typespecific_options'=>array('Mon option spécifique'),
 *             'validationrules'=>array(
 *                  'numeric'=>'Mon alerte',
 *                  'required'=>'Champs requis message'),
 *             'filters'=>array('trim', 'stripslashes')
 *         ),
 *         'PropertyName1'=>array([...]),
 *         'PropertyNameN'=>array([...]),
 *     ),
 *     'SectionName2'=>array([...]),
 *     'SectionNameN'=>array([...]),
 * )
 * </code>
 *
 * Notes:
 *     - ici SectionName correspond au label du header qui regroupera les
 *       propriétés qu'il contient, si la cléf est un entier le header ne sera
 *       pas présent dans le formulaire,
 *     - XXX: la cléf 'typespecific_options' n'est pas gérée pour le moment et
 *       est à documenter, elle pourra par exemple être utilisée pour des
 *       options spécifiques à un type de données (par ex. savoir si on propose
 *       un textfield avec autocomplétion des noms pour une foreignkey, plutôt
 *       qu'un select).
 *
 * Exemple de base:
 * ---------------
 * <code>
 * require_once('config.inc.php');
 *
 * $genericAddEdit = new GenericAddEdit(
 *     array(
 *         'clsname'  => 'Account',
 *         'title'    => 'Ajout/édition de comptes',
 *         'profiles' => array(PROFILE_ADMIN, PROFILE_AEROADMIN)
 *      )
 *  );
 * $genericAddEdit->render();
 * </code>
 *
 * Exemple avec héritage:
 * ---------------------
 * <code>
 * require_once('config.inc.php');
 *
 * class AccountAddEdit extends GenericAddEdit {
 *      function __construct() {
 *          parent::__construct(
 *              array(
 *                  'clsname'  => 'Account',
 *                  'title'    => 'Ajout/édition de comptes',
 *                  'profiles' => array(PROFILE_ADMIN, PROFILE_AEROADMIN)
 *              )
 *          );
 *      }
 *
 *      function renderNumber() {
 *          // gestion particulière du widget utilisé pour le numéro de compte
 *          // on met un select plutôt qu'un textfield
 *          $elt = HTML_QuickForm::createElement(
 *              'select',
 *              'Account_Number',
 *              _('Numéro de compte'),
 *              range(0, 1000)
 *          );
 *          $this->form->addElement($elt);
 *      }
 *
 *      function handleName($postdata) {
 *          // gestion custom du nom du compte
 *          // on rajoute la chaine "Compte " avant le nom
 *          $this->object->setName('Compte ' . $postdata);
 *      }
 * }
 *
 * $accountAddEdit = new AccountAddEdit();
 * $accountAddEdit->render();
 * </code>
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 * @subpackage Crud
 */ //}}}
class GenericAddEdit extends GenericController {
    // constantes {{{

    const PW_NOCHG = '__pwnochg__';

    // }}}
    // propriétés {{{

    /**
     * title de la section head du formulaire.
     *
     * @var string formTitle
     * @access protected
     */
    protected $formTitle = '';

    /**
     * Instance de HTML_QuickForm.
     *
     * @var object HTML_QuickForm form
     * @access protected
     */
    protected $form = false;

    /**
     * Instance de HTML_QuickForm.
     *
     * @var object HTML_QuickForm form
     * @access protected
     */
    protected $innerForm = false;

    /**
     * Instance de GenericAddEdit.
     *
     * @var object GenericAddEdit
     * @access protected
     */
    protected $parentForm = false;

    /**
     * Instance de Template.
     *
     * @var object Template
     * @access protected
     */
    protected $template = false;

    /**
     * L'URL de retour pour les actions add/edit/delete.
     *
     * @var string
     * @access protected
     */
    protected $url = false;

    /**
     * Les valeurs par défaut du formulaire (pour le mode édition)
     *
     * @var array
     * @access protected
     */
    protected $formDefaults = array();

    /**
     * Tableau comprenant les elements blank "séparateurs" du form.
     *
     * @var array
     * @access private
     */
    protected $_blankElements = array();

    // }}}
    // GenericAddEdit::__construct() {{{
    /**
     * Le constructeur prend en paramètres un tableau $params qui peut contenir
     * les valeurs suivantes:
     *
     * array(
     *     // paramètres obligatoires
     *     'clsname'     => 'Toto', // le nom de l'entité de base
     *     'action'      => 'edit', // le nom de l'action (add, edit ou del)
     *
     *     // obligatoire pour une action edit ou delete
     *     'id'          => 1, // l'id de l'objet à éditer (on peut aussi
     *                         // passer un tableau d'ids pour l'action delete)
     *
     *     // paramètres facultatifs
     *     'use_session' => true,    // si on doit utiliser le sessions
     *     'profiles'    => array(), // un tableau de profils
     *     'return_url'  => 'a.php', // l'url de retour à utiliser
     *     'title'       => 'foo',   // titre du formulaire à utiliser
     * )
     *
     * @access protected
     * @param array $params tableau de paramètres
     * @return void
     */
    public function __construct($params) {
        parent::__construct($params);
        $this->template = new Template();
        if (isset($params['url'])) {
            $this->url = $params['url'];
        }
        if ($this->action != GenericController::FEATURE_DELETE) {
            $this->form = new HTML_QuickForm($this->clsname . 'AddEdit');
        }
    }

    // }}}
    // GenericAddEdit::render() {{{

    /**
     * Méthode principale qui appelle les sous méthodes pour construire,
     * traiter et afficher le formulaire.
     *
     * @access public
     * @return void
     */
    public function render($template = false) {
        if ($this->useSession) {
            $this->includeSessionRequirements();
        }
        $this->session = Session::singleton();
        SearchTools::ProlongDataInSession();
        $this->auth();
        $this->initialize();
        if (!$this->parentForm && $this->action == GenericController::FEATURE_DELETE &&
            in_array(GenericController::FEATURE_DELETE, $this->features)) {
            if (!isset($_REQUEST['confirm_delete'])) {
                Template::confirmDialog(
                    I_DELETE_ITEMS,
                    $_SERVER['REQUEST_URI'] . '&confirm_delete=1',
                    $this->guessReturnURL()
                );
                exit();
            }
            $this->delete();
            Tools::redirectTo($this->guessReturnURL());
            exit(0);
        }
        $this->buildForm();
        $this->onBeforeDisplay();
        if ($this->action == GenericController::FEATURE_VIEW) {
            $this->form->freeze();
        } else if (
            (isset($_POST['fromAddButton']) && $_POST['fromAddButton'] == '1')
            || (isset($_POST['submitFlag']) && $this->form->validate()))
        {
            $values = $this->form->exportValues();
            $ret = $this->form->process(array($this, 'handlePostData'), $values);
            // le script sort ici, car redirigé par la méthode onFinish()
            // à moins qu'on ait surchargé celle ci.
        }
        if (!$this->parentForm) {
            $additionalContent = $this->additionalFormContent();
            $smarty = $this->template;
            $smarty->assign('preContent', $this->preContent());
            $smarty->assign('form', $this->form->toArray(true));
            $smarty->assign('formTitle',
                empty($this->formTitle)?'&nbsp;':$this->formTitle);
            $template = !$template ? GENERIC_ADDEDIT_TEMPLATE : $template;
            $smarty->assign('additionalContent', $additionalContent);
            $smarty->assign('postContent', $this->postContent());
            $content = $smarty->fetch($template);
            $method = $this->useAJAX?'ajaxPage':'page';
            Template::$method($this->title, $content, $this->jsRequirements,
                array(), $this->htmlTemplate);
        }
    }

    // }}}
    // GenericAddEdit::assignTemplateVar() {{{

    /**
     * Permet d'assigner des variables de template supplementaires
     *
     * @param array $ : tableau associatif de la forme:
     *         array('varName1' => 'value1',
     *               'varName2' => 'value2',
     *               .... )
     * @access public
     */
    public function assignTemplateVar($values) {
        $tpl = $this->template;
        foreach($values as $name=>$value) {
            $tpl->assign($name, $value);
        }
    }

    // }}}
    // GenericAddEdit::handlePostData() {{{

    /**
     * Méthode callback qui gère la soumission du formulaire add/edit.
     * Elle est appelée via callback par la méthode process() de HTML_QuickForm
     *
     * @access public
     * @param array $values les données du formulaire.
     * @return void
     */
    public function handlePostData($values) {
        if (!$this->innerForm) {
            Database::connection()->startTrans();
        }
        $this->onBeforeHandlePostData();
        if ($this->object->getId() == 0) {
            $this->object->generateId();
            $this->objID = $this->object->getId();
        }
        // gestion des propriétés et des fkeys
        foreach($this->attrs as $property=>$class) {
            $setter = 'set' . $property;
            $getter = 'get' . $property;
            if (!method_exists($this->object, $setter)) {
                continue;
            }
            $eltname = $this->clsname . '_' . $property;
            if (!is_string($class) && isset($values[$eltname])) {
                // propriétés simples
                $data = $values[$eltname];
            } else if(isset($values[$eltname.'_ID'])) {
                // on a une fkey
                $data = $values[$eltname . '_ID'];
                $getter .= 'Id';
            } else if(isset($_FILES[$eltname])) {
                $data = $_FILES[$eltname];
            }
            if (isset($data)) {
                $custom = 'handle' . $property;
                if (method_exists($this, $custom)) {
                    call_user_func(array($this, $custom), $data);
                    continue;
                }
                if ($class == Object::TYPE_PASSWORD) {
                    if ($data != $values[$eltname . '_Again']) {
                        Template::errorDialog(E_PASSWD_MISMATCH, $this->url);
                        exit(1);
                    }
                    if ($data != self::PW_NOCHG) {
                        $this->object->$setter($data);
                    }
                } elseif ($class == Object::TYPE_FILE_UPLOAD) {
                    if (isset($_FILES[$eltname]['name']) && !empty($_FILES[$eltname]['name'])) {
                        try {
                            $uploader = new Upload($eltname);
                            if (UPLOAD_STORAGE == 'db') {
                                // stock l'image ou le fichier en bd
                                $uploader->dbstore();
                                $this->object->$setter($_FILES[$eltname]['name']);
                            } else {
                                // stock l'image ou le fichier en dur et écrit le chemin en bd
                                $uploader->store(UPLOAD_STORAGE);
                                $this->object->$setter($data);
                            }
                        } catch (Exception $exc) {
                            Template::errorDialog($exc->getMessage(), $this->guessReturnURL());
                            exit;
                        }
                    }
                } else if ($class == Object::TYPE_IMAGE) {
                    if (isset($_FILES[$eltname]['name']) && !empty($_FILES[$eltname]['name'])) {
                        try {
                            $manager = new ImageManager($eltname);
                            $manager->dbstore();
                            $this->object->$setter($_FILES[$eltname]['name']);
                        } catch (Exception $exc) {
                            Template::errorDialog($exc->getMessage(), $this->guessReturnURL());
                            exit;
                        }
                    }
                } elseif($class == Object::TYPE_DATETIME) {
                    $this->object->$setter($data . ':00');
                    /*$datetime = explode(' ', $data);
                    if (count($datetime) == 2) {
                        list($date, $time) = $datetime;
                        $date =  explode('/', $date);
                        if (count($date) == 3) {
                            list($d, $m, $y) = $date;
                            $data = sprintf('%04d-%02d-%02d %s:00', $y, $m, $d, $time);
                            $this->object->$setter($data);
                        }
                    }*/
                } elseif(in_array($class, array(Object::TYPE_FLOAT, Object::TYPE_DECIMAL))) {
                    $this->object->$setter(I18N::extractNumber($data));
                } elseif (is_string($class)) {
                    if ($this->innerForm && $this->innerForm->clsname == $class) {
                        $data = $this->innerForm->object->getId();
                    }
                    $this->object->$setter($data);
                } else {
                    $this->object->$setter($data);
                }
                unset($data);
            }
        }
        // gestion des relations *..*
        foreach($this->links as $property=>$detail) {
            $setter = 'set' . $property . 'CollectionIds';
            if (!method_exists($this->object, $setter)) {
                continue;
            }
            $eltname = $this->clsname . '_' . $property . '_IDs';
            // XXX FIXME hack pour les advmultiselect, arranger ça
            if (!isset($values[$eltname])) {
                $eltname = 'advmultiselect' . $eltname;
                if(!isset($values[$eltname])) {
                    $this->object->$setter(array());
                }
            }
            // end hack
            if (isset($values[$eltname])) {
                $data = $values[$eltname];
                if (!is_array($data)) {
                    $data = array($data);
                }
                $custom = 'handle' . $property . 'Collection';
                if (method_exists($this, $custom)) {
                    call_user_func(array($this, $custom), $data);
                    continue;
                }
                $this->object->$setter($data);
            }
        }
        $this->onAfterHandlePostData();
        // pour l'action add sur les selects fkey
        if (isset($_REQUEST['fromEntity'])) {
            list($entity, $property) = explode(':', $_REQUEST['fromEntity']);
            if (isset($_SESSION['_' . $entity . '_'])) {
                $setter = 'set'.$property;
                $_SESSION['_'.$entity.'_']->$setter($this->object->getId());
            }
        }
        if (!$this->parentForm) {
            if ($values['fromAddButton'] == '1' && !empty($values['redirectURL'])) {
                // un bouton add/edit de fkey a été clické
                Tools::redirectTo($values['redirectURL']);
                exit(0);
            }
        }
        $this->save();
        if (!$this->parentForm) {
            if (Database::connection()->hasFailedTrans()) {
                $err = Database::connection()->errorMsg();
                trigger_error($err, E_USER_WARNING);
                Database::connection()->rollbackTrans();
                Template::errorDialog(E_ERROR_SQL . '.<br />' . $err, $this->guessReturnURL());
                exit;
            }
            Database::connection()->completeTrans();
            $this->form->setDefaults($values);
            $this->onFinish();
        }
    }

    // }}}
    // GenericAddEdit::save() {{{

    /**
     * Sauve l'objet en base de données.
     *
     * @access protected
     * @return void
     */
    protected function save() {
        try {
            $this->object->save();
        } catch (Exception $exc) {
            Template::errorDialog($exc->getMessage(), $this->url);
            exit;
        }
    }

    // }}}
    // GenericAddEdit::initialize() {{{

    /**
     * Méthode d'initialisation, récupère l'url de retour et charge l'objet en
     * bdd ou en crée un nouvel.
     *
     * @access protected
     * @return void
     */
    protected function initialize() {
        // si l'action est delete, pas la peine de charger l'objet...
        if ($this->action == GenericController::FEATURE_DELETE) {
            return;
        }
        // chargement de l'objet
        if ($this->useSession
            && isset($_SESSION['_' . $this->clsname . '_'])) {
            $this->object = $_SESSION['_' . $this->clsname . '_'];
            if($this->action == GenericController::FEATURE_ADD) {
                $this->object->setId(0);
                $this->objID = false;
            }
        } else if ($this->objID) {
            $mapper = Mapper::singleton($this->clsname);
            $this->object = $mapper->load(array('Id'=>$this->objID));
            if (!($this->object instanceof Object)) {
                $clsname = $this->clsname;
                $this->object = new $clsname();
            }
        } else {
            $clsname = $this->clsname;
            $this->object = new $clsname();
        }
        if (!$this->parentForm && $this->useSession) {
            $this->session->register('_' . $this->clsname . '_', $this->object, 1);
        }
    }

    // }}}
    // GenericAddEdit::onBeforeHandlePostData() {{{

    /**
     * Méthode appelée dans la transaction avant ajout ou édition de l'objet.
     *
     * @access protected
     * @return void
     */
    protected function onBeforeHandlePostData() {
        // Surchargez cette méthode si besoin
    }

    // }}}
    // GenericAddEdit::onAfterHandlePostData() {{{

    /**
     * Méthode appelée dans la transaction après ajout ou édition de l'objet.
     *
     * @access protected
     * @return void
     */
    protected function onAfterHandlePostData() {
        // Surchargez cette méthode si besoin
    }

    // }}}
    // GenericAddEdit::onFinish() {{{

    /**
     * Méthode appelée à la fin d'une action add/edit ou delete.
     * L'action par défaut est de rediriger vers l'url de retour.
     *
     * @access protected
     * @return void
     */
    protected function onFinish() {
        // Surchargez cette méthode si besoin
        unset($_SESSION['_' . $this->clsname . '_']);
        Tools::redirectTo($this->guessReturnURL());
        exit(0);
    }

    // }}}
    // GenericAddEdit::delete() {{{

    /**
     * Méthode qui gère l'action delete, supprime l'objet dans une transaction.
     *
     * @access protected
     * @return void
     */
    protected function delete() {
        $this->onBeforeDelete();
        Database::connection()->startTrans();
        $mapper = Mapper::singleton($this->clsname);
        $emptyForDeleteProperties = call_user_func(array($this->clsname,
            'getEmptyForDeleteProperties'));
        $notDeletedObjects = array();
        // il y a des check auto on supprime un à un car les verif ne sont
        // pas faites par Mapper::delete() mais par Object::delete()
        $col = $mapper->loadCollection(array('Id'=>$this->objID));
        $count = $col->getCount();
        for($i=0 ; $i<$count ; $i++) {
            $o = $col->getItem($i);
            try {
                $o->delete();
            } catch (Exception $exc) {
                $notDeletedObjects[] = $o->toString(); //. ': ' . $exc->getMessage();
            }
        }
        if (Database::connection()->hasFailedTrans()) {
            $err = Database::connection()->errorMsg();
            trigger_error($err, E_USER_WARNING);
            Database::connection()->rollbackTrans();
            Template::errorDialog(E_ERROR_SQL . '.<br/>' . $err, $this->guessReturnURL());
            exit;
        }
        Database::connection()->completeTrans();
        if(!empty($notDeletedObjects)) {
            Template::infoDialog(
                sprintf(I_NOT_DELETED_WITH_LIST,
                implode('</li><li>', $notDeletedObjects)),
                $this->guessReturnURL());
            exit;
        }
        $this->onAfterDelete();
    }

    // }}}
    // GenericAddEdit::onBeforeDelete() {{{

    /**
     * Méthode appelée dans la transaction avant suppression de l'objet.
     *
     * @access protected
     * @return void
     */
    protected function onBeforeDelete() {
        // Surchargez cette méthode si besoin
    }

    // }}}
    // GenericAddEdit::onAfterDelete() {{{

    /**
     * Méthode appelée dans la transaction après suppression de l'objet.
     *
     * @access protected
     * @return void
     */
    protected function onAfterDelete() {
        // Surchargez cette méthode si besoin
    }

    // }}}
    // GenericAddEdit::buildForm() {{{

    /**
     * Construit le formulaire quickform à partir du tableau des propriétés de
     * l'objet courant.
     *
     * @access protected
     * @return void
     */
    public function buildForm($forceNotRequired = false) {
        // champs cachés nécessaires
        unset($this->form->_attributes['name']);
        $this->form->addElement('hidden', 'submitFlag', '1', 'id="submitFlag"');
        $this->form->addElement('hidden', 'retURL', $this->guessReturnURL(),
            'id="retURL"');
        $this->form->addElement('hidden', 'entity', $this->clsname,
            'id="entity"');
        $this->form->addElement('hidden', 'altname', $this->altname,
            'id="altname"');
        $this->form->addElement('hidden', 'action', $this->action,
            'id="action"');
        $this->form->addElement('hidden', 'objID', $this->object->getId(),
            'id="objID"');
        $this->form->addElement('hidden', 'redirectURL', '',
            'id="redirectURL"');
        $this->form->addElement('hidden', 'fromAddButton', '0',
            'id="fromAddButton"');
        if (isset($_REQUEST['fromEntity'])) {
            $this->form->addElement('hidden', 'fromEntity',
                $_REQUEST['fromEntity'], 'id="fromEntity"');
        }
        // actions valider et annuler
        $this->form->addElement('header', 'actions', '');
        if ($this->action == GenericController::FEATURE_VIEW) {
            $this->form->addElement('button', null, A_BACK,
                'id="backButton" class="button" '
                . 'onclick="window.location.href=\''
                . $this->guessReturnURL() . '\'"');
        } else {
            $this->form->addElement('submit', 'submitButton', A_VALIDATE,
                'id="submitButton" class="button"');
            $this->form->addElement('button', 'cancelButton', A_CANCEL,
                'id="cancelButton" class="button" '
                . 'onclick="window.location.href=\''
                . $this->guessReturnURL() . '\'"');
        }
        $hasRequiredFields = false;
        $i = 0;
        $j = 0;
        // on récupère le mapping qui va nous permettre de construire le form
        $mapping = $this->getAddEditMapping();
        $hasPasswd = false;
        $addLater = array();
        foreach ($mapping as $section=>$elements) {
            // header
            if ($i == 0) {
                // première section, on assign le formTitle
                $this->formTitle = $section;
            }
            $sectionID = sprintf('header%02d', $i);
            $this->form->addElement('header', $sectionID, $section);
            // elements du formulaire
            foreach($elements as $name=>$params) {
                if (isset($this->_blankElements["$j"]) 
                  && $this->_blankElements["$j"] === true) {
                    $this->form->addElement(new HTML_QuickForm_Static());
                }
                $j++;
                $ename = $this->clsname . '_' . $name;
                $type = $this->getElementType($name);
                if ($type == Object::TYPE_MANYTOMANY) {
                    // XXX FIXME hack pour advmultiselect
                    $ename = 'advmultiselect' . $this->clsname . '_' . $name . '_IDs';
                }
                // si une méthode custom existe on l'appelle
                $customMethod = 'render' . $name;
                if (method_exists($this, $customMethod)) {
                    $this->$customMethod($ename, $params);
                    continue;
                }
                // sinon on crée l'élément
                $label = $params['label'];
                $opt = isset($params['options'])?$params['options']:'';
                $required = isset($params['required']) && $params['required'] && !$forceNotRequired;
                $editInplace = isset($params['inplace_edit']) && $params['inplace_edit'];
                $aeButton = isset($params['add_button']) && $params['add_button'];
                // XXX TODO passer un tableau plutôt
                $elts = $this->_createElement($name, $ename, $type, $label,
                    $opt, $required, $editInplace, $aeButton);
                if ($editInplace) {
                    $addLater = array_merge($addLater, $elts);
                } else if (count($elts) == 1) {
                    $this->form->addElement($elts[0]);
                } else {
                    $this->form->addGroup($elts, $ename . '_Group', $label, null, false);
                }
                // gestion de la valeur par défaut
                $getter = 'get'.$name;
                $arg = false;
                if ($type == Object::TYPE_FKEY) {
                    $getter = 'get' . $name . 'Id';
                    $ename = $this->clsname . '_' . $name . '_ID';
                } else if ($type == Object::TYPE_MANYTOMANY) {
                    $getter = 'get' . $name . 'CollectionIds';
                } else if ($type == Object::TYPE_TIME) {
                    $arg = 'H:i';
                } else if ($type == Object::TYPE_DATE) {
                    $arg = I18N::DATE_LONG;//getHTMLSelectDateFormat();
                } else if ($type == Object::TYPE_DATETIME) {
                    $arg = I18N::DATETIME_LONG;//I18N::getHTMLSelectDateFormat() . ' H:i';
                }
                if (method_exists($this->object, $getter)) {
                    if ($type == Object::TYPE_PASSWORD) {
                        $this->formDefaults[$ename] = self::PW_NOCHG;
                        $this->formDefaults[$ename . '_Again'] = self::PW_NOCHG;
                        $hasPasswd = true;
                    } else if ($type == Object::TYPE_DATE) {
                        $date = $this->object->$getter() . ' 00:00:00';
                        $this->formDefaults['displayed'.$ename] = I18N::formatDate($date, $arg);
                        $this->formDefaults[$ename] = $this->object->$getter();
                    } else if ($type == Object::TYPE_DATETIME) {
                        $this->formDefaults['displayed'.$ename] = I18N::formatDate($this->object->$getter(), $arg);
                        $this->formDefaults[$ename] = $this->object->$getter();
                    } else if (in_array($type, array(Object::TYPE_FLOAT,
                        Object::TYPE_DECIMAL))) {
                        $dec_num = isset($params['dec_num']) ? $params['dec_num'] : 2;
                        $this->formDefaults[$ename] = I18N::formatNumber(
                            $this->object->$getter(), $dec_num);
                    } else if ($arg) {
                        $this->formDefaults[$ename] = $this->object->$getter($arg);
                    } else {
                        $this->formDefaults[$ename] = $this->object->$getter();
                    }
                }
                // validations et filtres selon le type
                if ($type == Object::TYPE_INT) {
                    // ajoute une validation numérique
                    $msg = sprintf(E_VALIDATE_FIELD . ' "%s" ' . E_VALIDATE_IS_INT,
                        $params['label']);
                     $this->form->addRule($ename, $msg, 'numeric', '', 'client');
                } else if ($type == Object::TYPE_FLOAT || $type == Object::TYPE_DECIMAL) {
                    // ajoute une validation nombre flottant
                    $msg = sprintf(
                        E_VALIDATE_FIELD . ' "%s" ' . E_VALIDATE_IS_DECIMAL,
                        substr($params['label'], 0, -2)
                    );
                    $this->form->addRule($ename, $msg, 'regex', '/\d+[\.,]?\d*/',
                        'client');
                } else if ($type == Object::TYPE_URL) {
                    // ajoute une validation sur le format de l''url
                    $msg = sprintf(
                        E_VALIDATE_FIELD . ' "%s" ' . E_VALIDATE_IS_URL,
                        $params['label']
                    );
                    $rx = '/^(http|https|ftp|news):\/\/.*$/';
                    $this->form->addRule($ename, $msg, 'regex', $rx, 'client');
                } else if ($type == Object::TYPE_EMAIL) {
                    // ajoute une validation sur le format de l''url
                    $msg = sprintf(
                        E_VALIDATE_FIELD . ' "%s" ' . E_VALIDATE_IS_EMAIL,
                        $params['label']
                    );
                    $this->form->addRule($ename, $msg, 'email', '', 'client');
                }
                // gestion des règles de validation
                if ($required) {
                    $msg = sprintf(E_VALIDATE_FIELD . ' "%s" ' . E_VALIDATE_IS_REQUIRED, $label);
                    if ($type == Object::TYPE_FKEY) {
                        // cas special pour les fk required ne suffit pas la
                        // valeur ## est accepté par required
                        $this->form->addRule($ename, $msg, 'numeric', '', 'client');
                        $this->form->addRule($ename, $msg, 'numeric');
                    }
                    $this->form->addRule($ename, $msg, 'required', '', 'client');
                    $this->form->addRule($ename, $msg, 'required');
                }
                if (isset($params['validationrules'])) {
                    foreach ($params['validationrules'] as $rule=>$msg) {
                        if (!$hasRequiredFields && $rule == 'required') {
                            $hasRequiredFields = true;
                        }
                        // validation côté et client
                        $this->form->addRule($ename, $msg, $rule, '', 'client');
                        // et côté serveur
                        $this->form->addRule($ename, $msg, $rule);
                    }
                }
                // gestion des filtres
                if (isset($params['filters'])) {
                    foreach ($params['filters'] as $filter) {
                        $this->form->applyFilter($ename, $filter);
                    }
                }
            }
            $i++;
        }
        foreach ($addLater as $elt)
            $this->form->addElement($elt);
        if ($this->innerForm) {
            // gestion validations
            foreach ($this->innerForm->form->_rules as $id=>$rule) {
                foreach ($rule as $index=>$ruleItem) {
                    $this->form->addRule(
                        $id,
                        $ruleItem['message'],
                        $ruleItem['type'],
                        $ruleItem['format'],
                        $ruleItem['validation']
                    );
                }
            }
            $this->innerForm->form->_rules = array();
        }
        if ($hasPasswd) {
            //$this->form->_attributes['autocomplete'] = 'off';
        }
        // on assigne le tableau des valeurs
        $this->form->setDefaults($this->formDefaults);
        // traduction pour les messages de validation js
        $this->form->setJsWarnings(E_VALIDATE_FORM . ' : ', '');
        $asterisc = '<span style="color: #f00;">*</span>&nbsp;';
        $this->form->setRequiredNote($asterisc . E_VALIDATE_REQUIRED_FIELD);
    }

    // }}}
    // GenericAddEdit::getAddEditMapping() {{{

    /**
     * Retourne le tableau représentant le "mapping" du formulaire.
     * Cette méthode peut être surchargée, pour par ex. spécifier explicitement
     * le mapping que l'on veut, ou encore récupérer celui-ci de façon
     * differente (bdd, fichier xml, etc...).
     *
     * @access protected
     * @return array le tableau correspondant au mapping du formulaire
     */
    protected function getAddEditMapping() {
        $mapping  = $this->getMapping();
        $return   = array();
        foreach ($mapping as $name=>$data) {
            if (!in_array('addedit', $data['usedby'])) {
                continue;
            }
            if (!array_key_exists($data['section'], $return)) {
                $return[$data['section']] = array();
            }
            $return[$data['section']][$name] = array(
                'label' => isset($data['label']) ?
                    $data['label'] : $name,
                'required' => isset($data['required']) ?
                    $data['required'] : false,
                'inplace_edit' => isset($data['inplace_edit']) ?
                    $data['inplace_edit'] : false,
                'add_button' => isset($data['add_button']) ?
                    $data['add_button'] : false
            );
            if (isset($data['dec_num'])) {
                $return[$data['section']][$name]['dec_num'] = $data['dec_num'];
            }
        }
        return $return;
    }

    // }}}
    // GenericAddEdit::guessReturnURL() {{{

    /**
     * Retourne l'URL de retour
     *
     * @access protected
     * @return string
     */
    protected function guessReturnURL() {
        if (isset($_REQUEST['retURL'])) {
            $url = html_entity_decode($_REQUEST['retURL']);
        } else if ($this->returnURL != false) {
            $url = $this->returnURL;
        } else if (in_array(GenericController::FEATURE_GRID, $this->features)) {
            $url = $_SERVER['SCRIPT_NAME'] . '?entity=' . $this->clsname
                . '&altname=' . $this->altname;
        } else {
            // ??
            $url = 'home.php';
        }
        return $url;
    }

    // }}}
    // GenericAddEdit::insertBlankElement() {{{

    /**
     * Insère un element "separateur" à la position $position.
     *
     * @access public
     * @param  int $position
     * @return void
     */
    protected function insertBlankElement($position=0) {
        $this->_blankElements["$position"] = true;
    }

    // }}}
    // GenericAddEdit::_createElement() {{{

    /**
     * Retourne un objet HTML_QuickForm_Element construit avec les paramètres
     * passés.
     *
     * @access private
     * @param string $name le nom de l'attribut
     * @param string $ename le nom de l'élément de formulaire correspondant
     * @param string $type le type de l'attribut
     * @param string $label le label de l'élément de formulaire
     * @param array $opts un tableau d'options pour l'élément de formulaire
     * @param bool $req determine si l'élément est requis
     * @param bool $ipe determine si l'élément fkey doit être édité dans le form
     * @param bool $aeButton determine si l'élément fkey doit utiliser un bouton addedit
     * @return object HTML_QuickForm_Element
     */
    private function _createElement($name, $ename, $type, $label, $opts, $req, $ipe, $aeButton) {
        $ret = array();
        $opts['id'] = $ename;
        $labelWithSemiColon = $label . ': ';
        if ($type == Object::TYPE_FKEY) {
            // type fkey: on construit un select
            $objPropertiesArray = $this->object->getProperties();
            $className = $objPropertiesArray[$name];
            require_once(MODELS_DIR . '/' . $className . '.php');
            $tmpobject = new $className();
            $getter = 'get'.$name.'Id';
            $objID = $this->object->$getter();
            // denormalized forms
            if ($ipe) {
                $customFileName = CUSTOM_CONTROLLER_DIR . '/' . $className . 'AddEdit.php';
                if (file_exists(PROJECT_ROOT . '/' . LIB_DIR . '/' . $customFileName)) {
                    require_once($customFileName);
                    $class = $className . 'AddEdit';
                } else {
                    $class = 'GenericAddEdit';
                }
                $this->innerForm = new $class(array(
                    'clsname' => $className,
                    'id' => $objID,
                    'return_url' => $this->guessReturnURL(),
                    'profiles' => $this->profiles,
                ));
                $this->innerForm->parentForm = $this;
                $this->innerForm->initialize();
                $this->innerForm->render();
                $mapping = array_keys($tmpobject->getMapping());
                $this->form->addElement('hidden', $ename.'_ID', $objID);
                $ret[] = HTML_QuickForm::createElement('header', $ename, $label);
                foreach($this->innerForm->formDefaults as $k=>$v) {
                    $elt = $this->innerForm->form->getElement($k . '_Group');
                    if ($elt instanceof HTML_QuickForm_Element) {
                        $ret[] = $elt;
                    }
                    $elt = $this->innerForm->form->getElement($k);
                    if ($elt instanceof HTML_QuickForm_Element) {
                        $ret[] = $elt;
                    }
                    $this->formDefaults[$k] = $v;
                }
            } else {
                $toStringAttribute = call_user_func(
                    array($tmpobject, 'getToStringAttribute'));
                $sortOrderField = is_array($toStringAttribute)?
                    $toStringAttribute[0]:$toStringAttribute;
                $fGetter = 'getFilterFor'.$name;
                $filter = method_exists($this, $fGetter) ? $this->$fGetter() : array();
                $arr = SearchTools::createArrayIDFromCollection(
                    $className,
                    $filter,
                    $req ? '' : MSG_SELECT_AN_ELEMENT,
                    'toString',
                    array($sortOrderField=>SORT_ASC)
                );
                $ename  .= '_ID';
                $opts['id'] = $ename;
                if ($aeButton && $this->action != self::FEATURE_VIEW) {
                    $opts['class'] = 'select_with_add_button';
                    $ret[] = HTML_QuickForm::createElement('select', $ename,
                        $labelWithSemiColon, $arr, $opts);
                    $retURL = urlencode(sprintf(
                        '%s?action=%s&entity=%s&objID=%d',
                        $_SERVER['PHP_SELF'], $this->action, $this->altname, $this->objID
                    ));
                    $url = sprintf(
                        'dispatcher.php?entity=%s&action=add&retURL=%s&fromEntity=%s',
                        $className, $retURL, $this->clsname.':'.$name
                    );
                    $ret[] = HTML_QuickForm::createElement(
                        'button',
                        'addButton' . $name,
                        A_ADD,
                        'id="addButton' . $name . '" class="button" onclick="'
                        . 'this.form.redirectURL.value=\'' . $url . '\';' 
                        . 'this.form.fromAddButton.value=1;this.form.submit();"'
                    );
                } else {
                    $opts['class'] = 'select';
                    $ret[] = HTML_QuickForm::createElement('select', $ename,
                        $labelWithSemiColon, $arr, $opts);
                }
            }
        } else if ($type == Object::TYPE_CONST) {
            // type constante: on construit un select aussi
            $method = sprintf('get%sConstArray', $name);
            $arr = call_user_func(array($this->clsname, $method));
            $opts['class'] = 'select';
            $ret[] = HTML_QuickForm::createElement('select', $ename,
                $labelWithSemiColon, $arr, $opts);
        } else if (in_array($type, array(Object::TYPE_DATE, Object::TYPE_DATETIME))) {
            // type date ou datetime: un calendrier
            require_once 'HTML/QuickForm/jscalendar.php';

            $options = array(
                'baseURL' => 'js/jscalendar/',
                'styleCss' => JSCALENDAR_DEFAULT_CSS,
                'language' => I18N::getLocaleCode(true),
                /*'image' => array(
                    'src' => JSCALENDAR_DEFAULT_PICTO,
                    'border' => 0),*/
                'setup' => array(
                    'inputField' => $ename,
                    // Le trigger est un bouton par defaut
                    'button' => $ename . '_calendar_trigger',
                    'displayArea' => 'displayed' . $ename,
                    // ajoute l'heure au format de date défini dans le fichier
                    // de language si type datetime
                    'ifFormat' => '%Y-%m-%d',
                    'daFormat' => '\'+Calendar._TT["DEF_DATE_FORMAT"]+\'' .
                        ($type==Object::TYPE_DATETIME?' %H:%M':''),
                    'showsTime' => $type==Object::TYPE_DATETIME?true:false,
                    'showOthers' => true));
            $attributes = array(
                'readonly' => 'readonly',
                'class' => 'ReadOnlyField',
                'rows' => 1, 'style' => 'width:30%;height:14px;',
                'id' => 'displayed' . $ename,);

            $ret[] = HTML_QuickForm::createElement('hidden', $ename,
                null, array('id' => $ename));
            // vu que jscalendar utilise innerhtml, on ne peut mettre ici un input text
            // Du coup, ce 'bidouillage' avec un textarea
            $ret[] = HTML_QuickForm::createElement('textarea', 'displayed'.$ename, null,
                $attributes);
            /*$ret[] = HTML_QuickForm::createElement('static', 'displayed'.$ename, null,
                '<span id="displayed'. $ename . '" name="displayed'. $ename . '" style="border: 1px dotted rgb(0, 0, 0); margin: 3px; padding: 3px; background-color: rgb(225, 232, 239); text-align: right;">toto</span>' );*/
            $ret[] = HTML_QuickForm::createElement('button', $ename.'_calendar_trigger',
                '...', array(
                    'title' => _('Select a date'),
                    'id' => $ename . '_calendar_trigger',
                    'class' => 'button')
            );
            $ret[] = HTML_QuickForm::createElement('jscalendar',
                $ename.'_calendar', null, $options);
        } else if ($type == Object::TYPE_TIME) {
            $fmt = array(
                'language' => I18N::getLocaleCode(true),
                'format'   => 'H:i'
            );
            $opts['class'] = 'datetime';
            $ret[] = HTML_QuickForm::createElement('date', $ename,
                $labelWithSemiColon, $fmt, $opts);
        } else if (in_array($type, 
            array(Object::TYPE_TEXT, Object::TYPE_I18N_TEXT, Object::TYPE_LONGTEXT)))
        {
            // type texte ou longtext: un textarea
            $opts['rows'] = 10;
            $opts['cols'] = 40;
            $opts['class'] = 'textarea';
            $ret[] = HTML_QuickForm::createElement('textarea', $ename,
                $labelWithSemiColon, $opts);
        } else if ($type == Object::TYPE_BOOL) {
            $opts['id'] = $ename . '_0';
            $ret[] = HTML_QuickForm::createElement('radio', $ename,
                $labelWithSemiColon, A_YES, 1, $opts);
            $opts['id'] = $ename . '_1';
            $ret[] = HTML_QuickForm::createElement('radio', $ename,
                $labelWithSemiColon, A_NO, 0, $opts);
        } else if ($type == Object::TYPE_PASSWORD) {
            $opts['value'] = "this.value='" . self::PW_NOCHG . "';";
            $ret[] = HTML_QuickForm::createElement('password', $ename,
                $labelWithSemiColon, $opts);
            $opts['id'] = $ename . '_Again';
            $label = $label . ' ('. _('confirm') . ')';
            $ret[] = HTML_QuickForm::createElement('password', $opts['id'],
                $labelWithSemiColon, $opts);
        } elseif ($type == Object::TYPE_MANYTOMANY) {
            require_once('HTML/QuickForm/advmultiselect.php');
            // type many2many: on construit deux selects avec advmultiselect
            $objPropertiesArray = $this->object->getProperties();
            $className = $this->links[$name]['linkClass'];//objPropertiesArray[$name];
            require_once(MODELS_DIR . '/' . $className . '.php');
            $tmpobject = new $className();
            $toStringAttribute = call_user_func(
                array($tmpobject, 'getToStringAttribute'));
            $sortOrderField = is_array($toStringAttribute)?
                $toStringAttribute[0]:$toStringAttribute;
            $toString = is_array($toStringAttribute)?'toString':
                $toStringAttribute;
            $filterGetter = 'getFilterFor'.$name;
            if (method_exists($this, $filterGetter)) {
                $filter = $this->$filterGetter();
            } else {
                $filter = array();
            }
            $arr = SearchTools::createArrayIDFromCollection(
                $className,
                $filter,
                '',
                $toString,
                array($sortOrderField=>SORT_ASC)
            );
            $opts['size'] = 8;
            $opts['style'] = 'width:100%;';
            $ret[] = HTML_QuickForm::createElement('advmultiselect', $ename,
                array($label, $label, ''),
                $arr, $opts);
        } elseif(in_array($type, array(Object::TYPE_FILE, Object::TYPE_IMAGE, Object::TYPE_FILE_UPLOAD))) {
            $opts['class'] = 'textfield';
            $ret[] = HTML_QuickForm::createElement('file', $ename,
                $labelWithSemiColon, $opts);
        } elseif($type == Object::TYPE_HTML || $type == Object::TYPE_I18N_HTML) {
            // type html: un editeur wysiwyg
            // lib: HTML_QuickForm_Element_tinymce
            require_once 'HTML/QuickForm/tinymce.php';
            $options = array(
                'baseURL' => 'js/tinymce/jscripts/tiny_mce/',
                'configFile' => 'js/tinymce.inc.js');

            $opts['class'] = 'mceEditor';
            $ret[] = HTML_QuickForm::createElement('tinymce', $ename,
                $labelWithSemiColon, $options, $opts);
        } elseif($type == Object::TYPE_HEXACOLOR) {
            // type hexacolor: un colorpicker
            $this->addJSRequirements('js/colorpicker.js');
            require_once 'HTML/QuickForm/colorpicker.php';
            $ret[] = HTML_QuickForm::createElement('colorpicker', $ename,
                $labelWithSemiColon, array(), $opts);
        } else {
            // les autres types sont des textfield
            $opts['class'] = 'textfield';
            $ret[] = HTML_QuickForm::createElement('text', $ename,
                $labelWithSemiColon, $opts);
        }
        if (!$ipe) {
            foreach ($ret as $elt) {
                if ($req) {
                    $elt->setAttribute('class', $elt->getAttribute('class') . ' required_element');
                }
            }
        }
        return $ret;
    }

    // }}}
}

?>
