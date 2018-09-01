<?php

/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       gift_card.php
 * 	\ingroup    giftmodule
 * 	\brief      Page to create/edit/view gift
 */
//if (! defined('NOREQUIREUSER'))          define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))            define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))           define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))          define('NOREQUIRETRAN','1');
//if (! defined('NOSCANGETFORINJECTION'))  define('NOSCANGETFORINJECTION','1');			// Do not check anti CSRF attack test
//if (! defined('NOSCANPOSTFORINJECTION')) define('NOSCANPOSTFORINJECTION','1');		// Do not check anti CSRF attack test
//if (! defined('NOCSRFCHECK'))            define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test done when option MAIN_SECURITY_CSRF_WITH_TOKEN is on.
//if (! defined('NOSTYLECHECK'))           define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL'))         define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))          define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))          define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))          define('NOREQUIREAJAX','1');         // Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"]))
    $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;

while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}

if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php"))
    $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php"))
    $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php"))
    $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php"))
    $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include("../../../main.inc.php");
if (!$res)
    die("Include of main fails");

include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
include_once(DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php');
dol_include_once('/giftmodule/class/gift.class.php');
dol_include_once('/giftmodule/lib/gift.lib.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("giftmodule@giftmodule", "other"));
$conf->global->MAIN_MULTILANGS = false;

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object = new Gift($db);
$object->id = $id;
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->giftmodule->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('giftcard'));     // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('gift');
$search_array_options = $extrafields->getOptionalsFromPost($extralabels, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
    if (GETPOST('search_' . $key, 'alpha'))
        $search[$key] = GETPOST('search_' . $key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref))
    $action = 'view';

// Security check - Protection if external user
if ($user->societe_id > 0) access_forbidden();
if ($user->societe_id > 0) $socid = $user->societe_id;
$result = restrictedArea($user, 'giftmodule', $id);
// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php';  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

/*
 * Actions
 *
 * Put here all code to do according to value of "action" parameter
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0)
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook)) {
    $error = 0;

    $permissiontoadd = $user->rights->giftmodule->create;
    $permissiontodelete = $user->rights->giftmodule->delete;
    $backurlforlist = dol_buildpath('/giftmodule/gift_list.php', 1);

    // Actions when printing a doc from card
    include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';
}
/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);

// Part to add record
if ($action == 'add') {
    if (!empty($cancel)) {
        header("Location: /giftmodule/gift_list.php");
        exit;
    }

    $projectid = (GETPOST('projectid') ? GETPOST('projectid', 'int') : 0);
    $label = GETPOST("label");
    $giver = GETPOST("giver");
    $address = GETPOST("address");
    $mail = GETPOST("mail");
    $fk_soc = GETPOST("fk_soc");
    $description = GETPOST("description");
    $weight = GETPOST("weight");
    $date = dol_mktime(GETPOST('datehour'), GETPOST('datemin'), GETPOST('datesec'), GETPOST('datemonth'), GETPOST('dateday'), GETPOST('dateyear'));
    $status = GETPOST("status");
    $sign = GETPOST("sign");

    $error = 0;

    if (empty($label)) {
        setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("Label")), null, 'errors');
        $error++;
    }

    if (empty($date)) {
        $date = dol_now();
    }

    if (empty($status)) {
        $status = "Active";
    }

    if (!$error) {
        $object->label = $label;
        $object->giver = $giver;
        $object->address = $address;
        $object->mail = $mail;
        $object->fk_soc = $fk_soc;
        $object->description = $description;
        $object->weight = $weight;
        $object->date = $date;
        $object->status = $status;
        $object->sign = $sign;

        // Fill array 'array_options' with data from add form
        $ret = $extrafields->setOptionalsFromPost($extralabels, $object);
        if ($ret < 0)
            $error++;

        $res = $object->create($user);
        if ($res > 0) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $res . "&idmenu=35");
            exit;
        } else {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }
}

// Part to update record
if ($action == 'update') {
    if (!empty($cancel)) {
        header("Location: /giftmodule/gift_list.php");
        exit;
    }

    dol_syslog("gift_card.php?action=update GIFTMODULE_AUTO_GENPDF=" . $conf->global->GIFTMODULE_AUTO_GENPDF, LOG_DEBUG);

    $id = GETPOST('id');
    $projectid = (GETPOST('projectid') ? GETPOST('projectid', 'int') : 0);
    $label = GETPOST("label");
    $giver = GETPOST("giver");
    $address = GETPOST("address");
    $mail = GETPOST("mail");
    $fk_soc = GETPOST("fk_soc");
    $description = GETPOST("description");
    $weight = GETPOST("weight");
    $date = dol_mktime(GETPOST('datehour'), GETPOST('datemin'), GETPOST('datesec'), GETPOST('datemonth'), GETPOST('dateday'), GETPOST('dateyear'));
    $status = GETPOST("status");
    $sign = GETPOST("sign");

    $error = 0;

    if (empty($id)) {
        setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("Id")), null, 'errors');
        $error++;
    }

    if (empty($label)) {
        setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("Label")), null, 'errors');
        $error++;
    }

    if (empty($date)) {
        $date = dol_now();
    }

    if (empty($status)) {
        $status = "Active";
    }

    if (!$error) {
        $object->rowid = $id;
        $object->label = $label;
        $object->giver = $giver;
        $object->address = $address;
        $object->mail = $mail;
        $object->fk_soc = $fk_soc;
        $object->description = $description;
        $object->weight = $weight;
        $object->date = $date;
        $object->status = $status;
        $object->sign = $sign;

        // Fill array 'array_options' with data from add form
        $ret = $extrafields->setOptionalsFromPost($extralabels, $object);
        if ($ret < 0)
            $error++;

        $res = $object->update($user);
        if ($res > 0) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . "&idmenu=35");
            exit;
        } else {
            setEventMessages($object->error, $object->errors, 'errors');
        }
    }
}

/*
 * Send mail
 */
if (($action == 'send' || $action == 'relance') &&
        !GETPOST('addfile') &&
        !GETPOST('removAll') &&
        !GETPOST('removedfile') &&
        !GETPOST('cancel') &&
        !GETPOST('modelselected')
) {
    if (empty($trackid))
        $trackid = GETPOST('trackid', 'aZ09');

    $subject = '';
    $actionmsg = '';
    $actionmsg2 = '';

    $langs->load('mails');

    if (is_object($object)) {
        if (is_object($hookmanager)) {
            $parameters = array();
            $reshook = $hookmanager->executeHooks('initSendToSocid', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
        }
    } else
        $thirdparty = $mysoc;

    if ($result > 0) {
        $sendto = '';
        $sendtocc = '';
        $sendtobcc = '';
        $sendtoid = array();
        $sendtouserid = array();
        $sendtoccuserid = array();

        // Define $sendto
        $receiver = GETPOST('receiver');
        if (!is_array($receiver)) {
            if ($receiver == '-1')
                $receiver = array();
            else
                $receiver = array($receiver);
        }
        $tmparray = array();
        if (trim(GETPOST('sendto'))) {
            // Recipients are provided into free text
            $tmparray[] = trim(GETPOST('sendto'));
        }
        if (count($receiver) > 0) {
            foreach ($receiver as $key => $val) {
                // Recipient was provided from combo list
                if ($val == 'thirdparty') { // Id of third party
                    $tmparray[] = dol_string_nospecial($thirdparty->name, ' ', array(",")) . ' <' . $thirdparty->email . '>';
                } elseif ($val) { // Id du contact
                    $tmparray[] = $thirdparty->contact_get_property((int) $val, 'email');
                    $sendtoid[] = $val;
                }
            }
        }
        if (!empty($conf->global->MAIN_MAIL_ENABLED_USER_DEST_SELECT)) {
            $receiveruser = GETPOST('receiveruser');
            if (is_array($receiveruser) && count($receiveruser) > 0) {
                $fuserdest = new User($db);
                foreach ($receiveruser as $key => $val) {
                    $tmparray[] = $fuserdest->user_get_property($val, 'email');
                    $sendtouserid[] = $val;
                }
            }
        }

        $sendto = implode(',', $tmparray);

        // Define $sendtocc
        $receivercc = GETPOST('receivercc');
        if (!is_array($receivercc)) {
            if ($receivercc == '-1')
                $receivercc = array();
            else
                $receivercc = array($receivercc);
        }
        $tmparray = array();
        if (trim(GETPOST('sendtocc'))) {
            $tmparray[] = trim(GETPOST('sendtocc'));
        }
        if (count($receivercc) > 0) {
            foreach ($receivercc as $key => $val) {
                // Recipient was provided from combo list
                if ($val == 'thirdparty') { // Id of third party
                    $tmparray[] = dol_string_nospecial($thirdparty->name, ' ', array(",")) . ' <' . $thirdparty->email . '>';
                } elseif ($val) { // Id du contact
                    $tmparray[] = $thirdparty->contact_get_property((int) $val, 'email');
                    //$sendtoid[] = $val;  TODO Add also id of contact in CC ?
                }
            }
        }
        if (!empty($conf->global->MAIN_MAIL_ENABLED_USER_DEST_SELECT)) {
            $receiverccuser = GETPOST('receiverccuser');

            if (is_array($receiverccuser) && count($receiverccuser) > 0) {
                $fuserdest = new User($db);
                foreach ($receiverccuser as $key => $val) {
                    $tmparray[] = $fuserdest->user_get_property($val, 'email');
                    $sendtoccuserid[] = $val;
                }
            }
        }
        $sendtocc = implode(',', $tmparray);

        if (dol_strlen($sendto)) {
            // Define $urlwithroot
            $urlwithouturlroot = preg_replace('/' . preg_quote(DOL_URL_ROOT, '/') . '$/i', '', trim($dolibarr_main_url_root));
            $urlwithroot = $urlwithouturlroot . DOL_URL_ROOT;  // This is to use external domain name found into config file
            //$urlwithroot=DOL_MAIN_URL_ROOT;					// This is to use same domain name than current

            require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';

            $langs->load("commercial");

            $fromtype = GETPOST('fromtype', 'alpha');
            if ($fromtype === 'robot') {
                $from = dol_string_nospecial($conf->global->MAIN_MAIL_EMAIL_FROM, ' ', array(",")) . ' <' . $conf->global->MAIN_MAIL_EMAIL_FROM . '>';
            } elseif ($fromtype === 'user') {
                $from = dol_string_nospecial($user->getFullName($langs), ' ', array(",")) . ' <' . $user->email . '>';
            } elseif ($fromtype === 'company') {
                $from = dol_string_nospecial($conf->global->MAIN_INFO_SOCIETE_NOM, ' ', array(",")) . ' <' . $conf->global->MAIN_INFO_SOCIETE_MAIL . '>';
            } elseif (preg_match('/user_aliases_(\d+)/', $fromtype, $reg)) {
                $tmp = explode(',', $user->email_aliases);
                $from = trim($tmp[($reg[1] - 1)]);
            } elseif (preg_match('/global_aliases_(\d+)/', $fromtype, $reg)) {
                $tmp = explode(',', $conf->global->MAIN_INFO_SOCIETE_MAIL_ALIASES);
                $from = trim($tmp[($reg[1] - 1)]);
            } elseif (preg_match('/senderprofile_(\d+)_(\d+)/', $fromtype, $reg)) {
                $sql = 'SELECT rowid, label, email FROM ' . MAIN_DB_PREFIX . 'c_email_senderprofile WHERE rowid = ' . (int) $reg[1];
                $resql = $db->query($sql);
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    $from = dol_string_nospecial($obj->label, ' ', array(",")) . ' <' . $obj->email . '>';
                }
            } else {
                $from = dol_string_nospecial(GETPOST('fromname'), ' ', array(",")) . ' <' . GETPOST('frommail') . '>';
            }

            $replyto = dol_string_nospecial(GETPOST('replytoname'), ' ', array(",")) . ' <' . GETPOST('replytomail') . '>';
            $message = GETPOST('message', 'none');
            $subject = GETPOST('subject', 'none');

            // Make a change into HTML code to allow to include images from medias directory with an external reabable URL.
            // <img alt="" src="/dolibarr_dev/htdocs/viewimage.php?modulepart=medias&amp;entity=1&amp;file=image/ldestailleur_166x166.jpg" style="height:166px; width:166px" />
            // become
            // <img alt="" src="'.$urlwithroot.'viewimage.php?modulepart=medias&amp;entity=1&amp;file=image/ldestailleur_166x166.jpg" style="height:166px; width:166px" />
            $message = preg_replace('/(<img.*src=")[^\"]*viewimage\.php([^\"]*)modulepart=medias([^\"]*)file=([^\"]*)("[^\/]*\/>)/', '\1' . $urlwithroot . '/viewimage.php\2modulepart=medias\3file=\4\5', $message);

            $sendtobcc = GETPOST('sendtoccc');
            // Autocomplete the $sendtobcc
            // $autocopy can be MAIN_MAIL_AUTOCOPY_PROPOSAL_TO, MAIN_MAIL_AUTOCOPY_ORDER_TO, MAIN_MAIL_AUTOCOPY_INVOICE_TO, MAIN_MAIL_AUTOCOPY_SUPPLIER_PROPOSAL_TO...
            if (!empty($autocopy)) {
                $sendtobcc .= (empty($conf->global->$autocopy) ? '' : (($sendtobcc ? ", " : "") . $conf->global->$autocopy));
            }

            $deliveryreceipt = GETPOST('deliveryreceipt');

            if ($action == 'send' || $action == 'relance') {
                $actionmsg2 = $langs->transnoentities('MailSentBy') . ' ' . CMailFile::getValidAddress($from, 4, 0, 1) . ' ' . $langs->transnoentities('To') . ' ' . CMailFile::getValidAddress($sendto, 4, 0, 1);
                if ($message) {
                    $actionmsg = $langs->transnoentities('MailFrom') . ': ' . dol_escape_htmltag($from);
                    $actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('MailTo') . ': ' . dol_escape_htmltag($sendto));
                    if ($sendtocc)
                        $actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('Bcc') . ": " . dol_escape_htmltag($sendtocc));
                    $actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('MailTopic') . ": " . $subject);
                    $actionmsg = dol_concatdesc($actionmsg, $langs->transnoentities('TextUsedInTheMessageBody') . ":");
                    $actionmsg = dol_concatdesc($actionmsg, $message);
                }
            }

            // Create form object
            include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
            $formmail = new FormMail($db);
            $formmail->trackid = $trackid;      // $trackid must be defined

            $attachedfiles = $formmail->get_attached_files();
            $filepath = $attachedfiles['paths'];
            $filename = $attachedfiles['names'];
            $mimetype = $attachedfiles['mimes'];

            // Make substitution in email content
            $substitutionarray = getCommonSubstitutionArray($langs, 0, null, $object);
            $substitutionarray['__EMAIL__'] = $sendto;
            $substitutionarray['__CHECK_READ__'] = (is_object($object) && is_object($object->thirdparty)) ? '<img src="' . DOL_MAIN_URL_ROOT . '/public/emailing/mailing-read.php?tag=' . $object->thirdparty->tag . '&securitykey=' . urlencode($conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY) . '" width="1" height="1" style="width:1px;height:1px" border="0"/>' : '';

            $parameters = array('mode' => 'formemail');
            complete_substitutions_array($substitutionarray, $langs, $object, $parameters);

            $subject = make_substitutions($subject, $substitutionarray);
            $message = make_substitutions($message, $substitutionarray);

            if (method_exists($object, 'makeSubstitution')) {
                $subject = $object->makeSubstitution($subject);
                $message = $object->makeSubstitution($message);
            }

            // Send mail (substitutionarray must be done just before this)
            if (empty($sendcontext))
                $sendcontext = 'standard';
            $mailfile = new CMailFile($subject, $sendto, $from, $message, $filepath, $mimetype, $filename, $sendtocc, $sendtobcc, $deliveryreceipt, -1, '', '', $trackid, '', $sendcontext);

            if ($mailfile->error) {
                setEventMessages($mailfile->error, $mailfile->errors, 'errors');
                $action = 'presend';
            } else {
                $result = $mailfile->sendfile();
                if ($result) {

                    // Initialisation of datas of object to call trigger
                    if (is_object($object)) {
                        if (empty($actiontypecode))
                            $actiontypecode = 'AC_OTH_AUTO'; // Event insert into agenda automatically

                        $object->socid = $sendtosocid;    // To link to a company
                        $object->sendtoid = $sendtoid;    // To link to contacts/addresses. This is an array.
                        $object->actiontypecode = $actiontypecode; // Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
                        $object->actionmsg = $actionmsg;      // Long text
                        $object->actionmsg2 = $actionmsg2;     // Short text
                        $object->trackid = $trackid;
                        $object->fk_element = $object->id;
                        $object->elementtype = $object->element;
                        if (is_array($attachedfiles) && count($attachedfiles) > 0) {
                            $object->attachedfiles = $attachedfiles;
                        }
                        if (is_array($sendtouserid) && count($sendtouserid) > 0 && !empty($conf->global->MAIN_MAIL_ENABLED_USER_DEST_SELECT)) {
                            $object->sendtouserid = $sendtouserid;
                        }

                        // Call of triggers
                        if (!empty($trigger_name)) {
                            include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                            $interface = new Interfaces($db);
                            $result = $interface->run_triggers($trigger_name, $object, $user, $langs, $conf);
                            if ($result < 0) {
                                setEventMessages($interface->error, $interface->errors, 'errors');
                            }
                        }
                    }

                    // Redirect here
                    // This avoid sending mail twice if going out and then back to page
                    $mesg = $langs->trans('MailSuccessfulySent', $mailfile->getValidAddress($from, 2), $mailfile->getValidAddress($sendto, 2));
                    setEventMessages($mesg, null, 'mesgs');

                    $moreparam = '';
                    if (isset($paramname2) || isset($paramval2))
                        $moreparam .= '&' . ($paramname2 ? $paramname2 : 'mid') . '=' . $paramval2;
                    header('Location: ' . $_SERVER["PHP_SELF"] . '?' . ($paramname ? $paramname : 'id') . '=' . (is_object($object) ? $object->id : '') . $moreparam);
                    exit;
                } else {
                    $langs->load("other");
                    $mesg = '<div class="error">';
                    if ($mailfile->error) {
                        $mesg .= $langs->trans('ErrorFailedToSendMail', $from, $sendto);
                        $mesg .= '<br>' . $mailfile->error;
                    } else {
                        $mesg .= 'No mail sent. Feature is disabled by option MAIN_DISABLE_ALL_MAILS';
                    }
                    $mesg .= '</div>';

                    setEventMessages($mesg, null, 'warnings');
                    $action = 'presend';
                }
            }
        } else {
            $langs->load("errors");
            setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("MailTo")), null, 'warnings');
            dol_syslog('Try to send email with no recipient defined', LOG_WARNING);
            $action = 'presend';
        }
    } else {
        $langs->load("other");
        setEventMessages($langs->trans('ErrorFailedToReadObject', $object->element), null, 'errors');
        dol_syslog('Failed to read data of object id=' . $object->id . ' element=' . $object->element);
        $action = 'presend';
    }
}

