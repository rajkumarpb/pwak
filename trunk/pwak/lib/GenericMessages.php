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
 * @version   SVN: $Id: GenericMessages.php,v 1.5 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

define('E_ERROR_TITLE', _('Error'));
define('E_ERROR_IN_EXEC', _('Error when executing action'));
define('E_INFO_TITLE', _('Information'));
define('E_CONFIRMATION_TITLE', _('Confirm'));
define('E_QUESTION_TITLE', _('Question'));

define('E_ERROR_GENERIC', _('An error occurred'));
define('E_ERROR_SQL', _('SQL error.'));
define('E_ERROR_SESSION', _('Session expired, please try again.'));
define('E_ERROR_IMPOSSIBLE_ACTION', _('This action cannot be completed'));
define('E_MSG_CHOICE_DATE', _('You must select a wished date'));
define('E_MSG_MUST_SELECT_A', _('Please select a(n) %s.'));
define('E_MSG_CHECK_DATE',  _('Begin date must be lower than end date.'));
define('E_MSG_TRY_AGAIN', _('An error occurred, please try again later.'));
define('E_NO_ITEM_FOUND', _('Selected items were not found in the database.'));
define('E_MAXWEIGHT_OVER', _("Maximum allowed weight exceeded for selected airplane"));
define('E_ERROR_SAVING', _('Error.'));
define('E_PASSWD_MISMATCH', _('Passwords do not match, please correct.'));
define('E_NO_PAGE_FOUND', _('No page correspond to your request.'));
define('E_AUTH_FAILED', _('Authentication error.'));
define('E_NO_USER', _('No user'));
define('E_USER_NOT_ALLOWED', _('Access denied: user "%s" is not allowed to use this feature.'));
define('E_NO_RECORD_FOUND', _('No record found.'));
define('E_VALIDATE_FORM', _('Error(s) encountered'));
define('E_VALIDATE_REQUIRED_FIELD', _('Required field'));
define('E_VALIDATE_IS_INT', _('must be an integer'));
define('E_VALIDATE_IS_REQUIRED', _('is required'));
define('E_VALIDATE_FIELD', _('Field'));
define('E_VALIDATE_IS_DECIMAL', _('must be a float'));
define('E_VALIDATE_IS_EMAIL', _('must be a valid email address'));
define('E_VALIDATE_IS_URL', _('must be a valid URL, accepted protocols are http, https, ftp and news.'));

define('I_DELETE_ITEMS', _('Are you sure you want to delete selected item(s) ?'));
define('I_NEED_SELECT_ITEM', _('Please select one or more items.'));
define('I_NEED_SINGLE_ITEM', _('Please select one (and only one) item.'));
define('I_CONFIRM_ACTION', _('Are you sure you want to do this ?'));
define('I_CONFIRM_DO', _('Operation successful.'));
define('I_NOT_DELETED', _('Some items could not be deleted'));
define('I_NOT_DELETED_WITH_LIST', _('The following items could not be deleted: <ul><li>%s</li></ul>'));
define('I_ITEMS_DELETED', _('Selected items were successfully deleted.'));
define('I_NO_ITEMS_DELETED', _('None of the selected items could be deleted.'));

define('CANT_BE_SAVED', _('Item could not be saved because some property values must be unique for %s entity.'));
define('CANT_BE_DELETED', _('Item could not be deleted because some properties must be empty in order to delete the item.'));

define('MSG_SELECT_AN_ELEMENT', _('Select an item'));
define('MSG_SELECT_MANY_ELEMENTS', _('Select one or more items'));

// Actions
define('A_OK', _('Ok'));
define('A_CANCEL', _('Cancel'));
define('A_VALIDATE', _('Ok'));
define('A_ADD', _('Add'));
define('A_DELETE', _('Delete'));
define('A_UPDATE', _('Modify'));
define('A_CLOSE', _('Close'));
define('A_EXPORT', _('Export'));
define('A_PRINT', _('Print'));
define('A_ACTIVATE', _('Activate'));
define('A_DISABLE', _('Deactivate'));
define('A_YES', _('Yes'));
define('A_NO', _('No'));
define('A_VIEW', _('View'));
define('A_BACK', _('Back'));

?>
