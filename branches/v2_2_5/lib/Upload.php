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

if (!defined('DB_UPLOAD_TABLE')) {
    define('DB_UPLOAD_TABLE', 'FW_UploadedFiles');
}
if (!defined('DB_UPLOAD_AS_BASE64')) {
    define('DB_UPLOAD_AS_BASE64', true);
}
// }}}
// constantes des messages d'erreur {{{
/**
 * erreurs utilisateur
 */
define('E_UPLOAD_MAX_SIZE', _('"%s" file size exceeds maximum allowed size (%s octets).'));
define('E_UPLOAD_NO_FILE', _('Please select a file to upload.'));
define('E_UPLOAD_PARTIAL', _('"%s" file was uploaded but only partially.'));
define('E_UPLOAD_UNSUPPORTED_EXTENSION', _('"%s" file format is unsupported. Supported formats are %s.'));
/**
 * erreurs de programmation
 */
define('E_UPLOAD_NO_FILE_INPUT', _('Field "%s" does not exists.'));
define('E_UPLOAD_FORM_NOT_POSTED', _('Form is not submitted yet.'));
define('E_UPLOAD_TMP_DIR_MISSING', _('Missing upload temporary directory.'));
define('E_UPLOAD_DIR_WRITE', _('Insufficient permissions to write to "%s" directory.'));
define('E_UPLOAD_FILE_WRITE', _('Insufficient permissions to write "%s" file.'));
define('E_UPLOAD_FILE_WRITE_DB', _('"%s" file could not be stored in the database: "%s"'));
define('E_UPLOAD_FILE_EXISTS', _('File "%s" already exists.'));
define('E_UPLOAD_MUST_STORE_FIRST', _('Upload::resizeImage() should be called before you save the uploaded file.'));
define('E_UPLOAD_MALICIOUS', _('"%s" file does not match the uploaded file or does not exists anymore.'));
//}}}

/** 
 * Classe pour la gestion de l'upload de fichiers.
 *
 * La classe se charge de vérifier la validité de l'upload, d'enregistrer sur 
 * le disque ou en bases de données le fichier, et d'appliquer un ou plusieurs 
 * filtres avant enregistrement.
 *
 * Exemples {{{
 *
 * Exemple 1 (uploade un fichier dans le répertoire /tmp):
 * ======================================================
 *
 * <code>
 * <?php
 * 
 * if (isset($_POST['submit'])) {
 *     try {
 *         $uploader = new Upload('nom_champs_file');
 *         $uploader->store('/tmp');
 *     } catch (Exception $exc) {
 *         echo $exc->getMessage();
 *     }
 * }
 * 
 * echo <<<END
 * <html>
 * <head>
 * </head>
 * <body>
 *     <form name="test" method="POST" action="test.php" enctype="multipart/form-data">
 *         <input type="file" name="nom_champs_file" value=""/>
 *         <input type="submit" name="submit" value="Ok"/>
 *     </form>
 * </body>
 * </html>
 * <html>
 * END;
 * 
 * ?>
 * </code>
 *
 * }}}
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License 
 * @package Framework
 */
class Upload {
    // propriétés {{{

    /**
     * Nom du champs file
     *
     * @access public
     * @var    string name
     */
    public $name = ''; 
    /**
     * Tableau des extensions supportées pour les images
     *
     * @var    array extensions
     * @access public
     */
    public $extensions = array('gif', 'png', 'jpg', 'jpeg'); 

    /**
     * Taille maximale en octets autorisée pour l'upload.
     * Dépend du type des champ _DataB64 et _DataBLOB:
     * - TINYBLOB / TINYTEXT      < 2^8    256 octets
     * - BLOB / TEXT              < 2^16   65536 octets
     * - MEDIUMBLOB / MEDIUMTEXT  < 2^24   16777216 octets
     * - LONGBLOB / LONGTEXT      < 2^32   4294967296 octets
     *
     * @access public
     * @var    int maxsize
     */
    public $maxsize = 16777216;

