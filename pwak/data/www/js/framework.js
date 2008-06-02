/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 fdm=marker */

/**
 * $Source: /home/cvs/framework/data/www/js/framework.js,v $
 *
 * Mini librairie pour le framework contenant des fonctions utilitaires qui ne
 * sont pas directement implémentées dans MochiKit.
 *
 * @version    CVS: $Id$
 * @copyright  2002-2006 ATEOR - All rights reserved
 */

var DEFAULT_LOCALE = 'fr_FR';
var DEFAULT_COOKIE = '_Auth_Lang';

// fw.cookie {{{

/**
 * Ensemble de méthodes relatives à la gestion des cookies.
 *
 */
var __cookie = {
    /**
     * Crée un cookie
     *
     * @param  string name le nom du cookie
     * @param  mixed value sa valeur
     * @param  days le nombre de jours avant expiration
     * @return void
     */
    create: function(name, value, days) {
	    if (days){
		    var d = new Date();
		    d.setTime(d.getTime()+(days*24*60*60*1000));
		    var expire = "; expires=" + d.toGMTString();
	    } else {
            var expire = "";
        }
	    document.cookie = name + "=" + value + expire + "; path=/";
    },

    /**
     * Lit un cookie
     *
     * @param  string name le nom du cookie à lire
     * @return mixed la valeur du cookie ou null
     */
    read: function(name){
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0;i < ca.length;i++){
		    var c = ca[i];
		    while (c.charAt(0)==' ') c = c.substring(1,c.length);
		    if (c.indexOf(nameEQ) == 0)
                return c.substring(nameEQ.length,c.length);
	    }
	    return null;
    }
};

// }}}
// fw.dom {{{

/**
 * Ensemble de méthodes relatives à la gestion du DOM html.
 *
 */
var __dom = {

    /**
     * Inverse la visibilité d'un element qui accepte l'attribut css display.
     * Si celui ci est à none, il passe à 'block', sinon il passe à none.
     * Si state est renseigné, alors le l'élement sera affiché ou masqué en
     * fonction de ce booléen.
     * Si switchButtonID est renseigné, alors le input type button de cet Id
     * changera aussi d'etat
     *
     * @param mixed eltID l'id de l'élément, oubien l'élément lui même
     * @param  boolean state optionnel, afficher (true) ou masquer (false)
     * @return void
     */
    toggleElement: function(eltID, state, switchButtonID) {
        eltID = (typeof(eltID) == "string")?eltID:$(eltID).id;
        if (typeof(state) == "undefined" || state == null) {
            var state = getStyle(eltID, 'display');
        }
        if (state == true || state == 'none') showElement($(eltID));
        else
            hideElement($(eltID));
        if (arguments.length == 3) {
            $(switchButtonID).value = $(eltID).style.display!='none'?'-':'+';
        }
    },

    /**
     * Selectionne une option dans une liste deroulante par sa valeur
     *
     * @param mixed selectID l'id de l'élément, oubien l'élément lui même
     * @param  mixed  optionValue la valeur de(s) l'option(s) à selectionner
     * @return boolean true si l'option a pu être sélectionnée et false sinon
     */
    selectOptionByValue: function(selectID, optionValue) {
        var done = false;
        var widget = (typeof(selectID) == "string")?$(selectID):selectID;
        var selectedOptions = (optionValue instanceof Array)?optionValue:[optionValue];
        if (widget && widget.options) {
	        for (var i = 0; i < widget.options.length ;i++) {
                for (var j = 0; j < selectedOptions.length ;j++) {
                    if (widget.options[i].value == selectedOptions[j]) {
    			        widget.options[i].selected = true;
                        done = true;
    		        }
                }

	        }
        }
        return done;
    },

    /**
     * Selectionne une option dans une liste deroulante par son index
     *
     * @param mixed selectID l'id de l'élément, oubien l'élément lui même
     * @param  mixed  optionIndex l'index de l'option à selectionner
     * @return boolean true si l'option a pu être sélectionnée et false sinon
     */
    selectOptionByIndex: function(selectID, optionIndex) {
        var done = false;
        var widget = (typeof(selectID) == "string")?$(selectID):selectID;
        if (widget && widget.options) {
	        for (var i = 0; i < widget.options.length ;i++) {
		        if (i == optionIndex) {
			        widget.options[i].selected = true;
                    done = true;
		        }
	        }
        }
        return done;
    },

    /**
     * Recupere dans un tableau les valeurs selectionnees dans un select multiple
     *
     * @param mixed selectID l'id de l'élément, oubien l'élément lui même
     * @return list
     */
    getMultipleSelectValues: function(selectID) {
        var selectedList = new Array();
        var widget = (typeof(selectID) == "string")?$(selectID):selectID;
        for (var i=0; i<widget.options.length; i++) {
            if (widget.options[i].selected) {
                selectedList.push(widget.options[i].value);
            }
        }
        return selectedList;
    }
};

