<?php

/* Copyright (C) 2003		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2005-2006	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2012       Marcos García           <marcosgdf@gmail.com>
 * Copyright (C) 2014-2015  Alexandre Spangaro		<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2015  		Benoit Bruchard			<benoitb21@gmail.com>
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
 * 	\file       htdocs/core/modules/dons/html_cerfafr.modules.php
 * 	\ingroup    don
 * 	\brief      Form of donation
 */

require_once DOL_DOCUMENT_ROOT . '/custom/giftmodule/class/gift.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/giftmodule/core/modules/giftmodule/modules_giftmodule.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';

/**
 * 	Class to generate document for subscriptions
 */
class test extends ModelePDFGiftmodule {

    /**
     *  Constructor
     *
     *  @param      DoliDb		$db      Database handler
     */
    function __construct($db) {
        global $conf, $langs;

        $this->db = $db;
        $this->name = "test";
        $this->description = 'Cert don matière';

        // Dimension page for size A4
        $this->type = 'pdf';
    }

    /**
     * 	Return if a module can be used or not
     *
     *  @return	boolean     true if module can be used
     */
    function isEnabled() {
        return true;
    }

    /**
     *  Write the object to document file to disk
     *
     *  @param      Don		$gift	        Donation object
     *  @param      Translate	$outputlangs    Lang object for output language
     *  @param	string		$currency	Currency code
     *  @return	int             		>0 if OK, <0 if KO
     */
    function write_file($gift, $outputlangs) {
        global $user, $conf, $langs, $mysoc;

        $now = dol_now();
        $id = (!is_object($gift) ? $gift : '');
        $prefix = "justificatif-";

        if (!is_object($outputlangs))
            $outputlangs = $langs;

        $outputlangs->load("main");
        $outputlangs->load("dict");
        $outputlangs->load("companies");
        $outputlangs->load("bills");
        $outputlangs->load("products");
        $outputlangs->load("donations");

        if (!empty($conf->giftmodule->dir_output)) {
            // Definition of the object don (for upward compatibility)
            if (!is_object($gift)) {
                $gift = new Gift($this->db);
                $ret = $gift->fetch($id);
                $id = $gift->rowid;
            }

            // Definition of $dir and $file
            if (!empty($gift->specimen)) {
                $dir = $conf->giftmodule->dir_output;
                $file = $dir . "/SPECIMEN.html";
            } else {
                $giftref = dol_sanitizeFileName($gift->id);
                $dir = $conf->giftmodule->dir_output . "/" . $giftref;
                $file = $dir . "/" . $prefix . $giftref . "." . $this->type;
            }

            if (!file_exists($dir)) {
                if (dol_mkdir($dir) < 0) {
                    $this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
                    return -1;
                }
            }

            if (file_exists($dir)) {
                $formclass = new Form($this->db);

                // Define contents
                $giftmodel = DOL_DOCUMENT_ROOT . "/custom/giftmodule/core/modules/giftmodule/pdf_test.html";
                $form = implode('', file($giftmodel));

                $logo = $conf->global->MAIN_INFO_SOCIETE_LOGO_SMALL;
                if (!empty($logo) && is_readable($conf->mycompany->dir_output . '/logos/thumbs/' . $logo)) {
                    $logopath = $conf->mycompany->dir_output . '/logos/thumbs/' . $logo;
                } else {
                    $logopath = dol_buildpath(DOL_URL_ROOT . '/theme/dolibarr_logo.png', 0);
                }
                $logotype = pathinfo($logopath, PATHINFO_EXTENSION);
                $logodata = file_get_contents($logopath);
                $base64logo = 'data:image/' . $logotype . ';base64,' . base64_encode($logodata);

                $form = str_replace('__LOGO_DATA__', $base64logo, $form);
                $form = str_replace('__REF__', $id, $form);
                $form = str_replace('__DATE__', dol_print_date($gift->date, 'day', false, $outputlangs), $form);
                $form = str_replace('__NOW__', dol_print_date($now, 'day', false, $outputlangs), $form);

                $form = str_replace('__MAIN_INFO_SOCIETE_NOM__', $mysoc->name, $form);
                $form = str_replace('__GIFTMODULE_PDF_PHONE__', $conf->global->GIFTMODULE_PDF_PHONE, $form);
                $form = str_replace('__GIFTMODULE_PDF_SIRET__', $conf->global->GIFTMODULE_PDF_SIRET, $form);
                $form = str_replace('__GIFTMODULE_PDF_APE__', $conf->global->GIFTMODULE_PDF_APE, $form);
                $form = str_replace('__GIFTMODULE_THANKS_TEXT__', $conf->global->GIFTMODULE_THANKS_TEXT, $form);

                $donorName = $gift->giver;
                if ($gift->fk_soc > 0) {
                    $company = new Societe($this->db);
                    $result = $company->fetch($gift->fk_soc);
                    if ($result == 1)
                        $donorName = $company->nom;
                    else
                        $donorName = "Anonyme";
                }
                $form = str_replace('__DONATOR_NAME__', $donorName, $form);
                $form = str_replace('__DONATOR_SIGN__', $gift->sign, $form);

                $form = str_replace('__GIFT_ID__', $gift->rowid, $form);
                $desc = nl2br($gift->description);
                $form = str_replace('__GIFT_DESC__', $desc, $form);
                $form = str_replace('__GIFT_PLACE__', $gift->address, $form);

                $form = str_replace('__DonationMessage__', $conf->global->DONATION_MESSAGE, $form);

                // pdf convert if needed
                if ($this->type == 'pdf') {
                    include_once DOL_DOCUMENT_ROOT . '/custom/html2pdf/core/modules/modHtml2Pdf.class.php';
                    $form = modHtml2Pdf::html2pdf($form);
                }

                // Save file on disk
                dol_syslog($this->type . "_" . $this->name . "::write_file " . $file);
                $handle = fopen($file, "w");
                fwrite($handle, $form);
                fclose($handle);

                if (!empty($conf->global->MAIN_UMASK))
                    @chmod($file, octdec($conf->global->MAIN_UMASK));

                $this->result = array('fullpath' => $file);

                return 1;
            }
            else {
                $this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
                return 0;
            }
        } else {
            $this->error = $langs->trans("ErrorConstantNotDefined", "DON_OUTPUTDIR");
            return 0;
        }
    }

}