    /**
     * Tableau d'infos de l'upload en cours, tableau de la forme:
     * array(
     *     'name'=>'nomoriginal.png',             // nom du fichier original
     *     'tmp_name'=>'upload_tmp_dir/tmp_name', // chemin sur le serveur
     *     'extension'=>'png',                    // extension du fichier
     *     'mime_type'=>'image/png',              // type mime
     *     'size'=>'10234'                        // taille en octets
     * )
     *
     * @access public
     * @var    array info
     */
    public $infos = false; 

    /**
     * Flag permettant de déterminer si le check a été effectué
     *
     * @access protected
     * @var    boolean _checkdone
     */
    protected $_checkdone = false;

    /**
     * Flag permettant de déterminer si l'enregistrement a été effectué
     *
     * @access protected
     * @var    boolean _storedone
     */
    protected $_storedone = false;

    // }}}
    // Constructeur {{{

	/**
     * Constructor
     *
     * @param string $name nom du champ File
     * @return void
     */
	public function __construct($name){
        $this->name = $name;
        if (!empty($_FILES)) {
            if (!isset($_FILES[$this->name])) {
                trigger_error(sprintf(E_UPLOAD_NO_FILE_INPUT, $this->name),
                    E_USER_ERROR);
            }
            $name_ext = explode('.', $_FILES[$name]['name']);
            $ext = strtolower(array_pop($name_ext));
            $this->infos = array(
                'name'=>$_FILES[$this->name]['name'],
                'tmp_name'=>$_FILES[$this->name]['tmp_name'],
                'mime_type'=>$_FILES[$this->name]['type'],
                'extension'=>strtolower($ext),
                'size'=>$_FILES[$this->name]['size'],
                'error'=>$_FILES[$this->name]['error']
            );
        }
	}

    // }}}
    // Upload::check() {{{

    /**
     * Batterie de vérifications des fichiers uploadés.
     * Retourne true si tous les tests sont ok ou lève une exception.
     *
     * Cette fonction est publique, mais il n'est pas nécessaire de l'appeler 
     * explicitement, en effet si on appelle Upload::store() ou 
     * Upload::dbstore(), elle est appelée par ces méthodes.
     *
     * @access public
     * @return boolean
     * @throws Exception
     */
    public function check() {
        if ($this->_checkdone) {
            return true;
        }
        // checke si le tableau a bien été initialisé
        if (!$this->infos) {
            throw new Exception(E_UPLOAD_FORM_NOT_POSTED);
        }
        // gestion des erreurs de base
        if ($this->infos['error'] > UPLOAD_ERR_OK) {
            // erreur, taille fichier dépassée
            switch ($this->infos['error']) {
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception(E_UPLOAD_NO_FILE);
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception(sprintf(E_UPLOAD_MAX_SIZE, 
                        $this->infos['name'], $this->maxsize));
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception(sprintf(E_UPLOAD_PARTIAL, 
                        $this->infos['name']));
                case UPLOAD_ERR_NO_TMP_DIR:
                    trigger_error(E_UPLOAD_TMP_DIR_MISSING, E_USER_ERROR);
                case UPLOAD_ERR_CANT_WRITE:
                    $tmpdir = ini_get('upload_tmp_dir');
                    trigger_error(sprintf(E_UPLOAD_DIR_WRITE, $tmpdir),
                        E_USER_ERROR);
                default:
                    // on ne devrait pas passer ici
                    trigger_error('Unknown error !');
            }
        }
        if(false !== strpos($this->infos['mime_type'], 'image')) {
            // check du type de l'image

		    if (!in_array($this->infos['extension'], $this->extensions)) {
                throw new Exception(sprintf(E_UPLOAD_UNSUPPORTED_EXTENSION,
                    $this->infos['name'], implode(', ', $this->extensions)));
		    }
        }
        // check de la taille du fichier
        if ($this->infos['size'] > $this->maxsize) {
            throw new Exception(sprintf(E_UPLOAD_MAX_SIZE, 
                $this->infos['name'], $this->maxsize));
        }
        $this->_checkdone = true;
    }

    // }}}
    // Upload::store() {{{