llxHeader('', 'Gift', '');

// Example : Adding jquery code
//print '<script type="text/javascript" language="javascript">
//jQuery(document).ready(function() {
//	function init_myfunc()
//	{
//		jQuery("#myid").removeAttr(\'disabled\');
//		jQuery("#myid").attr(\'disabled\',\'disabled\');
//	}
//	init_myfunc();
//	jQuery("#mybutton").click(function() {
//		init_myfunc();
//	});
//});
//</script>';
// Part to create
if ($action == 'create') {
    print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Gift")));

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';

    dol_fiche_head(array(), '');

    print '<table class="border centpercent">' . "\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_add.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_add.tpl.php';

    print '</table>' . "\n";

    dol_fiche_end();

    print '<input type="hidden" name="sign" id="sign_value" value="">';

    print '<div class="center">';
    print '<input type="submit" class="button" name="add" value="' . dol_escape_htmltag($langs->trans("Create")) . '">';
    print '&nbsp; ';
    print '<input type="' . ($backtopage ? "submit" : "button") . '" class="button" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '"' . ($backtopage ? '' : ' onclick="javascript:history.go(-1)"') . '>'; // Cancel for create does not post form if we don't know the backtopage
    print '</div>';

    print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
    print load_fiche_titre($langs->trans("Gift"));

    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    print '<input type="hidden" name="id" value="' . $object->id . '">';

    dol_fiche_head();

    print '<table class="border centpercent">' . "\n";

    // Common attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

    print '</table>';

    dol_fiche_end();

    print '<input type="hidden" name="sign" id="sign_value" value="' . $object->sign . '">';

    print '<div class="center"><input type="submit" class="button" name="save" value="' . $langs->trans("Save") . '">';
    print ' &nbsp;';
    print '<input type="' . ($backtopage ? "submit" : "button") . '" class="button" name="cancel" value="' . dol_escape_htmltag($langs->trans("Cancel")) . '"' . ($backtopage ? '' : ' onclick="javascript:history.go(-1)"') . '>'; // Cancel for create does not post form if we don't know the backtopage
    print '</div>';

    print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create' && $action != 'add'))) {
    $res = $object->fetch_optionals($object->id, $extralabels);

    $head = giftPrepareHead($object);
    dol_fiche_head($head, 'card', $langs->trans("Gift"), -1, 'gift@giftmodule');

    $formconfirm = '';

    // Confirmation to delete
    if ($action == 'delete') {
        $formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('DeleteGift'), $langs->trans('ConfirmDeleteGift'), 'confirm_delete', '', 0, 1);
    }

    if (!$formconfirm) {
        $parameters = array('lineid' => $lineid);
        $reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
        if (empty($reshook))
            $formconfirm .= $hookmanager->resPrint;
        elseif ($reshook > 0)
            $formconfirm = $hookmanager->resPrint;
    }

    // Print form confirm
    print $formconfirm;

    // Object card
    // ------------------------------------------------------------
    $linkback = '<a href="' . dol_buildpath('/giftmodule/gift_list.php', 1) . '?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

    $morehtmlref = '<div class="refidno">';
    $morehtmlref .= '</div>';

    dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<div class="underbanner clearboth"></div>';
    print '<table class="border centpercent">' . "\n";

    // Common attributes
    //$keyforbreak='fieldkeytoswithonsecondcolumn';
    include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

    // Other attributes
    include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

    print '</table>';
    print '</div>';
    print '</div>';
    print '</div>';

    print '<div class="clearboth"></div><br>';

    dol_fiche_end();

    // Remove file in doc form
    if ($action == 'remove_file') {
        $object = new Gift($db, 0, $_GET['id']);
        if ($object->fetch($id)) {
            require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

            $object->fetch_thirdparty();

            $langs->load("other");
            $upload_dir = $conf->giftmodule->dir_output;
            $file = $upload_dir . '/' . GETPOST('file');
            $ret = dol_delete_file($file, 0, 0, 0, $object);
            if ($ret)
                setEventMessages($langs->trans("FileWasRemoved", GETPOST('urlfile')), null, 'mesgs');
            else
                setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('urlfile')), null, 'errors');
            $action = '';
        }
    }

    /*
     * Build pdf document
     */
    if ($action == 'builddoc') {

        // set the create document permission
        $permissioncreate = $user->rights->giftmodule->create;

//        error_reporting(E_ALL);
        include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

        dol_syslog("builddoc");

        // Define output language
        $result = $object->generateDocument("test", $langs);
        if ($result <= 0) {
            dol_print_error($db, $result);
            exit;
        }
    }

    print '<div class="fichecenter"><div class="fichehalfleft">';
    /*
     * Documents generes
     */
    $filename = dol_sanitizeFileName($object->id);
    $filedir = $conf->giftmodule->dir_output . "/" . dol_sanitizeFileName($object->id);
    $urlsource = $_SERVER['PHP_SELF'] . '?id=' . $object->id;
    $genallowed = (($object->status == 1 || $user->admin) && $user->rights->giftmodule->read);
    $delallowed = $user->rights->giftmodule->create;
    print $formfile->showdocuments('giftmodule', $filename, $filedir, $urlsource, $genallowed, $delallowed, 'gift');

    print '</div></div>';

    // Buttons for actions
    if ($action != 'presend' && $action != 'editline') {
        print '<div class="tabsAction">' . "\n";
        $parameters = array();
        $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
        if ($reshook < 0)
            setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

        if (empty($reshook)) {
            // Send
            if ($user->rights->giftmodule->sendmail) {
                $mail = $object->mail;
                if ($object->fk_soc != 0) {
                    $company = new Societe($db);
                    $result = $company->fetch($gift->fk_soc);
                    if ($result == 1)
                        $mail = $company->email;
                }
                print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init&sendto' . $mail . '#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>' . "\n";
            } else {
                print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('SendPDFMail') . '</a>' . "\n";
            }

            if ($user->rights->giftmodule->write) {
                print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=edit">' . $langs->trans("Modify") . '</a>' . "\n";
            } else {
                print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Modify') . '</a>' . "\n";
            }

            if ($user->rights->giftmodule->create) {
                if ($object->status == 1) {
                    print '<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=disable">' . $langs->trans("Disable") . '</a>' . "\n";
                } else {
                    print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=enable">' . $langs->trans("Enable") . '</a>' . "\n";
                }
            }

            if ($user->rights->giftmodule->delete) {
                print '<a class="butActionDelete" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=delete">' . $langs->trans('Delete') . '</a>' . "\n";
            } else {
                print '<a class="butActionRefused" href="#" title="' . dol_escape_htmltag($langs->trans("NotEnoughPermissions")) . '">' . $langs->trans('Delete') . '</a>' . "\n";
            }
        }
        print '</div>' . "\n";
    }

    // custom mail section
    if ($action == 'presend') {

        $diroutput = $conf->societe->dir_output;
        $trackid = 'thi' . $object->id;

        $langs->load("mails");

        $titreform = 'SendMail';

        $object->fetch_projet();

        if (!in_array($object->element, array('societe', 'user', 'member'))) {
            // TODO get also the main_lastdoc field of $object. If not found, try to guess with following code

            $ref = dol_sanitizeFileName($object->ref);
            include_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
            $fileparams = dol_most_recent_file($diroutput . '/' . $ref, preg_quote($ref, '/') . '[^\-]+');
            //
            if ($object->element == 'invoice_supplier') {
                $fileparams = dol_most_recent_file($diroutput . '/' . get_exdir($object->id, 2, 0, 0, $object, $object->element) . $ref, preg_quote($ref, '/') . '([^\-])+');
            }

            $file = $fileparams['fullname'];
        }

        // Define output language
        $outputlangs = $langs;
        $newlang = '';
        if ($conf->global->MAIN_MULTILANGS && empty($newlang) && !empty($_REQUEST['lang_id'])) {
            $newlang = $_REQUEST['lang_id'];
        }
        if ($conf->global->MAIN_MULTILANGS && empty($newlang)) {
            $newlang = $object->thirdparty->default_lang;
        }

        if (!empty($newlang)) {
            $outputlangs = new Translate('', $conf);
            $outputlangs->setDefaultLang($newlang);
            $outputlangs->loadLangs(array('commercial', 'bills', 'orders', 'contracts', 'members', 'propal', 'products', 'supplier_proposal', 'interventions'));
        }

        print '<div id="formmailbeforetitle" name="formmailbeforetitle"></div>';
        print '<div class="clearboth"></div>';
        print '<br>';
        print load_fiche_titre($langs->trans($titreform));

        dol_fiche_head('');

        // Create form for email
        include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
        $formmail = new FormMail($db);

        $formmail->param['langsmodels'] = (empty($newlang) ? $langs->defaultlang : $newlang);
        $formmail->fromtype = (GETPOST('fromtype') ? GETPOST('fromtype') : (!empty($conf->global->MAIN_MAIL_DEFAULT_FROMTYPE) ? $conf->global->MAIN_MAIL_DEFAULT_FROMTYPE : 'user'));

        if ($formmail->fromtype === 'user') {
            $formmail->fromid = $user->id;
        }
        $formmail->trackid = $trackid;
        if (!empty($conf->global->MAIN_EMAIL_ADD_TRACK_ID) && ($conf->global->MAIN_EMAIL_ADD_TRACK_ID & 2)) { // If bit 2 is set
            include DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
            $formmail->frommail = dolAddEmailTrackId($formmail->frommail, $trackid);
        }
        $formmail->withfrom = 1;

        // Fill list of recipient with email inside <>.
        $liste = array();
        if ($object->element == 'expensereport') {
            $fuser = new User($db);
            $fuser->fetch($object->fk_user_author);
            $liste['thirdparty'] = $fuser->getFullName($langs) . " <" . $fuser->email . ">";
        } elseif ($object->element == 'societe') {
            foreach ($object->thirdparty_and_contact_email_array(1) as $key => $value) {
                $liste[$key] = $value;
            }
        } elseif ($object->element == 'user' || $object->element == 'member') {
            $liste['thirdparty'] = $object->getFullName($langs) . " <" . $object->email . ">";
        } else {
            if (is_object($object->thirdparty)) {
                foreach ($object->thirdparty->thirdparty_and_contact_email_array(1) as $key => $value) {
                    $liste[$key] = $value;
                }
            }
        }

        $mail = $object->mail;
        if ($object->fk_soc != 0) {
            $company = new Societe($db);
            $result = $company->fetch($object->fk_soc);
            if ($result == 1)
                if ($company->email != '')
                    $mail = $company->email;
                else
                    $mail = 'Anonyme';
        }
        $donorName = $object->giver;
        if ($object->fk_soc != 0) {
            $company = new Societe($db);
            $result = $company->fetch($object->fk_soc);
            if ($result == 1)
                $donorName = $company->nom;
            else
                $donorName = "Anonyme ";
        }
        $formmail->withto = $mail;
        $formmail->withtocc = 0;
        $formmail->withtoccc = 0;
        $formmail->withtopic = $conf->global->GIFTMODULE_MAIL_OBJECT;
        $formmail->withbody = $conf->global->GIFTMODULE_MAIL_TEXT;
        $formmail->withfile = 1; //only show atteched files, no ability to add new files
        $formmail->withdeliveryreceipt = 1;
        $formmail->withcancel = 1;

        if (!isset($arrayoffamiliestoexclude))
            $arrayoffamiliestoexclude = null;

        // Make substitution in email content
        $substitutionarray = getCommonSubstitutionArray($outputlangs, 0, $arrayoffamiliestoexclude, $object);
        $substitutionarray['__CHECK_READ__'] = (is_object($object) && is_object($object->thirdparty)) ? '<img src="' . DOL_MAIN_URL_ROOT . '/public/emailing/mailing-read.php?tag=' . $object->thirdparty->tag . '&securitykey=' . urlencode($conf->global->MAILING_EMAIL_UNSUBSCRIBE_KEY) . '" width="1" height="1" style="width:1px;height:1px" border="0"/>' : '';
        $substitutionarray['__PERSONALIZED__'] = ''; // deprecated
        $substitutionarray['__CONTACTCIVNAME__'] = '';
        // custom substitution
        $substitutionarray['PredefinedMailContent'] = '';
        $substitutionarray['__DONATOR_NAME__'] = $donorName;
        $substitutionarray['__MAIN_INFO_SOCIETE_NOM__'] = $conf->global->MAIN_INFO_SOCIETE_NOM;
        $parameters = array(
            'mode' => 'formemail'
        );
        complete_substitutions_array($substitutionarray, $outputlangs, $object, $parameters);

        // Tableau des substitutions
        $formmail->substit = $substitutionarray;

        // Tableau des parametres complementaires
        $formmail->param['action'] = 'send';
        $formmail->param['models'] = $conf->global->GIFTMODULE_MAIL_TEXT;
        $formmail->param['id'] = $object->id;
        $formmail->param['returnurl'] = $_SERVER["PHP_SELF"] . '?id=' . $object->id;

        if ($handle = opendir(DOL_DATA_ROOT . '/giftmodule/' . $object->id . '/')) {
            while (false !== ($entry = readdir($handle))) { // list object files
                if ($entry != "." && $entry != "..") { // filter
                    $file = DOL_DATA_ROOT . '/giftmodule/' . $object->id . '/' . $entry;
                    if (is_readable($file)) {
                        $formmail->param['fileinit'][] = $file; // add attached file path
                    }
                }
            }
        }
        //hack
        $object->element = 'user';

        // Show form
        print $formmail->get_form();

        dol_fiche_end();
    }
}

// End of page
llxFooter();
$db->close();
