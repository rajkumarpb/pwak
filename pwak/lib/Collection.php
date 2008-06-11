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

class Collection implements Iterator, ArrayAccess, Countable
{
    // propriétés publiques {{{

    /**
     * Le nom du type de collection.
     *
     * @var    string entityName
     * @access public
     */
    public $entityName = '';
    /**
     * Détermine si oui ou non une collection peut contenir des doublons.
     *
     * @var    boolean acceptDuplicate
     * @access public
     */
    public $acceptDuplicate = true;

    /**
     * Index de la page courante
     * (utilisé pour la pagination dans les grids).
     *
     * @var    integer currentPage
     * @access public
     */
    public $currentPage = 0;

    /**
     * Numéro de la dernière page
     * (utilisé pour la pagination dans les grids).
     *
     * @var    integer lastPageNo
     * @access public
     */
    public $lastPageNo = 0;

    /**
     * Nombre total d'enregistrements.
     * (utilisé pour la pagination dans les grids).
     *
     * @var    integer totalCount
     * @access public
     */
    public $totalCount = 0;

    /**
     * Tableau des ids items qui ont été supprimés de la collection.
     * (utilisé pour retrouver les items supprimés afin de pouvoir faire les
     * deleteObject() correspondant).
     *
     * @var    array removedItems
     * @access public
     */
    public $removedItems = array();

    // }}}
    // propriétés private/protected {{{

    /**
     * Le tableau des éléments de la collection.
     *
     * @var    array _items
     * @access private
     */
    private $_items = array();

    /**
     * Le tableau des ids de la collection.
     *
     * @var    array _itemIds
     * @access private
     */
    private $_itemIds = array();

    // }}}
    // Constructeur {{{

    /**
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct($entityName = '', $acceptDuplicate = true) {
        $this->entityName = $entityName;
        $this->acceptDuplicate = $acceptDuplicate;
        $this->reset();
    }

    // }}}
    // collection::getCount() {{{

    /**
     * Retourne le nombre d'éléments dans la collection.
     *
     * @access public
     * @return integer
     */
    public function getCount() {
        return count($this->_items);
    }

    // }}}
    // Collection::getItem() {{{

    /**
     * Retourne l'element à l'index $index
     *
     * @access public
     * @param  integer $index
     * @return mixed un objet de la collection ou false
     */
    public function getItem($index) {
        if (isset($this->_items[$index])) {
            if (is_numeric($this->_items[$index])) {
                $mapper = Mapper::singleton($this->entityName);
                return $mapper->load(array("Id"=>$this->_items[$index]));
            }
            return $this->_items[$index];
        }
        $return = false;
        return $return;
    }

    // }}}
    // Collection::getItemById() {{{

    /**
     * Retourne l'element de la collection qui a l'id $id ou false si aucun
     * élément n'a cet ID.
     *
     * @access public
     * @param  integer $id l'id de l'objet à récupérer
     * @return mixed un objet de la collection ou false
     */
    public function getItemById($id) {
        foreach($this->_itemIds as $i=>$currentID){
            if ($currentID == $id) {
                return $this->getItem($i);
            }
        }
        return false;
    }

    // }}}
    // Collection::getItemByObjectProperty() {{{

    /**
     * Retourne le premier element de la collection dont la propriété passée en
     * paramètre $prop vaut $value ou false si aucun élément n'est trouvé
     *
     * Example:
     * <code>
     * $act = $actorCollection->getItemByObjectProperty('Name', 'toto');
     * // $act vaudra false s'il n'y a pas d'acteur du nom de toto dans la
     * // collection $actCollection
     * </code>
     *
     * @access public
     * @param  string $prop le nom de la propriété de comparaison
     * @param  mixed $value la valeur de comparaison
     * @return mixed un objet de la collection ou false
     */
    public function getItemByObjectProperty($prop, $value) {
        $getter = 'get' . $prop;
        $count  = $this->getCount();
        for ($i=0; $i<$count; $i++) {
            $item = $this->getItem($i);
            if (method_exists($item, $getter) && $item->$getter() == $value) {
                return $item;
            }
        }
        return false;
    }

    // }}}
    // Collection::getIndexById() {{{

    /**
     * Retourne l'index de l'element de la collection qui a l'id $id ou false
     * si aucun élément n'a cet ID.
     *
     * @access public
     * @param  integer $id l'id de l'objet à récupérer
     * @return mixed integer or false
     */
    public function getIndexById($id) {
        return array_search($id, $this->_itemIds);
    }

    // }}}
    // Collection::setItem() {{{