// }}}
// fw.ajax {{{

/**
 * Ensemble de méthodes relatives à AJAX/JSON
 *
 */
var __ajax = {


    /**
     * Appelle une méthode du serveur AJAX par POST
     *
     * @access public
     * @return function a mochikit deferred
     */
    call: function(/*method, arg1, arg2, ...*/) {
        if (!doXHR) {  // loadJSONDoc
            alert("Error: function loadJSONDoc unavailable, check your MochiKit install");
        }
        var method = arguments[0];
        var args = new Array();
        for(var i=1; i<arguments.length; i++) {
            args[i-1] = serializeJSON(arguments[i]);
        }
        var sendContent = queryString(['calledMethod', 'args'], [method, serializeJSON(args)]);
        return doXHR(AJAX_SERVER_URL, {'method': 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 'sendContent': sendContent}).addCallback(MochiKit.Async.evalJSONRequest)
        // Si method GET:
        //return loadJSONDoc(AJAX_SERVER_URL, {'method': method, 'args': args});
    },

    /**
     * Permet d'updater un select widget2 en fonction de la value du select widget1
     *
     * @param string widget1Id
     * @param string widget2Id
     * @param string entity  nom de l'entite dont il faut une collection pour
     * construire le widget2Id
     * @param string filterName  nom de l'attribut FK pour filtrer cette collection
     * @param int noZeroItem true pour ne pas avoir le message select an element
     * @param array additionalFilter filtre supplémentaire de la forme
     * @param string method type de la requete: GET par defaut ou POST
     * {'Active':1}
     * @return object Deferred
     */
    updateSelect: function(widget1Id, widget2Id, entity, filterName, noZeroItem,
        additionalFilter, method) {
        if(!additionalFilter) {
            var filter = new Object();
        } else {
            var filter = additionalFilter;
        }
        if (!$(widget1Id).multiple) filter[filterName] = $(widget1Id).value;
        else {
            var selectedList = new Array();
            for (var i=0; i<$(widget1Id).options.length; i++) {
                if ($(widget1Id).options[i].selected) {
                    selectedList.push($(widget1Id).options[i].value);
                }
            }
            filter[filterName] = selectedList;
        }
        if (!method) {
            var d = this.call('getCollectionForSelect', entity, filter);
        }
        else {
            var d = this.call('getCollectionForSelect', entity, filter);
        }

        var onSuccess = function (data) {
            if (data.isError) {
                alert('Error (code ' + data.errorCode +'): ' + data.errorMessage);
                return;
            }
            // contruire les options
            if (!data.length || data.length == 0) {
                var opt = createDOM('option', {'value': '##'}, 'N/A');
                replaceChildNodes($(widget2Id), opt);
                return;
            }
            var nodes = new Array();
            if (!noZeroItem) {
                nodes[0] = createDOM('option', {'value': '##'}, 'Sélectionner');
            }
            for(var i=0; i<data.length; i++) {
                //createDOM('option', {'value': data[i].id}, data[i].toString);
                nodes[i+1] = createDOM('option', {'value': data[i].id}, data[i].toString);
            }
            replaceChildNodes($(widget2Id), nodes)
        }
        var onError = function (err) {
            alert('Error: ' + err);
        }
        d.addCallbacks(onSuccess, onError);
        return d;
    },

    /**
     * Permet d'updater un select widget2 en fonction de la value du select widget1,
     * avec traitement spécifique cote serveur: appel de [methodName]() plutot que
     * getCollectionForSelect()
     *
     * @param string widget1Id
     * @param string widget2Id
     * @param string entity  nom de l'entite dont il faut une collection pour
     * construire le widget2Id
     * @param string filterName  nom de l'attribut FK pour filtrer cette collection
     * @param string methodName  nom de la methode cote serveur à appeler
     * @param int noZeroItem true pour ne pas avoir le message select an element
     * @param array additionalFilter filtre supplémentaire de la forme
     * {'Active':1}
     * @return object Deferred
     */
    updateSelectCustom: function(widget1Id, widget2Id, entity, filterName,
        methodName, noZeroItem, additionalFilter) {
        if(!additionalFilter) {
            var filter = new Object();
        } else {
            var filter = additionalFilter;
        }
        if (!$(widget1Id).multiple) filter[filterName] = $(widget1Id).value;
        else {
            var selectedList = new Array();
            for (var i=0; i<$(widget1Id).options.length; i++) {
                if ($(widget1Id).options[i].selected) {
                    selectedList.push($(widget1Id).options[i].value);
                }
            }
            filter[filterName] = selectedList;
        }
        var d = this.call(methodName, entity, filter);
        var onSuccess = function (data) {
            if (data.isError) {
                alert('Error (code ' + data.errorCode +'): ' + data.errorMessage);
                return;
            }

            // contruire les options
            if (!data.length || data.length == 0) {
                var opt = createDOM('option', {'value': '##'}, 'N/A');
                replaceChildNodes($(widget2Id), opt);
                return;
            }
            var nodes = new Array();
            if (!noZeroItem) {
                nodes[0] = createDOM('option', {'value': '##'}, 'Sélectionner');
            }
            for(var i=0; i<data.length; i++) {
                //createDOM('option', {'value': data[i].id}, data[i].toString);
                nodes[i+1] = createDOM('option', {'value': data[i][0]}, data[i][1]);
            }
            replaceChildNodes($(widget2Id), nodes)
        }
        var onError = function (err) {
            alert('Error: ' + err);
        }
        d.addCallbacks(onSuccess, onError);
        return d;
    },

    /*
     * Suite à retour sur ecran contenant un formulaire, conservation des
     * saisies dans le form, en recuperant les donnees en session.
     * Pour cela il faut que les var en session aient le meme nom que les champs
     * de formulaire (utilisation de SearchTools::inputDataInSession) par exemple
     * Gere les input type text, hidden, select non multiple
     * XXXTODO debuger si le champs de form n'existe pas: ca stoppe le js sans 
     * provoquer d'erreur: pas de solution trouvee a ce pb
     *
     * @param array of strings fieldNames
     * @return void
     */
    restoreWidgetsFromSession: function(fieldNames) {
        var dd = this.call('getSessionContent', fieldNames);
        var onSuccess = function (data) {
            if (data.isError) {
                alert('Error (code ' + data.errorCode +'): ' + data.errorMessage);
                return;
            }
            var varNames = keys(data);
            if (!varNames.length || varNames.length == 0) {
                return;
            }
            var count = varNames.length;
            var docForm = currentDocument().forms[0];
            for(var i=0; i<count; i++) {
                var varName = varNames[i];
                var isArray = eval("data." + varName) instanceof Array;
                // Cette var en session n'est pas un tableau
                var varNameData = (isArray)?
                    eval("data." + varName):[eval("data." + varName)];
                var itemNumber = varNameData.length;
                for(var j=0; j < itemNumber; j++) {
                    if (typeof(docForm.elements[varName]) != "undefined") {
                        if (!docForm.elements[varName].options) {  // input text/hidden
                            docForm.elements[varName].value = varNameData[j];
                        }else {  // select
                            var eltId = docForm.elements[varName].id;
                            fw.dom.selectOptionByValue(eltId, varNameData[j]);
                        }
         //               continue; // 1 seul champs dans ce cas
                    }
                    else if (typeof(docForm.elements[varName + '[]'][j]) != "undefined") {
                        if (!docForm.elements[varName + '[]'][j].options) {  // input text
                            docForm.elements[varName + '[]'][j].value = varNameData[j];
                        }else {  // select
                            var eltId = docForm.elements[varName + '[]'][j].id;
                            fw.dom.selectOptionByValue(eltId, varNameData[j]);
                        }
                    }
                    else if (typeof(docForm.elements[varName + '[]']) != "undefined") {
                        if (!docForm.elements[varName + '[]'].options) {  // input text
                            docForm.elements[varName + '[]'].value = varNameData[j];
                        }else {  // select
                            var eltId = docForm.elements[varName + '[]'].id;
                            fw.dom.selectOptionByValue(eltId, varNameData[j]);
                        }
         //               continue; // 1 seul champs dans ce cas
                    }
                    else {
                        continue;
                    }
                }
            }
        }
        var onError = function (err) {
            alert('Error: ' + err);
        }
        dd.addCallbacks(onSuccess, onError);
        return dd;
    }
};

