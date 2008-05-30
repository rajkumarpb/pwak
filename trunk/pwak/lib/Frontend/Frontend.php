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
 * @version   SVN: $Id: Frontend.php,v 1.14 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class Frontend
{
    // properties {{{

    /**
     * The command line parser
     *
     * @var Object parser
     * @access public
     */
    protected $parser;

    /**
     * The command line parser result array
     *
     * @var array
     * @access public
     */
    protected $parserResult;

    // }}}
    // constants {{{

    /**
     * Mode of the created directories.
     *
     * @constant int MODE
     */
    const MODE = 0755;

    // }}}
    // Constructeur {{{

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {

    }

    // }}}
    // Frontend::createProject() {{{

    /**
     * Create the project layout and copy required files.
     *
     * @access public
     * @return boolean
     * @throw  Exception
     */
    public static function createProject($project_path, $quiet=false) {
        if (!$quiet) {
            self::output(sprintf('Creating new project in "%s"', $project_path)); 
        }
        if (is_dir($project_path)) {
            throw new Exception(sprintf(
                'directory "%s" already exists, please choose another name or remove it first',
                $project_path
            ));
        }
        // create layout
        $skel = '@DATA_DIR@' . DIRECTORY_SEPARATOR . FRAMEWORK_NAME . 
            DIRECTORY_SEPARATOR . 'data';
        if(!is_dir($skel)) {
            $skel = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' 
                . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data';
        } 
        return self::copyDir($skel, $project_path, false, array('CVS'));
    }

    // }}}
    // Frontend::updateProject() {{{

    /**
     * Update the project layout and required files.
     *
     * @access public
     * @return boolean
     * @throw  Exception
     */
    public static function updateProject($project_path, $quiet=false) {
        if (!$quiet) {
            self::output(sprintf('Updating project "%s"', $project_path)); 
        }
        if (!is_dir($project_path)) {
            throw new Exception(
                'project does not exists, please run the "create_project" command first'
            );
        }
        // update project
        // skip files that can be modified by the user
        $skip = array('CVS', '.cvsignore', 'project.xml', 'menu.xml',
            'project.conf', 'config.inc.php');
        $skel = '@DATA_DIR@' . DIRECTORY_SEPARATOR . FRAMEWORK_NAME . 
            DIRECTORY_SEPARATOR . 'data';
        if(!is_dir($skel)) {
            $skel = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' 
                . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data';
        } 
        return self::copyDir($skel, $project_path, true, $skip);
    }

    // }}}
    // Frontend::handleCommandLine() {{{

    /**
     * Handle the command line user input.
     *
     * @access public
     * @return boolean
     * @throw  Exception
     */
    public function handleCommandLine() {
        require_once 'Console/CommandLine.php';
        $this->parser = new Console_CommandLine(array(
            'description' => 'Framework frontend',
            'version'     => '0.1.0'
        ));
        // general options
        $this->parser->addOption(
            'quiet',
            array(
                'short_name'  => '-q',
                'long_name'   => '--quiet',
                'action'      => 'StoreTrue',
                'description' => 'be quiet when executing commands'
            )
        );
        $this->parser->addOption(
            'batch',
            array(
                'short_name'  => '-b',
                'long_name'   => '--batch',
                'action'      => 'StoreTrue',
                'description' => 'batch mode, disable user interaction'
            )
        );

        // commands
        $create_project_cmd = $this->parser->addCommand('create_project');
        $create_project_cmd->description = 'Create a new project';
        $create_project_cmd->addArgument('project_path');

        $update_project_cmd = $this->parser->addCommand('update_project');
        $update_project_cmd->description = 'Update an existing project';
        $update_project_cmd->addArgument('project_path');

        $gen_sql_cmd = $this->parser->addCommand('gen_sql');
        $gen_sql_cmd->description = 'Generate the project sql code';
        $gen_sql_cmd->addArgument('project_path');
        $gen_sql_cmd->addOption(
            'fake',
            array(
                'short_name'  => '-f',
                'long_name'   => '--fake',
                'action'      => 'StoreTrue',
                'description' => 'debug mode, nothing is write on the disk'
            )
        );

        $gen_models_cmd = $this->parser->addCommand('gen_models');
        $gen_models_cmd->description = 'Generate the project models';
        $gen_models_cmd->addArgument('project_path');
        $gen_models_cmd->addOption(
            'fake',
            array(
                'short_name'  => '-f',
                'long_name'   => '--fake',
                'action'      => 'StoreTrue',
                'description' => 'debug mode, nothing is write on the disk'
            )
        );
        $gen_models_cmd->addOption(
            'entities',
            array(
                'short_name'  => '-e',
                'long_name'   => '--entities',
                'action'      => 'StoreArray',
                'description' => 'list of the entities to generate'
            )
        );
        //$gen_models_cmd->addArgument('entities', true);

        $gen_xml_cmd = $this->parser->addCommand('gen_xml');
        $gen_xml_cmd->description = 'Generate the project xml from an xmi file';
        $gen_xml_cmd->addArgument('project_path');
        $gen_xml_cmd->addArgument('xmi_file');

        try {
            $this->parserResult = $this->parser->parse();
        } catch (Exception $exc) {
            $this->parser->displayError($exc->getMessage());
        }
        switch($this->parserResult->command_name) {
            case 'create_project':
            case 'update_project':
                $method = $this->parserResult->command_name=='create_project' ?
                    'createProject' : 'updateProject';
                try {
                    self::$method(
                        $this->parserResult->command->args['project_path'],
                        $this->parserResult->options['quiet']
                    );
                } catch (Exception $exc) {
                    $this->parser->displayError($exc->getMessage());
                }
                break;
            case 'gen_sql':
            case 'gen_models':
                require_once 'lib/Frontend/CodeGenerator.php';
                $method = $this->parserResult->command_name=='gen_sql' ?
                    'generateSQL' : 'generateModels';
                $codegen = new Codegenerator(
                    $this->parserResult->command->args['project_path'],
                    $this->parserResult->command->options['entities']
                );
                try {
                    $codegen->$method($this->parserResult->command->options['fake']);
                } catch (Exception $exc) {
                    $this->parser->displayError($exc->getMessage());
                }
                break;
            case 'gen_xml':
                require_once 'lib/Frontend/xmi2xml.php';
                $gen = new ArgoUmlXmi2Xml(
                    $this->parserResult->command->args['xmi_file'],
                    $this->parserResult->command->args['project_path']
                );
                $gen->render($this->parserResult->command->args['project_path'] . '/config/xml/_project.xml');
                break;
            default:
                $this->parser->displayUsage();
        }

    }

    // }}}
    // Frontend::input() {{{
    
    /**
     * Grab user input.
     *
     * Description of the $params array:
     *
     * array(
     *   'answers', // array of possible answers (answer=>return value)
     *   'notnull', // bool, true if the answer cannot be null (default)
     *   'default'  // default value to return if the user only press the 
     *              // <return> key (ignored if notnull is true)
     * )
     *
     * @access protected
     * @param  string $msg
     * @param  array  $params
     * @return mixed
     */
    protected function input($msg, $params=array()) {
        $answers = isset($params['answers'])?$params['answers']:array();
        $notnull = isset($params['notnull'])?$params['notnull']:true;
        $default = isset($params['default'])?$params['default']:null;
        fwrite(STDOUT, $msg);
        while (!isset($answers[($selection = trim(fgets(STDIN)))])) {
            if ($selection == '' && $default !== null) {
                return $default;
            }
            if (empty($answers) && !($notnull && $selection == '')) {
                return $selection;
            }
            fwrite(STDOUT, $msg);
        }
        return $answers[$selection];
    }

    // }}}
    // Frontend::output() {{{

    /**
     * Output a message to STDOUT (default) or STDERR
     *
     * @access protected
     * @param  string $msg
     * @param  ressource $fd
     * @return mixed
     */
    protected function output($msg, $fd = STDOUT) {
        fwrite($fd, $msg . "\n");
    }
    // }}}
    // Frontend::copyDir() {{{

    /**
     * static function that copy a whole directory to another.
     *
     * @param string $source
     * @param string $dest
     * @param bool $overwrite
     * @param array $exclude
     * @return boolean
     * @throw Exception
     */
    protected static function copyDir($source, $dest, $overwrite=false,
        $exclude=array()) 
    {
        if (false === ($handle = opendir($source))) {
            throw new Exception(
                sprintf('cannot open source directory "%s"', $source)
            );
        }
        if (!is_dir($dest) && !@mkdir($dest)) {
            throw new Exception(
                sprintf('Cannot create destination directory "%s"', $dest)
            );
        }
        while (false !== ($file = readdir($handle))) {
            if($file != '.' && $file != '..' && !in_array($file, $exclude)) {
                $path = $source . DIRECTORY_SEPARATOR . $file;
                $destpath = $dest . DIRECTORY_SEPARATOR . $file;
                if(is_file($path) && (!is_file($destpath) || $overwrite)) {
                    printf("Copying \"%s\" to \"%s\"...\n", $path, $destpath);
                    if(!@copy($path, $destpath)) {
                        throw new Exception(
                            sprintf('Failed to copy "%s" to "%s"', $path, $destpath)
                        );
                    }
                } else if (is_dir($path)) {
                    if (!is_dir($destpath) && !@mkdir($destpath, self::MODE)) {
                        throw new Exception(
                            sprintf('Failed to create directory "%s"', $destpath)
                        );
                    }
                    self::copyDir($path, $destpath, $overwrite, $exclude);
                }
            }
        }
        closedir($handle);
        return true;
    }
        
    // }}}
    // Frontend::cleanPath() {{{
    
    /**
     * cleanPath 
     *
     * remove the "/" at the end of the path
     * 
     * @param string $path 
     * @static
     * @access public
     * @return string
     */
    public static function cleanPath($path) {
        if(strrpos($path, '/') == strlen($path)-1) {
            return substr($path, 0, strlen($path)-1);
        }
        return $path;
    }
    
    // }}}
}

?>