    /**
     * Sauve le fichier uploadé dans le répertoire passé en paramètre et 
     * retourne true ou lève une Exception en cas de problème.
     *
     * @access public
     * @param  string $path le répertoire où doit être copié le fichier
     * @param  boolean $overwrite (optionnel) si true et qu'un fichier existe
     *         avec le même nom il est écrasé.
     * @param  string $name  (optionnel) nom alternatif pour le fichier
     * @return boolean
     * @throws Exception
     */
    public function store($path, $overwrite=true, $name=false) {
        if ($this->_storedone) {
            return;
        }
        try {
            $this->check();
        } catch (Exception $exc) {
            throw $exc;
        }
        if (is_uploaded_file($this->infos['tmp_name'])) {
            if (!is_writeable($path)) {
                throw new Exception(sprintf(E_UPLOAD_DIR_WRITE, $path));
            }
            $newfile = $name?$name:$this->infos['name'];
            $newfile = $path . '/' . basename($newfile);
            if (file_exists($newfile) && !$overwrite) {
                throw new Exception(sprintf(E_UPLOAD_FILE_EXISTS, $newfile));
            }
            if (move_uploaded_file($this->infos['tmp_name'], $newfile)) {
                $this->_storedone = true;
                return true;
            } else {
                throw new Exception(sprintf(E_UPLOAD_FILE_WRITE, $newfile));
            }
        }
        trigger_error(sprintf(E_UPLOAD_MALICIOUS, $this->infos['tmp_name']),
            E_USER_ERROR);
    }

    // }}}
    // Upload::dbstore() {{{

    /**
     * Sauve le fichier uploadé en bases de données.
     * retourne true ou lève une Exception en cas de problème.
     *
     * @access public
     * @param  string $path le répertoire où doit être copié le fichier
     * @param  mixed $filter (optionnel) le nom (ou un tableau de nom) de 
     *         fonction "callable" qui va formatter le contenu du fichier avant 
     *         upload, cela est pratique par ex. pour redimensionner une image 
     *         à la volée.
     * @param  string $name  (optionnel) nom alternatif pour le fichier
     * @return boolean
     * @throws Exception
     */
    public function dbstore($overwrite=true, $name=false) {
        if ($this->_storedone) {
            return;
        }
        try {
            $this->check();
        } catch (Exception $exc) {
            throw $exc;
        }
        if (is_uploaded_file($this->infos['tmp_name'])) {
            $name = $name?$name:$this->infos['name'];
            $uuid = md5($name);
            // Mise en commentaire de cette ligne (bug 3619)
            //$qname = Database::connection()->qstr($name);
            $qname = $name;
/*            $sql = 'SELECT COUNT(_Name) FROM ' . DB_UPLOAD_TABLE 
                 . ' WHERE _Name="' . $qname . '"';*/
            $sql = 'SELECT COUNT(_Name) FROM ' . DB_UPLOAD_TABLE 
                 . ' WHERE _UUID="' . $uuid . '"';
            $dbid = defined('DATABASE_ID')?DATABASE_ID:'NULL';
            if ($dbid != 'NULL') {
                $sql .= ' AND _DBId=' . $dbid;
            }
            $rs = Database::connection()->execute($sql);
            $exists = ($rs && $rs->fields['COUNT(_Name)'] > 0);
            if ($rs) $rs->close();
            if ($exists && !$overwrite) {
                throw new Exception(sprintf(E_UPLOAD_FILE_EXISTS, $name));
            }
            $data = file_get_contents($this->infos['tmp_name']);
            $field = DB_UPLOAD_AS_BASE64?'_DataB64':'_DataBLOB';
            //$data = (DB_UPLOAD_AS_BASE64 && $this->infos['mime_type'] != 'application/pdf')?
            $data = DB_UPLOAD_AS_BASE64?
                base64_encode($data):Database::connection()->qstr($data);
            if ($exists) {
                $sql = 'UPDATE ' . DB_UPLOAD_TABLE . ' SET _DBId=' . $dbid
                     . ', _Name="' . $qname . '", ' . $field . '="' . $data
                     . '", _MimeType="' . $this->infos['mime_type'] . '" '
                     . 'WHERE _UUID="' . $uuid . '"';
//                     . 'WHERE _Name="' . $qname . '"';
            } else {
                $sql = 'INSERT INTO ' . DB_UPLOAD_TABLE . '(_DBId, _Name, '
                     . $field . ', _MimeType, _UUID) VALUES (' . $dbid . ', "'
                     . $qname . '", "' . $data  . '", "'
                     . $this->infos['mime_type'] . '", "' . $uuid . '")';
            }
            $result = Database::connection()->execute($sql);
            if ($result) {
                $result->close();
                $this->_storedone = true;
                return true;
            } else {
                trigger_error(sprintf(E_UPLOAD_FILE_WRITE_DB, $name, 
                    Database::connection()->errorMsg()), E_USER_ERROR);
            }
        }
        trigger_error(sprintf(E_UPLOAD_MALICIOUS, $this->infos['tmp_name']),
            E_USER_ERROR);
    }

