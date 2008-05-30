// ** I18N

// Calendar EN language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// Translator: David Duret, <pilgrim@mala-template.net> from previous french version

// full day names
Calendar._DN = new Array
("Dimanche",
 "Lundi",
 "Mardi",
 "Mercredi",
 "Jeudi",
 "Vendredi",
 "Samedi",
 "Dimanche");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Dim",
 "Lun",
 "Mar",
 "Mer",
 "Jeu",
 "Ven",
 "Sam",
 "Dim");

// full month names
Calendar._MN = new Array
("Janvier",
 "Février",
 "Mars",
 "Avril",
 "Mai",
 "Juin",
 "Juillet",
 "Août",
 "Septembre",
 "Octobre",
 "Novembre",
 "Décembre");

// short month names
Calendar._SMN = new Array
("Jan",
 "Fev",
 "Mar",
 "Avr",
 "Mai",
 "Juin",
 "Juil",
 "Aout",
 "Sep",
 "Oct",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "A propos du calendrier";

Calendar._TT["ABOUT"] =
"DHTML Date/Heure Sélecteur\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"Pour la dernière version visitez : http://www.dynarch.com/projects/calendar/\n" +
"Distribué par GNU LGPL.  Voir http://gnu.org/licenses/lgpl.html pour les details." +
"\n\n" +
"Sélection de la date :\n" +
"- Utiliser les boutons \xab, \xbb  pour sélectionner l\'année\n" +
"- Utiliser les boutons " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " pour sélectionner les mois\n" +
"- Garder la souris sur n'importe quel bouton pour une sélection plus rapide";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Sélection de l\'heure :\n" +
"- Cliquer sur heures ou minutes pour incrémenter\n" +
"- ou Maj-clic pour décrémenter\n" +
"- ou clic et glisser-deplacer pour une sélection plus rapide";

Calendar._TT["PREV_YEAR"] = "Année préc. (maintenir pour menu)";
Calendar._TT["PREV_MONTH"] = "Mois préc. (maintenir pour menu)";
Calendar._TT["GO_TODAY"] = "Atteindre la date du jour";
Calendar._TT["NEXT_MONTH"] = "Mois suiv. (maintenir pour menu)";
Calendar._TT["NEXT_YEAR"] = "Année suiv. (maintenir pour menu)";
Calendar._TT["SEL_DATE"] = "Sélectionner une date";
Calendar._TT["DRAG_TO_MOVE"] = "Déplacer";
Calendar._TT["PART_TODAY"] = " (Aujourd'hui)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Afficher %s en premier";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Fermer";
Calendar._TT["TODAY"] = "Aujourd'hui";
Calendar._TT["TIME_PART"] = "(Maj-)Clic ou glisser pour modifier la valeur";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d/%m/%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "Sem.";
Calendar._TT["TIME"] = "Heure :";

////////// AJOUT BRUNO  /////////////
// this table holds your special days, so that we can automatize
// ATTENTION: Contient UNIQUEMENT les jours feries qui ont une date 
// fixe chaque annee (pas comme lundi de Pentecote par exple)
var SPECIAL_DAYS = {
0 : [ 1 ], // special days in January
4 : [ 1, 8], // special days in May
6 : [ 14 ], // special days in July
7 : [ 15 ], // special days in August
10 : [ 1, 11 ], // special days in November
11 : [ 25 ] // special days in December
};

// this function returns true if the passed date is special
function dateIsSpecial(year, month, day) {
 var m = SPECIAL_DAYS[month];
 if (!m) return false;
 for (var i in m) if (m[i] == day) return true;
 return false;
}

/* this is the actual date status handler. Note that it receives the
 * date object as well as separate values of year, month and date, for 
 * your confort.
 * Retourne "special" pour les jours feires a date fixe chaque annee:
 * affichage different gere ds la css par style "special"
 * Pour desactiver des dates, par exple relativement au planning Acteur:
 * il suffit de retourner true pour les dates a desactiver. 
 */
function dateStatusHandler(date, y, m, d) {
 if (dateIsSpecial(y, m, d)) return "special";
 else return false; // pour desactiver par exple les week end, remplacer cette ligne par celle en dessous
 //return (date.getDay() == 6 || date.getDay() == 0) ? true : false; 
}