// }}}
// fw.grid {{{

/**
 * Mini librairie : module dédié aux grids
 *
 */
var __grid = {
    /**
     * Soumet le formulaire du grid.
     *
     * @param  object ownerForm
     * @param  integer actionIndex
     * @return boolean
     */
    triggerAction: function(ownerForm, actionIndex){
        this.buildToRemoveData(ownerForm);
    	ownerForm.elements['actionId'].value = actionIndex;
    	ownerForm.target = '_self';
    	ownerForm.action = window.location;
        ownerForm.method = 'POST';
    	ownerForm.submit();
    	return false;
    },

    /**
     * Pour la pagination
     * @return boolean false
     */
    triggerPopupAction: function(ownerForm, actionIndex){
        this.buildToRemoveData(ownerForm);
    	ownerForm.elements['actionId'].value = actionIndex;
    	ownerForm.target = 'popup';
    	ownerForm.action = window.location;
        ownerForm.method = 'GET';
    	ownerForm.submit();
    	return false;
    },

    /**
     * Pour la pagination
     * @return boolean false
     */
    jumpToPage: function(pageIndex, ownerForm){
        this.buildToRemoveData(ownerForm);
        ownerForm.elements['PageIndex'].value = pageIndex;
    	ownerForm.elements['actionId'].value = -1;
    	ownerForm.target = '_self';
    	ownerForm.action = window.location;
        ownerForm.method = 'POST';
    	ownerForm.submit();
    	return false;
    },

    /**
     * Appelé lors de désélection d'un item du grid pour le supprimer
     * en session aussi
     * @return void
     */
    buildToRemoveData: function(ownerForm){
        ownerForm.elements['toRemove'].value = toRemove.join('|');
    },


    /**
     * Selectionne ou deselectionne toutes les cases a cocher du grid.
     * @param object widget
     * @return void
     */
    changeCheckedStateOfAllItems: function(widget) {
        var ownerForm = widget.form;
        var elts = ownerForm.elements['gridItems[]'];
        elts = typeof(elts.length) == 'undefined'?[elts]:elts;
    	for(var i = 0; i < elts.length; i++) {
    		elts[i].checked = widget.checked;
            fw.grid.handleCBDeselect(elts[i]);
    	}
    },

    /**
     * Pour la gestion des tris (multiples) sur les colonnes de grid
     * Remplit le tableau gridSortOrderList
     * @return boolean true
     */
    sortLinkExecute: function(filterOrder, columnIndex, sortOrder){
    	if(filterOrder < gridSortOrderList.length){
    		gridSortOrderList[filterOrder].sortOrder = sortOrder;
    	}else{
    		gridSortOrderList[filterOrder] = new gridSortOrderItem(filterOrder, columnIndex, sortOrder);
    	}
    	this.populateGridSortLayer();
        document.forms[0].method = "POST";
    	document.forms[0].target = '_self';
    	document.forms[0].submit();
    	return true;
    },

    /*
     * Pour la gestion des tris (multiples) sur les colonnes de grid
     * Creation pour cela de input(s) type hidden
     */
    populateGridSortLayer: function() {
    	var AddonLayer = getElement('GridSortAddon');
    	if(AddonLayer != null) {
        	for(var i = 0; i < gridSortOrderList.length; i++) {
                var hiddenInput = INPUT({'id' : 'order[' + i + '][' + gridSortOrderList[i].columnIndex + ']'});
                updateNodeAttributes(hiddenInput, {
                    'name' : 'order[' + i + '][' + gridSortOrderList[i].columnIndex + ']',
                    'type' : 'hidden',
                    'value' : gridSortOrderList[i].sortOrder
                });

    	       appendChildNodes(AddonLayer, hiddenInput);
    	   }
    	}
    },


    /**
     * Appelé lors de désélection d'un item du grid pour le supprimer
     * en session aussi
     * @return boolean false
     */
    handleCBDeselect: function(widget) {
        var val = (widget.checked == false)?widget.value:-1;
        var func = removeElementClass;
        if(val == -1) {
            // on coche la checkbox, il faut l'enlever du tableau removeItem
            // si elle y est
            toRemove = filter(function(i) {return i != widget.value;}, toRemove);
            func = addElementClass;
        } else {
            // on décoche la checkbox, on l'ajoute au tableau
            toRemove.push(val);
        }
        // assigne/enlève la classe à la ligne du grid
        func(widget.parentNode.parentNode, 'hover');
    },

    /**
     * Initialise le comportement "sortable" des lignes de grid.
     *
     * @return void
     */
    sortableInit: function() {
        var opts = {tag: 'tr', onUpdate: fw.grid.sortableOnUpdate};
        if (!/MSIE/.test(navigator.userAgent)) {
            // XXX fait planter ie6 et ie7 :(
            opts.ghosting = true;
        }
        MochiKit.Sortable.Sortable.create('table_tbody', opts);
    },

    /**
     * Callback appelé quand l'utilisateur a drag'n'droppé une ligne de grid.
     * Appèle le serveur ajax pour modifier l'index des objets du grid.
     *
     * @return void
     */
    sortableOnUpdate: function(elt) {
        try {
            var ids = MochiKit.Sortable.Sortable.serialize(elt);
            ids = ids.substring('table_tbody[]='.length);
            ids = ids.split('&table_tbody[]=');
        } catch (e) {
            return;
        }
        var lastclass = 'grid_row_even';
        var resetCssFunc = function(x) {
            setElementClass('_' + x, lastclass + ' grid_dnd_sortable');
            lastclass = (lastclass == 'grid_row_odd') ?
                'grid_row_even' : 'grid_row_odd';
        }
        var d = fw.ajax.call(
            'dndSortEntity',
            $('Grid_EntityName').value,
            $('Grid_DndSortableField').value,
            ids,
            $('PageIndex').value
        );
        var onSuccess = function (data) {
            if (data.isError) {
                alert('Error (code ' + data.errorCode +'): ' + data.errorMessage);
                return;
            }
            map(resetCssFunc, ids);
            if(data != true) {
                alert(data);
            }
        }
        var onError = function (err) {
            alert('Error: ' + err);
        }
        d.addCallbacks(onSuccess, onError);
    }

}