    /**
     * Ajoute un élément à collection, si $index est different de null,
     * l'élément est ajouté à cet index, sinon il est ajouté à la fin.
     *
     * @access public
     * @param  mixed $mixed an Object instance or an integer
     * @param  integer $index
     * @return boolean
     */
    public function setItem($mixed, $index = null) {
        $id = method_exists($mixed, 'getId')?$mixed->getId():$mixed;
        if ($index === null && !$this->acceptDuplicate &&
            in_array($id, $this->_itemIds)) {
            return false;
        }
        if ($index === null) {
            $this->_items[] = $mixed;
            $this->_itemIds[] = $id;
        } else {
            $this->_items[$index] = $mixed;
            $this->_itemIds[$index] = $id;
        }
        $this->totalCount = count($this->_itemIds);
        return true;
    }

    // }}}
    // Collection::insertItem() {{{

    /**
     * Insere un élément à collection, si $index est different de -1,
     * l'élément est ajouté à cet index, avec decallage des index de tous les
     * elements suivants, sinon il est ajouté à la fin.
     *
     * @access public
     * @param  mixed $mixed an Object instance or an integer
     * @param  integer $index
     * @return boolean
     */
    public function insertItem($mixed, $index = -1) {
        $id = method_exists($mixed, 'getId')?$mixed->getId():$mixed;
        if (!$this->acceptDuplicate && in_array($id, $this->_itemIds)) {
            return false;
        }
        if ($index == -1) {
            $this->_items[] = $mixed;
            $this->_itemIds[] = $id;
        } else {
            $count = $this->getCount();
            for($i = $count - 1; $i >= $index; $i--) {
                $this->_items[$i+1] = $this->_items[$i];
                $this->_itemIds[$i+1] = $this->_itemIds[$i];
            }
            $this->_items[$index] = $mixed;
            $this->_itemIds[$index] = $id;
        }
        $this->totalCount = count($this->_itemIds);
        return true;
    }

    // }}}
    // Collection::removeItem() {{{

    /**
     * Supprime l'élément à l'index $index
     *
     * @access public
     * @param  integer $index
     * @return boolean
     */
    public function removeItem($index) {
        if (is_array($index)) {
            $count = count($index);
            for ($i = 0; $i < $count; $i++) {
                $this->removedItems[] = $this->_itemIds[$index[$i]];
                unset($this->_items[$index[$i]]);
                unset($this->_itemIds[$index[$i]]);
            }
        } else if (isset($this->_items[$index])) {
            $this->removedItems[] = $this->_itemIds[$index];
            unset($this->_items[$index]);
            unset($this->_itemIds[$index]);
        } else {
            return false;
        }
        $_tempcollection = $this->_items;
        $_tempcollectionIDs = $this->_itemIds;
        $this->reset();
        foreach($_tempcollection as $currentItemKey=>$currentItem) {
            $this->setItem($_tempcollection[$currentItemKey]);
        }
        $this->totalCount = count($this->_itemIds);
        return true;
    }

    // }}}
    // Collection::removeItemById() {{{

    /**
     * Collection::removeItemById()
     * supprime l'element de la collection qui a l'id $id
     *
     * @access public
     * @param  integer $id l'id de l'objet à supprimer de la collection
     * @return boolean true ou false
     */
    public function removeItemById($id) {
        foreach($this->_itemIds as $i=>$currentID){
            if ($currentID == $id) {
                return $this->removeItem($i);
            }
        }
        return false;
    }

    // }}}
    // Collection::sort() {{{

    /**
     * Tri la collection dans l'ordre défini par $order sur la propriété
     * $field en utilisant l'algo de tri à bulle.
     *
     * @access public
     * @param  string $field le nom du champs sur lequel on effectue le tri
     * @param  const $order SORT_ASC ou SORT_DESC
     * @return mixed true ou une exception
     */
    public function sort($field, $order = SORT_ASC) {
        $accessor = 'get' . $field;
        $count = $this->getCount();
        for($i = $count-1; $i > 0 ; $i--) {
            for($j = 0; $j < $i; $j++) {
                $itemJ = $this->getItem($j);
                $itemJ_1 = $this->getItem($j+1);
                if (!method_exists($itemJ, $accessor)) {
                    $msg = get_class($itemJ) .
                        ' doesn\'t have any method ' . $accessor;
                    return new Exception($msg);
                }
                if (!method_exists($itemJ_1, $accessor)) {
                    $msg = get_class($itemJ_1) .
                        ' doesn\'t have any method ' . $accessor;
                    return new Exception($msg);
                }
                if ((SORT_ASC == $order) &&
                    ($itemJ->$accessor() > $itemJ_1->$accessor()) ||
                   ((SORT_DESC == $order) &&
                       ($itemJ->$accessor() < $itemJ_1->$accessor()))) {
                    $this->setItem($itemJ_1, $j);
                    $this->setItem($itemJ, $j+1);
                }
            }
        }
        return true;
    }

    // }}}
    // Collection::reset() {{{

