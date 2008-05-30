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
 * @version   SVN: $Id: Template.php,v 1.5 2008-05-30 09:23:48 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once('smarty/libs/Smarty.class.php');
require_once('lib/Template/SmartyAddons.php');

/**
 * Template
 *
 * Classe de gestion des templates. Pour la customisation des templates on peut
 * définir un certain normbre de constantes (cf. plus haut), les templates
 * customs doivent veiller à garder les mêmes variables de base que les
 * templates par défaut du framework.
 * Cette classe peut s'utiliser de manière statique ou classique, voici deux
 * exemples détaillant l'utilisation:
 *
 * Exemples {{{
 *
 * Le hello world de rigueur:
 * =========================
 * <code>
 * Template::page('Hello World!', 'Hello this is my first page...');
 * </code>
 *
 * Construction d'une page via un sous-template, et display de la page:
 * ===================================================================
 * <code>
 * $tpl = new Template();
 * $tpl->assign('foo', 'Foo !');
 * $tpl->assign('bar', 'Bar !');
 * $content = $tpl->fetch('MySubTemplate.html');
 * Template::page('My title', $content);
 * </code>
 *
 * Construction d'une page ajax avec inclusion de js supplémentaire:
 * ================================================================
 * <code>
 * $tpl = new Template();
 * $tpl->assign('foo', 'Foo ajax enabled !');
 * $tpl->assign('bar', 'Bar ajax enabled !');
 * $content = $tpl->fetch('MySubTemplate.html');
 * Template::ajaxPage('My title', $content, array('my_ajax_custom.js'));
 * </code>
 *
 * Exemples de dialogues:
 * =====================
 * <code>
 * // Un dialogue d'information
 * Template::infoDialog('Ceci est un message d\'information', 'back.php');
 * // Un dialogue d'erreur
 * Template::errorDialog('Une erreur 0xx23452x est survenue...', 'bsod.php');
 * // Un dialogue de confirmation
 * Template::confirmDialog('Continuer ?', 'ok.php', 'cancel.php');
 * // Une question (oui, non, annuler)
 * Template::questionDialog('Avez-vous un chat ?', 'cats.php', 'dogs.php',
 *     'rabbits.php');
 * </code>
 *
 * }}}
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 * @subpackage Template
 */
class Template
{
    // constantes {{{

    const ERROR_IMAGE    = 'images/dialog_error.png';
    const INFO_IMAGE     = 'images/dialog_information.png';
    const QUESTION_IMAGE = 'images/dialog_question.png';

    // }}}
    // propriétés {{{

    /**
     * Tableau de fonctions callable appelées avant le render du template
     * corrspondant à la cléf.
     * Cette propriété publique permet de faire des taches qui doivent être
     * faites sur toutes les pages (comme l'assignation de variables etc...).
     *
     * Format du tableau:
     * array(
     *     'MyTemplate.html'      => 'myFunction',
     *     'MyOtherTemplate.html' => 'anotherfunc',
     *     ...
     * );
     *
     * @static
     * @var    array $prebuildFunctions
     * @access protected
     */
    public static $prebuildFunctions = array();

    /**
     * Instance statique du moteur de template
     *
     * @static
     * @var    engine
     * @access protected
     */
    protected static $engine = false;

