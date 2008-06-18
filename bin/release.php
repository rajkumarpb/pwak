<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK the PHP Web Application Kit.
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
 * @version   SVN: $Id: release.php,v 1.2 2008-05-15 11:18:49 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

#!/usr/bin/env php
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * $Source: /home/cvs/framework/bin/release.php,v $
 * Release management script.
 *
 * @version   CVS: $Id: release.php,v 1.2 2008-05-15 11:18:49 david Exp $
 * @author    Guillaume <guil@ateor.com>
 * @copyright 2002-2007 ATEOR - All rights reserved
 */

define('PROJECT_ROOT', '.');
define('FRAMEWORK_ROOT', '.');
require_once 'framework.inc.php';
require_once 'OptionParser/OptionParser.php';

$frameworkSourcePath = substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), '/bin'));

$optionParser = new OptionParser();
$optionParser->description = 'Framework releaser';

$create_doc_cmd = $optionParser->addCommand('create_doc');
$create_doc_cmd->description = 'Generate documentation';

$create_pear_pkg_cmd = $optionParser->addCommand('create_pear_pkg');
$create_pear_pkg_cmd->description = 'Generate the Pear package.xml file';
$create_pear_pkg_cmd->addArgument('version');
$create_pear_pkg_cmd->addArgument('stability');
$create_pear_pkg_cmd->addOption('make', array(
    'shortname'   => '-m',
    'longname'    => '--make',
    'action'      => optionParser::ACTION_STORE_TRUE,
    'description' => 'to write the package'));

try {
    $parserResult = $optionParser->parse();
} catch(Exception $exc) {
    $optionParser->displayError($exc->getMessage());
}

switch($parserResult->command_name) {
    case 'create_doc';
        system('phpdoc -c ' . $frameworkSourcePath . '/doc/phpDocumentor.ini');

        $source = $frameworkSourcePath . '/doc/rst/quickstart.txt';
        $destination = $frameworkSourcePath . '/doc/quickstart.html';
        system('rst2html ' . $source . ' ' . $destination);
    break;

    case 'create_pear_pkg';
        try {
            $version   = $parserResult->command->args['version'];
            $stability = $parserResult->command->args['stability'];
            $make      = $parserResult->command->options['make'];
            
            require_once 'PEAR/PackageFileManager2.php';
            $pfm = new PEAR_PackageFileManager2();

            $pfm->setOptions(array(
                'baseinstalldir' => FRAMEWORK_NAME,
                'packagedirectory' => $frameworkSourcePath,
                'exceptions' => array('bin/pear-framework' => 'script'),
                'installexceptions' => array('bin/pear-framework' => '/'),
                'dir_roles' => array(
                    'doc' => 'doc', // to include css and js files
                    'lib/Frontend/templates' => 'php'),
                'ignore' => array('CVS/', 'TODO')
            ));

            $pfm->setPackage(FRAMEWORK_NAME);
            $pfm->setPackageType('php');
            $pfm->setChannel('pear.php.net');
            $pfm->setAPIVersion('2.0');
            $pfm->setAPIStability('stable');
            $pfm->setReleaseVersion($version);
            $pfm->setReleaseStability($stability);
            $pfm->setSummary('Php Web Application Kit');
            $pfm->setDescription('Php Ateor\'s framework');
            $pfm->setNotes('see changelog');
            $pfm->setPhpDep('5.1.2');
            $pfm->setPearInstallerDep('1.5.4');
            $pfm->addMaintainer('lead', 'lead', 'ateor team', 'dev@ateor.com');
            $pfm->setLicense('MIT License', 'http://opensource.org/license/mit-license.php');

            // to replace in the files with the correct values
            $pfm->addReplacement('bin/pear-framework', 'pear-config', '@PHP_BIN@', 'php_bin');
            $pfm->addReplacement('bin/pear-framework', 'pear-config', '@PHP_DIR@', 'php_dir');
            $pfm->addReplacement('lib/Frontend/Frontend.php', 'pear-config', '@DATA_DIR@', 'data_dir');

            $pfm->generateContents();

            // to change the file name
            $pfm->addInstallAs('bin/pear-framework', FRAMEWORK_NAME);

            if($make !== false) {
                $pfm->writePackageFile();
            } else {
                $pfm->debugPackageFile();
            }
        } catch (Exception $exc) {
            $optionParser->displayError($exc->getMessage());
        }
    break;

    default:
        $optionParser->displayUsage();
}
?>