    /**
     * Remet la collection à zero.
     *
     * @access public
     * @return void
     */
    public function reset() {
        $this->_items = array();
        $this->_itemIds = array();
        $this->totalCount = 0;
    }

    // }}}
    // Collection::getIntersection() {{{

    /**
     * Retourne une collection d'objets qui se trouvent dans la collection
     * en cours et dans la collection passée en paramètre (intersection)
     *
     * @access public
     * @param  object $col
     * @return object Collection
     */
    public function getIntersection($col) {
        $result = new Collection();
        $result->entityName = $this->entityName;
        if ($this->entityName == $col->entityName) {
            $ids = array_intersect($this->_itemIds, $col->_itemIds);
            $ids = array_values($ids);
            foreach($ids as $i=>$currentID){
                $item = $this->getItem($i);
                if (!$item || $item->getId() != $currentID) {
                    $item = $col->getItem($i);
                }
                $result->setItem($item);
            }
        }
        return $result;
    }

    // }}}
    // Collection::getItemIds() {{{

    /**
     * Retourne un tableau d'ids des elements de la collection.
     *
     * @access public
     * @return array
     */
    public function getItemIds() {
        return $this->_itemIds;
    }

    // }}}
    // Collection::toString() {{{

    /**
     * Retourne la représentation textuelle de la collection, sans doublon
     *
     * @access public
     * @return string la représentation textuelle de la collection
     **/
    public function toString() {
        $toStringItems = array();
        $count = $this->getCount();
        for($i = 0; $i < $count; $i++){
            $item = $this->getItem($i);
            $toStringItems[] = $item->toString();
        }
        $toStringItems = array_unique($toStringItems);
        sort($toStringItems);
        return implode(', ', $toStringItems);
    }

    // }}}
    // Collection::merge() {{{

    /**
     * Merge n collections dans un seule
     *
     * @access public
     * @param  object autant de collections que l'on veut 'merger' avec $this
     * @return object Collection
     */
    public function merge() {
        $result = clone $this;
        for($i = 0; $i < func_num_args(); $i++) {
            $col = func_get_arg($i);
            if ($col instanceof Collection) {
                $count = $col->getCount();
                for($j = 0; $j < $count; $j++) {
                    $item = $col->getItem($j);
                    $result->setItem($item);
                    unset($item);
                }
            } else {
                $msg = 'Invalid type (' .gettype($col) . ') for item '
                     . $i . ', waited collection.';
                return new Exception($msg);
            }
        }
        return $result;
    }

    // }}}
    // Collection::toStringArray() {{{

    /**
     * Retourne un tableau Id => object->$method()
     *
     * @param string $method the method to use for array values instead of 
     *                       toString method (optional)
     * @param bool   $sort   determine whether to sort or not the array
     *
     * @access public
     * @return array
     */
    public function toArray($method=false, $sort=true) {
        $result = array();
        $count  = $this->getCount();
        $method = $method === false ? 'toString' : $method;
        for($i = 0; $i < $count; $i++) {
            $item  = $this->getItem($i);
            $value = $item->$method();
            if ($value instanceof Object) {
                $value = $value->toString();
            }
            $result[$item->getId()] = $value;
        }
        if ($sort) {
            asort($result);
        }
        return $result;
    }

    // }}}
    // Collection::toJSON() {{{

    /**
     * Retourne un tableau encodé au format json, contenant la représentation
     * json de chaque objet de la collection, en prenant en compte le paramètre
     * $field, qui est passé à Object::toJSON().
     *
     * Voir Object::toJSON() pour plus d'informations.
     *
     * @access public
     * @see    Object::toJSON()
     * @param  array $fields
     * @return array
     */
    public function toJSON($fields = array()) {
        $count   = $this->getCount();
        $padding = '';
        $result  = '[';
        for ($i=0; $i<$count; $i++) {
            $result .= $padding . $this->getItem($i)->toJSON($fields);
            $padding = ', ';
        }
        return $result . ']';
    }

    // }}}
    // Interface implementations
    // Iterator interface implementation {{{

    public function rewind() {
        reset($this->_items);
    }

    public function current() {
        return $this->getItem(key($this->_items));
    }

    public function key() {
        return key($this->_items);
    }

    public function next() {
        return next($this->_items);
    }

    public function valid() {
        return $this->current() != false;
    }

    // }}}
    // ArrayAccess interface implementation {{{

    public function offsetGet($offset) {
        return $this->getItem($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->setItem($value, $offset);
    }

    public function offsetUnset($offset) {
        return $this->removeItem($offset);
    }

    public function offsetExists($offset) {
        return isset($this->_items[$offset]);
    }

    // }}}
    // Countable interface implementation {{{

    public function count() {
        return $this->getCount();
    }

    // }}}
}

?>
