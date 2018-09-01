<?php

/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2018 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    giftmodule/admin/setup.php
 * \ingroup giftmodule
 * \brief   GiftModule setup page.
 */
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
if (!$res && file_exists("../../main.inc.php"))
    $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include("../../../main.inc.php");
if (!$res)
    die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/giftmodule.lib.php';
// Translations
$langs->loadLangs(array("admin", "giftmodule@giftmodule"));

// Access control
if (!$user->admin)
    accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$arrayofparameters = array(
    //examples
//    'GIFTMODULE_PDF_PHONE' => array('css' => 'minwidth200'),
//    'GIFTMODULE_PDF_SIRET' => array('css' => 'minwidth200'),
//    'GIFTMODULE_SIGN' => array('type' => 'sign'),
    'GIFTMODULE_AUTO_GENPDF' => array('type' => 'select', 'values' => array('yes', 'no'), 'default' => 'no'),
//    'GIFTMODULE_AUTO_SENDMAIL' => array('type' => 'select', 'values' => array('yes', 'no'), 'default' => 'no'),
    'GIFTMODULE_MAIL_OBJECT' => array('css' => 'minwidth200'),
    'GIFTMODULE_MAIL_TEXT' => array('type' => 'textarea', 'options' => array('cols' => 100, 'rows' => 6)),
    'GIFTMODULE_THANKS_TEXT' => array('type' => 'textarea', 'options' => array('cols' => 80, 'rows' => 2))
);

/*
 * Actions
 */

include DOL_DOCUMENT_ROOT . '/core/actions_setmoduleoptions.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';

/*
 * View
 */

$page_name = "GiftModuleSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_giftmodule@giftmodule');

// Configuration header
$head = giftmoduleAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "giftmodule@giftmodule");

// Setup page goes here
echo $langs->trans("GiftModuleSetupPage");

if ($action == 'edit') {
    print '<form method="POST" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
    print '<input type="hidden" name="action" value="update">';

    print '<table class="noborder" width="100%">';

    print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';

    foreach ($arrayofparameters as $key => $val) {
        print '<tr class="oddeven">';
        print '<td>' . $form->textwithpicto($langs->trans($key), $langs->trans($key . 'Tooltip')) . '</td>';
        if ($val['type'] == "sign") {
            print '<td>';
            $size = [256, 256];
            print '<label for="clear-sign" class="custom-file-upload" id="clear_sign"><i class="fa fa-trash"></i> Clear</label>'
                    . '<input type="button" class="button" name="clear-sign" value="Clear" style="display: none"/> '
                    . '<label for="upload_sign" class="custom-file-upload"><i class="fa fa-cloud-upload"></i> Upload</label>'
                    . '<input type="hidden" name="sign" id="sign_value" value="' . $conf->global->$key . '">'
                    . '<br><br>'
                    . '<canvas class="flat maxwidthonsmartphone" name="sign" id="sign" style="width: ' . $size[0] . 'px; height: ' . $size[1] . 'px;" width="' . $size[0] . '" height="' . $size[1] . '"></canvas>'
                    . '';
            print '<input type="hidden" name="sign" id="sign_value" value="' . $conf->global->$key . '">';
            print '</td>';
        } else
        if ($val['type'] == "select") {
            print '<td colspan="2">';
            print '<select name="' . $key . '">';
            foreach ($val['values'] as $value) {
                $selected = $conf->global->$key == $value ? "selected" : ($val['default'] == $value ? "" : "selected");
                print '<option value="' . $value . '"' . $selected . '>' . $langs->trans($value) . '</option>';
            }
            print '</select>';
            print '</td>';
        } elseif ($val['type'] == "textarea") {
            print '<td colspan="2">';
            print '<textarea name="' . $key . '" class="flat ckeditor" rows="' . $val['options']['rows'] . '" cols="' . $val['options']['cols'] . '">' . $conf->global->$key . '</textarea>';
            print '</td>';
        } else
            print '<td><input name="' . $key . '" type=text value="' . $conf->global->$key . '"></td>';
        print '</tr>';
    }

    print '</table>';

    print '<br><div class="center">';
    print '<input class="button" type="submit" value="' . $langs->trans("Save") . '">';
    print '</div>';

    print '</form>';
    print '<br>';
} else {
    print '<table class="noborder" width="100%">';
    print '<tr class="liste_titre">'
            . '<td class="titlefield">' . $langs->trans("Parameter") . '</td>'
            . '<td>' . $langs->trans("Value") . '</td>'
            . '</tr>';

    foreach ($arrayofparameters as $key => $val) {
        print '<tr class="oddeven">';
        print '<td>' . $form->textwithpicto($langs->trans($key), $langs->trans($key . 'Tooltip')) . '</td>';
        if ($val['type'] == "select") {
            print '<td colspan="2">';
            print '<select id="' . $key . '" disabled>';
            foreach ($val['values'] as $value) {
                $selected = $conf->global->$key == $value ? "selected" : ($value == $val['default'] ? "" : "selected");
                print '<option value="' . $value . '"' . $selected . '>' . $langs->trans($value) . '</option>';
            }
            print '</select>';
            print '</td>';
        } elseif ($val['type'] == "textarea") {
            print '<td colspan="2">';
//            print '<textarea name="' . $key . '" rows="' . $val['options']['rows'] . '" cols="' . $val['options']['cols'] . '" readonly>' . $conf->global->$key . '</textarea>';
            print $conf->global->$key;
            print '</td>';
        } else
            print '<td>' . $conf->global->$key . '</td>';
        print '</tr>';
    }

    print '</table>';

    print '<div class="tabsAction">';
    print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=edit">' . $langs->trans("Modify") . '</a>';
    print '</div>';
}

// Page end
dol_fiche_end();

llxFooter();
$db->close();