    // }}}
    // Template::__construct() {{{

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        // initialize le moteur de templates
        Timer::start('Template');
        self::engine();
    }
    // }}}
    // Template::page() {{{

    /**
     * Affiche une page simple avec comme titre $title et comme corps $body.
     * Il est possible de spécifier des fichiers js et css à inclure dans
     * l'entête html via les 2 paramètres optionnels $js et $css. Pour les css,
     * le format du tableau est:
     * array(
     *     'screen' => array('macss1.css', 'macss2.css'),
     *     'print'  => array('macss_printonly.css')
     * )
     *
     * <code>
     * Template::page(
     *     'My title',
     *     'Hello world!',
     *     array('path/my_jsfile1.js', 'path/my_jsfile2.js'),
     *     array('screen'=>array('path/my_css.css'))
     * );
     * </code>
     *
     * @static
     * @access public
     * @param  string $title
     * @param  string $body
     * @param  array  $js  un tableau de fichiers js
     * @param  array  $css un tableau de fichiers css ('media'=>'fichier')
     * @return void
     */
    public static function page($title = '', $body = '', $js = array(),
        $css = array(), $tpl = BASE_TEMPLATE)
    {
        Timer::start('Template');
        self::prebuild($tpl);
        if (empty($title)) {
            $nav = Navigation::singleton();
            $title = $nav->activeTitle;
        }
        $engine = self::engine();
        $engine->assign('PageTitle', $title);
        $engine->assign('PageContent', $body);
        $engine->assign('JavaScript', $js);
        $print_css  = array();
        $screen_css = array();
        foreach ($css as $media=>$files) {
            if ($media === 'print') {
                $print_css = array_merge($print_css, $files);
            } else {
                $screen_css = array_merge($screen_css, $files);
            }
        }
        $engine->assign('PrintCSS',  $print_css);
        $engine->assign('ScreenCSS', $screen_css);
        Timer::stop('Template');
        if (DEV_VERSION) {
            $engine->assign('TimerStats', Timer::render());
        }
        $engine->display($tpl);
    }

    // }}}
    // Template::ajaxPage() {{{

    /**
     * Affiche une page utilisant ajax/json.
     * (@see Template::page() pour la doc et l'exemple).
     *
     * @access public
     * @param  string $title
     * @param  string $body
     * @param  array $js  un tableau de fichiers js
     * @param  array $css un tableau de fichiers css ('media'=>'fichier')
     * @return void
     */
    public static function ajaxPage($title, $body = '', $js = array(),
        $css = array(), $tpl = BASE_TEMPLATE)
    {
        $ajaxClient = new AjaxClient();
        self::engine()->assign('AJAXJavascript', $ajaxClient->initialize());
        self::page($title, $body, $js, $css, $tpl);
    }

    // }}}
    // Template::pageWithGrid() {{{

    /**
     * Affiche une page avec un grid.
     *
     * <code>
     * Template::pageWithGrid(
     *     $gridInstance,
     *     'EntityName',
     *     'My title',
     *     array('Active'=>true),
     *     array('Name'=>SORT_ASC)
     * );
     * </code>
     *
     * @static
     * @access public
     * @param  object $grid
     * @param  string $clsname
     * @param  string $title
     * @param  array  $filter
     * @param  array  $order
     * @param  string $tpl
     * @return void
     */
    public static function pageWithGrid($grid, $clsname, $title = '',
        $filter = array(), $order = array(), $tpl = BASE_TEMPLATE)
    {
        $retURL = $tpl==BASE_POPUP_TEMPLATE?
            'javascript:window.close();':'javascript:history.go(-1);';
        $grid->setMapper($clsname);
        $result = $grid->execute($filter, $order);
        if (Tools::isException($result)) {
            self::errorDialog(
                E_ERROR_IN_EXEC . '<br/>' . $result->getMessage(),
                $retURL, $tpl
            );
            exit(1);
        }
        $action = UrlTools::compliantURL($_SERVER['REQUEST_URI']);
        self::page(
            $title,
            '<form id="'.$clsname.'Grid" action="'.$action.'" method="post">'
            . $result . '</form>',
            array(),
            array(),
            $tpl
        );
    }

    // }}}
    // Template::pdf() {{{

    /**
     * Crée et affiche un document pdf.
     *
     * Utilisation:
     * <code>
     * $smartyParams = array(
     *     'foo' => 'foosValue',
     *     'bar' => 'barsValue');
     * $outputOptions = array(
     *     'name' => 'myDocumentName',
     *     'dest' => 'I');
     * Template::pdf('path/to/the/template.xml',
     *     $smartyParams, $outputOptions);
     * </code>
     *
     * @param string $xmlTpl nom du template xml
     * @param array $params tableau de valeurs à passer au template
     * @param array $outputOpt options de sortie du pdf
     *      ('name'=>'docname', 'dest'=>'[I|D|F|S]')
     * @return void
     */
    public static function pdf($xmlTpl, $params=array(), $outputOpt=array()) {
        if(!isset($outputOpt['name'])) {
            $outputOpt['name'] = 'doc.pdf';
        }
        if(!isset($outputOpt['dest'])) {
            $outputOpt['dest'] = 'I';
        }
        require_once 'xml2pdf/Xml2Pdf.php';
        $template = new Template();
        foreach($params as $key=>$value) {
            $template->assign($key, $value);
        }
        $xml = $template->fetch($xmlTpl);
        $xml2pdf = new Xml2Pdf($xml);
        $pdf = $xml2pdf->render();
        $pdf->Output($outputOpt['name'], $outputOpt['dest']);
    }

    // }}}
    // Template::infoDialog() {{{

    /**
     * Affiche un dialogue d'info contenant le message $msg et un bouton ok
     * qui va rediriger l'utilisateur vers le lien $okLink. Si $okLink n'est
     * pas renseigné, c'est la page précédente qui sera réaffichée via js.
     *
     * <code>
     * Template::infoDialog('Opération effectuée...', 'index.php');
     * </code>
     *
     * @static
     * @access public
     * @param  string $msg
     * @param  string $okLink
     * @return void
     */
    public static function infoDialog($msg, $okLink=false,
        $parentTpl=BASE_TEMPLATE)
    {
        if (!$okLink) {
            $okLink = 'javascript:history.go(-1);';
        }
        $engine = self::engine();
        $engine->assign('Title', E_INFO_TITLE);
        $engine->assign('Message', $msg);
        $engine->assign('ImageSource', self::INFO_IMAGE);
        $engine->assign('Button1', array('label'=>A_OK, 'link'=>$okLink));
        $css = array();
        if (file_exists(CSS_DIR . '/dialog.css')) {
            $css['screen'] = array(CSS_DIR . '/dialog.css');
        }
        self::page(
            E_INFO_TITLE,
            $engine->fetch(DIALOG_TEMPLATE),
            array(),
            $css,
            $parentTpl
        );
    }

    // }}}
    // Template::errorDialog() {{{

    /**
     * Affiche un dialogue d'erreur contenant le message $msg et un bouton ok
     * qui va rediriger l'utilisateur vers le lien $okLink. Si $okLink n'est
     * pas renseigné, c'est la page précédente qui sera réaffichée via js.
     *
     * <code>
     * Template::errorDialog('Mon message d\'erreur ici...', 'index.php');
     * </code>
     *
     * @static
     * @access public
     * @param  string $msg
     * @param  string $okLink
     * @return void
     */
    public static function errorDialog($msg, $okLink=false,
        $parentTpl=BASE_TEMPLATE)
    {
        if (!$okLink) {
            $okLink = 'javascript:history.go(-1);';
        }
        $engine = self::engine();
        $engine->assign('Title', E_ERROR_TITLE);
        $engine->assign('Message', $msg);
        $engine->assign('ImageSource', self::ERROR_IMAGE);
        $engine->assign('Button1', array('label'=>A_OK, 'link'=>$okLink));
        $css = array();
        if (file_exists(CSS_DIR . '/dialog.css')) {
            $css['screen'] = array(CSS_DIR . '/dialog.css');
        }
        self::page(
            E_ERROR_TITLE,
            $engine->fetch(DIALOG_TEMPLATE),
            array(),
            $css,
            $parentTpl
        );
    }

    // }}}
    // Template::confirmDialog() {{{

    /**
     * Affiche un dialogue de confirmation contenant le message $msg et 2
     * boutons qui vont rediriger l'utilisateur vers le lien $okLink si
     * l'utilisateur confirme (clique sur ok), ou $cancelLink s'il préfère
     * annuler l'opération. Si $cancelLink n'est pas renseigné, c'est la page
     * précédente qui sera réaffichée via js.
     *
     * <code>
     * Template::confirmDialog('Supprimer les éléments ?', 'ok.php', 'cancel.php');
     * </code>
     *
     * @static
     * @access public
     * @param  string $msg
     * @param  string $okLink
     * @param  string $cancelLink
     * @return void
     */
    public static function confirmDialog($msg, $okLink, $cancelLink=false,
        $parentTpl=BASE_TEMPLATE)
    {
        if (!$cancelLink) {
            $cancelLink = 'javascript:history.go(-1);';
        }
        $engine = self::engine();
        $engine->assign('Title', E_CONFIRMATION_TITLE);
        $engine->assign('Message', $msg);
        $engine->assign('ImageSource', self::QUESTION_IMAGE);
        $engine->assign('Button1', array('label'=>A_OK, 'link'=>$okLink));
        $engine->assign('Button2',
            array('label'=>A_CANCEL, 'link'=>$cancelLink));
        $css = array();
        if (file_exists(CSS_DIR . '/dialog.css')) {
            $css['screen'] = array(CSS_DIR . '/dialog.css');
        }
        self::page(
            E_CONFIRMATION_TITLE,
            $engine->fetch(DIALOG_TEMPLATE),
            array(),
            $css,
            $parentTpl
        );
    }

    // }}}
    // Template::questionDialog() {{{

    /**
     * Affiche un dialogue "question" contenant le message $msg et 2 ou 3
     * boutons (Oui, Non, [Annuler]) qui vont rediriger l'utilisateur vers le
     * lien $yesLink s'il répond oui, $noLink s'il répond non et enfin
     * $cancelLink s'il préfère répondre Annuler.
     *
     * <code>
     * Template::questionDialog('Continuer ?', 'yes.php', 'no.php', 'index.php');
     * </code>
     *
     * @static
     * @access public
     * @param  string $message
     * @param  string $yesLink
     * @param  string $noLink
     * @param  string $cancelLink
     * @return void
     */
    public static function questionDialog($msg, $yesLink, $noLink,
        $cancelLink=false, $parentTpl=BASE_TEMPLATE)
    {
        $engine = self::engine();
        $engine->assign('Title', E_QUESTION_TITLE);
        $engine->assign('Message', $msg);
        $engine->assign('ImageSource', self::QUESTION_IMAGE);
        $engine->assign('Button1', array('label'=>A_YES, 'link'=>$yesLink));
        $engine->assign('Button2', array('label'=>A_NO, 'link'=>$noLink));
        if ($cancelLink) {
            $engine->assign('Button3',
                array('label'=>A_CANCEL, 'link'=>$cancelLink));
        }
        $css = array();
        if (file_exists(CSS_DIR . '/dialog.css')) {
            $css['screen'] = array(CSS_DIR . '/dialog.css');
        }
        self::page(
            E_QUESTION_TITLE,
            $engine->fetch(DIALOG_TEMPLATE),
            array(),
            $css,
            $parentTpl
        );
    }

    // }}}
    // Template::engine() {{{

    /**
     * Initialize (if not done) and return the template engine.
     *
     * @static
     * @access protected
     * @return object
     */
    protected static function engine() {
        if (!self::$engine) {
            self::$engine = new Smarty();
            self::$engine->compile_dir = SMARTY_COMPILE_DIR;
            if (!file_exists(self::$engine->compile_dir)) {
                mkdir(self::$engine->compile_dir, 0777);
            }
            self::$engine->force_compile = true;
            self::$engine->caching = false;
            self::$engine->use_sub_dirs = false;
            self::$engine->register_block('t', 'gettextize');
        }
        return self::$engine;
    }
    // }}}
    // Template::prebuild() {{{

    /**
     * Construit le template avant affichage, cette méthode peut-être
     * surchargée pour par exemple assigner des variables qui doivent être
     * affichées sur toutes les pages.
     *
     * @static
     * @access protected
     * @return void
     */
    protected static function prebuild($tpl)
    {
        $engine = self::engine();
        if (isset(self::$prebuildFunctions[$tpl]) &&
            is_callable(self::$prebuildFunctions[$tpl])) {
            $func = self::$prebuildFunctions[$tpl];
            $func($engine);
        }
    }
    // }}}
    // Template::__call() {{{

    /**
     * Intercepteur de méthodes qui sert à faire "proxy" avec le moteur de
     * template.
     *
     * @access public
     * @param  string $name
     * @param  mixed  $args
     * @return mixed
     */
    function __call($name, $args) {
        $engine = self::engine();
        if (method_exists($engine, $name)) {
            return call_user_func_array(array($engine, $name), $args);
        }
        trigger_error(sprintf('Call to undefined method %s::%s',
            __CLASS__, $name), E_USER_ERROR);
    }

    // }}}
    // Template::__get() {{{

    /**
     * Appelé quand on essaie de récupérer la valeur d'une propriété, fait
     * office de proxy avec l'instance du moteur de template.
     *
     * @access public
     * @param  string $name
     * @return mixed
     */
    private function __get($name) {
        $engine = self::engine();
        if (property_exists($engine, $name)) {
            return $engine->$name;
        }
        trigger_error(sprintf('Undefined property %s::%s', __CLASS__, $name),
            E_USER_NOTICE);
    }

    // }}}
    // Template::__set() {{{

    /**
     * Appelé quand on essaie d'attribuer une valeur à une propriété, fait
     * office de proxy avec l'instance du moteur de template.
     *
     * @access public
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    private function __set($name , $value) {
        $engine = self::engine();
        if (property_exists($engine, $name)) {
            $engine->$name = $value;
            return;
        }
        trigger_error(sprintf('Undefined property %s::%s', __CLASS__, $name),
            E_USER_NOTICE);
    }

    // }}}
}

?>