// }}}
// fw.i18n {{{

/**
 * Ensemble de méthodes relatives à la gestion de l'i18n.
 *
 */
var __i18n = {
    /**
     * Retourne la locale courante (via le cookie) ou à défaut la locale par
     * défaut définie dans la globale LOCALE_DEFAULT.
     *
     * @param sting cookie nom du cookie
     * @return string
     */
    getLocale: function(cookie) {
        var cookieName = cookie?cookie:DEFAULT_COOKIE;
        var locale = fw.cookie.read(cookieName);
        return locale?locale:DEFAULT_LOCALE;
    },

    /**
     * Défini la locale courante en renseignant le cookie adhoc.
     *
     * @param string locale la locale que l'on veut utiliser (ex: en_GB)
     * @param sting cookie nom du cookie
     * @return string
     */
    setLocale: function(locale, cookie) {
        var cookieName = cookie?cookie:DEFAULT_COOKIE;
        return fw.cookie.create(cookieName, locale, 10);
    },

    /**
     * Formate une date conformément aux usages dans la langue courante.
     * Utilise le cookie de langue pour la locale.
     *
     * @param object Date dateObj
     * @param sting cookie nom du cookie
     * @return string
     */
    formatDate: function(dateObj, cookie) {
        var year = dateObj.getFullYear();
        var month = dateObj.getMonth() + 1 <10?'0' + (dateObj.getMonth() + 1):dateObj.getMonth() + 1;
        var day = dateObj.getDate() <10?'0' + dateObj.getDate():dateObj.getDate();
        switch(fw.i18n.getLocale(cookie)) {
            case 'en_GB':
                return year + '/' + month + '/' + day;
                break;
            case 'fr_FR':
            case 'nl_NL':
            case 'tr_TR':
                return day + '/' + month + '/' + year;
                break;
            case 'de_DE':
                return year + '.' + month + '.' + day;
                break;
            default:
                return year + '/' + month + '/' + day;
        } // switch
    },

    /**
     * Formate un nombre conformément aux usages dans la langue courante.
     * Utilise le cookie de langue pour la locale.
     * Par defaut, Mochikit possede les locales en_US, de_DE, fr_FR, et pt_BR
     * auxquelles on ajoute en_GB, et autant d'autres que necessaire
     *
     * @param float value la valeur numeric a traiter
     * @param integer dec_num le nombre de décimales (facultatif)
     * @param sting cookie nom du cookie
     * @return string
     */
    formatNumber: function(value, dec_num, cookie) {
        MochiKit.Format.LOCALE.en_GB = MochiKit.Format.LOCALE.en_US;
        MochiKit.Format.LOCALE.nl_NL = MochiKit.Format.LOCALE.pt_BR;
        MochiKit.Format.LOCALE.tr_TR = MochiKit.Format.LOCALE.pt_BR;
        dec_num = dec_num?dec_num:2;
        // Ne gere que decimales a 2 ou 3 chiffres!!
        var pattern = (dec_num == 2)?"######.00":"######.000";
	    var localeNumberFormatter = numberFormatter(pattern, "",
            fw.i18n.getLocale(cookie));
	    return localeNumberFormatter(value);
    },

    /**
     * Extrait la valeur numérique d'un nombre formatté en fonction des usages
     * de la langue courante.
     * Plus permissif que I18n::extractNumber(), qui ne gere pas les separateurs 
     * de milliers, mais qui peut le plus peut le moins
     *
     * @param mixed mixed soit un widget, ou un id de widget, ou une valeur
     * @param sting cookie nom du cookie
     * @return float
     */
    extractNumber: function(mixed, cookie) {
        var value = $(mixed)?$(mixed).value:mixed;
        switch (fw.i18n.getLocale(cookie)) {
            case 'fr_FR':
                // gere les saisies telles que: 123 456 789,123
                input = parseFloat(value.replace(',', '.').replace(/\s/g, ''));
                break;
            case 'en_GB':
            case 'en_US':
                // gere les saisies telles que: 123,456,789.123
                input = parseFloat(value.replace(/,/g, '').replace(/\s/g, ''));
                break;
            case 'nl_NL':
            case 'tr_TR':
                // gere les saisies telles que: 123.456.789,123
                input = parseFloat(value.replace(/\./g, '').replace(',', '.').replace(/\s/g, ''));
                break;
            default:
                // on ne devrait pas etre ici
                input = parseFloat(value);
        }
        return input;
    }
};

// }}}

/**
 * Namespace public
 *
 */
var fw = {
    cookie: __cookie,
    dom:    __dom,
    ajax:   __ajax,
    grid:   __grid,
    i18n:   __i18n
}