    // }}}
    // Upload::resizeImage() {{{

	/**
     * Méthode spécifique aux uploads d'image.
	 * Redimensionne l'image uploadée en gardant les proportions avant stockage
	 * 
	 * @access public
     * @param  int $width
     * @param  int $height
	 * @return void 
	 */
	public function resizeImage($width=200, $height=200){
        if ($this->_storedone) {
            trigger_error(E_UPLOAD_MUST_STORE_FIRST, E_USER_ERROR);
        }
        try {
            $this->check();
        } catch (Exception $exc) {
            throw $exc;
        }
        if (is_uploaded_file($this->infos['tmp_name'])) {
		    $array = getimagesize($this->infos['tmp_name']);
		    list($w, $h) = $array;
		    if($w >= $h && $height < $h){
		        $height = $h / ($w / $width);
		    } else if ($w < $h && $width < $w) {
		        $width = $w / ($h / $height);   
		    } else {
		        $width = $w;
		        $height = $h;
		    }
		    $thumb  = imagecreatetruecolor($width, $height);
            $createfunc = 'imagecreatefrom' . $this->infos['extension'];
		    $source = $createfunc($this->infos['tmp_name']);
		    imagecopyresized($thumb, $source, 0, 0, 0, 0, $width, $height, $w, $h);
            $displayfunc = 'image' . $this->infos['extension'];
		    $displayfunc($thumb, $this->infos['tmp_name']);
        } else {
            trigger_error(sprintf(E_UPLOAD_MALICIOUS, $this->infos['tmp_name']),
                E_USER_ERROR);
        }
	}
    // }}}
    // Upload::getUploadedFiles() {{{

    /**
     * getUploadedFiles 
     *
     * Retourne un tableau avec tout les fichiers de la table, pour chaque 
     * fichier les infos retournées sont:
     * <code>
     * array('type'=>'MimeType', 'name'=>'file name', 'uuid'=>'file uuid')
     * </code>
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getUploadedFiles($noImage=null) {
        $files = array();
        $field = DB_UPLOAD_AS_BASE64?'_DataB64':'_DataBLOB';
        $sql = 'SELECT '.$field.', _Name, _MimeType, _UUID, length('.$field.') as size ' .
            'FROM ' . DB_UPLOAD_TABLE;
        $pad = ' WHERE ';
        if($noImage === true) {
            $sql .= ' WHERE _MimeType NOT LIKE "image%"';
            $pad = ' AND ';
        } elseif($noImage === false) {
            $sql .= ' WHERE _MimeType LIKE "image%"';
            $pad = ' AND ';
        }
        $dbid = defined('DATABASE_ID')?DATABASE_ID:'NULL';
        if ($dbid != 'NULL') {
            $sql .= $pad . '_DBId=' . $dbid;
        }
        $rs = Database::connection()->execute($sql);
        if ($rs) {
            while (!$rs->EOF) {
                $content = $rs->fields[$field];
                $data = DB_UPLOAD_AS_BASE64?
                    base64_decode($content):$content;
                $width = $height = 0;
                $mimetype = $rs->fields['_MimeType'];
                if(false !== strpos($mimetype, 'image')) {
                    $img = imagecreatefromstring($data);
                    $width = imagesx($img);
                    $height = imagesy($img);
                }
                $files[] = array(
                    'mimetype' => $mimetype, 
                    'name'     => $rs->fields['_Name'], 
                    'uuid'     => $rs->fields['_UUID'],
                    'size'     => $rs->fields['size'],
                    'width'    => $width,
                    'height'   => $height);
                $rs->moveNext();
            }
            $rs->close();
        } else {
            trigger_error(Database::connection()->errorMsg(), E_USER_ERROR);
        }
        return $files;
    }

    // }}}
    // Upload::getFileInfo() {{{

    /**
     * getFileInfo 
     *
     * Retourne le fichier de nom $name
     *
     * valeur retournée:
     * <code>
     * array(
     *  'data' => 'file content',
     *  'type' => 'mymetype',
     *  'name' => 'file name'
     * )
     * </code>
     * 
     * @param mixed $name nom du fichier
     * @static
     * @access public
     * @return void
     */
    public static function getFileInfo($uuid) {
        $data = false;
        $field = DB_UPLOAD_AS_BASE64?'_DataB64':'_DataBLOB';
        $sql = 'SELECT ' . $field . ', _MimeType, _Name, _UUID, length('.$field.') as size' .
            ' FROM ' . DB_UPLOAD_TABLE . 
            ' WHERE _UUID="' . $uuid . '"';
        $dbid = defined('DATABASE_ID')?DATABASE_ID:'NULL';
        if ($dbid != 'NULL') {
            $sql .= ' AND _DBId=' . $dbid;
        }
        $rs = Database::connection()->execute($sql);
        if ($rs) {
            $content = $rs->fields[$field];
            $data['data'] = DB_UPLOAD_AS_BASE64?
                base64_decode($content):$content;
            $data['mimetype'] = $rs->fields['_MimeType'];
            $data['name'] = $rs->fields['_Name'];
            $data['uuid'] = $rs->fields['_UUID'];
            $data['length'] = $rs->fields['size'];
            if(false !== strpos($data['mimetype'], 'image')) {
                $img = imagecreatefromstring($data['data']);
                $data['width'] = imagesx($img);
                $data['height'] = imagesy($img);
            }
            $rs->close();
        } else {
            trigger_error(Database::connection()->errorMsg(), E_USER_ERROR);
        }
        return $data;
    }

    // }}}
    // Upload::show() {{{

    /**
     * show 
     * 
     * @param mixed $uuid 
     * @static
     * @access public
     * @return void
     */
    public static function show($uuid, $encode=false) {
        if($encode) {
            $uuid = md5($uuid);
        }
        $file = self::getFileInfo($uuid);
        header('Content-Type: ' . $file['mimetype']);
        if(headers_sent()) {
            die('Some data has already been output to browser, can\'t send file');
        }
        header('Content-Length: ' . strlen($file['data']));
        header('Content-disposition: inline; filename="'.$file['name'].'"');
        echo $file['data'];
        exit();
    }

    // }}}
    // Upload::dbdelete() {{{

    /**
     * dbdelete 
     * 
     * @param string $uuid 
     * @static
     * @access public
     * @return void
     */
    public static function dbdelete($uuid='') {
        if(empty($uuid)) {
            return false;
        }
        $sql = 'DELETE FROM ' . DB_UPLOAD_TABLE . ' WHERE _UUID="'.$uuid.'"';
        $dbid = defined('DATABASE_ID')?DATABASE_ID:'NULL';
        if($dbid != 'NULL') {
            $sql .= ' AND _DBId=' . $dbid;
        }
        return Database::connection()->execute($sql);
    }

    // }}}
    // Upload::dbupdate() {{{
    
    /**
     * dbupdate 
     * 
     * @param string $uuid 
     * @param array $params 
     * @static
     * @access public
     * @return void
     */
    public static function dbupdate($uuid='', $params=array()) {
        if(empty($params) || empty($uuid)) {
            return false;
        }
        $sql = 'UPDATE ' . DB_UPLOAD_TABLE . ' SET';
        $pad = ' ';
        foreach($params as $field=>$value) {
            if($field=='Name') {
                $value = Database::connection()->qstr($value);
            }
            $sql .= $pad . '_' . $field . '="' . $value . '"';
            $pad = ', ';
        }
        $sql .= ' WHERE _UUID="' . $uuid . '"';
        $dbid = defined('DATABASE_ID')?DATABASE_ID:'NULL';
        if ($dbid != 'NULL') {
            $sql .= ' AND _DBId=' . $dbid;
        }
        return Database::connection()->execute($sql);
    }
    
    // }}}
}

?>
