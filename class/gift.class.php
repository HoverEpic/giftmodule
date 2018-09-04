<?php

/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/gift.class.php
 * \ingroup     giftmodule
 * \brief       This file is a CRUD class file for Gift (Create/Read/Update/Delete)
 */
// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

/**
 * Class for Gift
 */
class Gift extends CommonObject {

    /**
     * @var string ID to identify managed object
     */
    public $element = 'gift';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'giftmodule_gift';

    /**
     * @var int  Does gift support multicompany module ? 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
     */
    public $ismultientitymanaged = 0;

    /**
     * @var int  Does gift support extrafields ? 0=No, 1=Yes
     */
    public $isextrafieldmanaged = 1;

    /**
     * @var string String with name of icon for gift. Must be the part after the 'object_' into object_gift.png
     */
    public $picto = 'gift@giftmodule';

    /**
     *  'type' if the field format.
     *  'label' the translation key.
     *  'enabled' is a condition when the field must be managed.
     *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only. Using a negative value means field is not shown by default on list but can be selected for viewing)
     *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     *  'index' if we want an index in database.
     *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     *  'position' is the sort order of field.
     *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     *  'help' is a string visible as a tooltip on field
     *  'comment' is not used. You can store here any text of your choice. It is not used by application.
     *  'default' is a default value for creation (can still be replaced by the global setup of default values)
     *  'showoncombobox' if field must be shown into the label of combobox
     */
    // BEGIN MODULEBUILDER PROPERTIES
    /**
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields = array(
        'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -1, 'position' => 1, 'notnull' => 1, 'index' => 1, 'comment' => "Id",),
        'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => 1, 'visible' => -1, 'position' => 20, 'notnull' => 1, 'index' => 1,),
        'label' => array('type' => 'varchar(255)', 'label' => 'Label', 'enabled' => 1, 'visible' => 1, 'position' => 30, 'notnull' => -1, 'searchall' => 1, 'help' => "What's given",),
        'giver' => array('type' => 'varchar(255)', 'label' => 'Giver', 'enabled' => 1, 'visible' => 1, 'position' => 31, 'notnull' => -1, 'searchall' => 1, 'help' => "Giver's name",),
        'address' => array('type' => 'varchar(255)', 'label' => 'Address', 'enabled' => 1, 'visible' => 1, 'position' => 31, 'notnull' => -1, 'searchall' => 1,),
        'mail' => array('type' => 'varchar(255)', 'label' => 'Mail', 'enabled' => 1, 'visible' => 1, 'position' => 31, 'notnull' => -1, 'searchall' => 1, 'help' => "Giver's mail",),
        'fk_soc' => array('type' => 'integer:Societe:societe/class/societe.class.php', 'label' => 'ThirdParty', 'enabled' => 1, 'visible' => 1, 'position' => 50, 'notnull' => -1, 'default' => '-1', 'index' => 1, 'searchall' => 1, 'help' => "LinkToThirparty",),
        'description' => array('type' => 'text', 'label' => 'Description', 'enabled' => 1, 'visible' => -1, 'position' => 60, 'notnull' => -1, 'help' => "Description",),
        'weight' => array('type' => 'double', 'label' => 'Weight', 'enabled' => 1, 'visible' => -1, 'position' => 70, 'notnull' => -1, 'help' => "Weight of the thing",),
        'date' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => 1, 'visible' => 1, 'position' => 500, 'notnull' => 1,),
        'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => 1, 'visible' => -2, 'position' => 501, 'notnull' => 1, 'default' => 'CURRENT_TIMESTAMP'),
        'fk_user_creat' => array('type' => 'integer', 'label' => 'UserAuthor', 'enabled' => 1, 'visible' => -2, 'position' => 510, 'notnull' => 1,),
        'fk_user_modif' => array('type' => 'integer', 'label' => 'UserModif', 'enabled' => 1, 'visible' => -2, 'position' => 511, 'notnull' => -1,),
        'import_key' => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => 1, 'visible' => -2, 'position' => 1000, 'notnull' => -1,),
        'status' => array('type' => 'integer', 'label' => 'Status', 'enabled' => 1, 'visible' => 1, 'position' => 1000, 'notnull' => 1, 'default' => '1', 'index' => 1, 'arrayofkeyval' => array('0' => 'Draft', '1' => 'Active', '-1' => 'Cancel')),
        'sign' => array('type' => 'blob', 'label' => 'Signature', 'enabled' => 1, 'visible' => -1, 'position' => 1100, 'notnull' => -1, 'comment' => "Giver signature",),
    );
    public $rowid;
    public $entity;
    public $label;
    public $giver;
    public $address;
    public $mail;
    public $fk_soc;
    public $description;
    public $weight;
    public $date;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $import_key;
    public $status;
    public $sign;

    // END MODULEBUILDER PROPERTIES

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db) {
        global $conf;

        $this->db = $db;

        if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID))
            $this->fields['rowid']['visible'] = 0;
        if (empty($conf->multicompany->enabled))
            $this->fields['entity']['enabled'] = 0;
    }

    /**
     * Create object into database
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, Id of created object if OK
     */
    public function create(User $user, $notrigger = false) {
        global $conf, $langs;

        $this->db->begin();

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "giftmodule_gift ";
        $sql .= "(entity, label, giver, address, mail, fk_soc, description, weight, date, fk_user_creat, status, sign)";
        $sql .= " VALUES (";
        $sql .= $this->db->escape($conf->entity) . ", ";
        $sql .= "'" . $this->db->escape($this->label) . "', ";
        $sql .= "'" . $this->db->escape($this->giver) . "', ";
        $sql .= "'" . $this->db->escape($this->address) . "', ";
        $sql .= "'" . $this->db->escape($this->mail) . "', ";
        $sql .= ($this->fk_soc > 0 ? $this->fk_soc : 0) . ", ";
        $sql .= "'" . $this->db->escape($this->description) . "', ";
        $sql .= "" . str_replace(',', '.', $this->db->escape($this->weight ? $this->weight : 0)) . ", ";
        $sql .= "'" . $this->db->idate($this->date) . "', ";
        $sql .= ($this->fk_user_creat > 0 ? $this->fk_user_creat : 0) . ", ";
        $sql .= $this->db->escape($this->status) . ", ";
        $sql .= "'" . $this->db->escape($this->sign) . "'";
        $sql .= ")";

        dol_syslog("Gift::create", LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $id = $this->db->last_insert_id(MAIN_DB_PREFIX . "giftmodule_gift");
            if ($id > 0) {
                $this->id = $id;
                $this->rowid = $id;
                $this->db->commit();
                if ($conf->global->GIFTMODULE_AUTO_GENPDF) {
                    $this->generateDocument('test', $langs);
                }
                return $id;
            } else {
                $this->error = $this->db->lasterror();
                $this->errno = $this->db->lasterrno();
                $this->db->rollback();
                return -2;
            }
        } else {
            $this->error = $this->db->lasterror();
            $this->errno = $this->db->lasterrno();
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id   Id object
     * @param string $ref  Ref
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null) {
        $result = $this->fetchCommon($id, $ref);
        if ($result > 0 && !empty($this->table_element_line))
            $this->fetchLines();
        return $result;
    }

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false) {
        global $conf, $langs;
        $this->db->begin();

        $sql = "UPDATE " . MAIN_DB_PREFIX . "giftmodule_gift SET ";
        $sql .= "label='" . $this->db->escape($this->label) . "', ";
        $sql .= "giver='" . $this->db->escape($this->giver) . "', ";
        $sql .= "address='" . $this->db->escape($this->address) . "', ";
        $sql .= "mail='" . $this->db->escape($this->mail) . "', ";
        $sql .= "fk_soc=" . ($this->fk_soc > 0 ? $this->fk_soc : 0) . ", ";
        $sql .= "description='" . $this->db->escape($this->description) . "', ";
        $sql .= "weight=" . str_replace(',', '.', $this->db->escape($this->weight)) . ", ";
        $sql .= "date='" . $this->db->idate($this->date) . "', ";
        $sql .= "fk_user_modif=" . ($this->fk_user_creat > 0 ? $this->fk_user_creat : 0) . ", ";
        $sql .= "status=" . $this->db->escape($this->status) . ", ";
        $sql .= "sign='" . $this->db->escape($this->sign) . "' ";
        $sql .= "WHERE rowid=" . $this->rowid . "";

        dol_syslog("Gift::update GIFTMODULE_AUTO_GENPDF=" . $conf->global->GIFTMODULE_AUTO_GENPDF, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $this->db->commit();
            return $this->rowid;
        } else {
            $this->error = $this->db->lasterror();
            $this->errno = $this->db->lasterrno();
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Delete object in database
     *
     * @param User $user       User that deletes
     * @param bool $notrigger  false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false) {
        return $this->deleteCommon($user, $notrigger);
    }

    /**
     *  Return a link to the object card (with optionaly the picto)
     *
     * 	@param	int	$withpicto		Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     * 	@param	string	$option			On what the link point to ('nolink', ...)
     *  @param	int  	$notooltip		1=Disable tooltip
     *  @param  string  $morecss            	Add more css on link
     *  @param  int     $save_lastsearch_value  -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
     * 	@return	string				String with URL
     */
    function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1) {
        global $db, $conf, $langs;
        global $dolibarr_main_authentication, $dolibarr_main_demo;
        global $menumanager;

        if (!empty($conf->dol_no_mouse_hover))
            $notooltip = 1;   // Force disable tooltips

        $result = '';
        $companylink = '';

        $label = '<u>' . $langs->trans("Gift") . '</u>';
        $label .= '<br>';
        $label .= '<b>' . $langs->trans('Ref') . ':</b> ' . $this->ref;

        $url = dol_buildpath('/giftmodule/gift_card.php', 1) . '?id=' . $this->id;

        if ($option != 'nolink') {
            // Add param to save lastsearch_values or not
            $add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
            if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"]))
                $add_save_lastsearch_values = 1;
            if ($add_save_lastsearch_values)
                $url .= '&save_lastsearch_values=1';
        }

        $linkclose = '';
        if (empty($notooltip)) {
            if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label = $langs->trans("ShowGift");
                $linkclose .= ' alt="' . dol_escape_htmltag($label, 1) . '"';
            }
            $linkclose .= ' title="' . dol_escape_htmltag($label, 1) . '"';
            $linkclose .= ' class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"';
        } else
            $linkclose = ($morecss ? ' class="' . $morecss . '"' : '');

        $linkstart = '<a href="' . $url . '"';
        $linkstart .= $linkclose . '>';
        $linkend = '</a>';

        $result .= $linkstart;
        if ($withpicto)
            $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="' . (($withpicto != 2) ? 'paddingright ' : '') . 'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
        if ($withpicto != 2)
            $result .= $this->ref;
        $result .= $linkend;
        //if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

        return $result;
    }

    /**
     *  Retourne le libelle du status d'un user (actif, inactif)
     *
     *  @param	int	$mode   0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *  @return	string 		Label of status
     */
    function getLibStatut($mode = 0) {
        return $this->LibStatut($this->status, $mode);
    }

    /**
     *  Return the status
     *
     *  @param	int	$status     Id status
     *  @param  int	$mode       0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return string              Label of status
     */
    static function LibStatut($status, $mode = 0) {
        global $langs;

        if ($mode == 0) {
            $prefix = '';
            if ($status == 1)
                return $langs->trans('Enabled');
            if ($status == 0)
                return $langs->trans('Disabled');
        }
        if ($mode == 1) {
            if ($status == 1)
                return $langs->trans('Enabled');
            if ($status == 0)
                return $langs->trans('Disabled');
        }
        if ($mode == 2) {
            if ($status == 1)
                return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
            if ($status == 0)
                return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
        }
        if ($mode == 3) {
            if ($status == 1)
                return img_picto($langs->trans('Enabled'), 'statut4');
            if ($status == 0)
                return img_picto($langs->trans('Disabled'), 'statut5');
        }
        if ($mode == 4) {
            if ($status == 1)
                return img_picto($langs->trans('Enabled'), 'statut4') . ' ' . $langs->trans('Enabled');
            if ($status == 0)
                return img_picto($langs->trans('Disabled'), 'statut5') . ' ' . $langs->trans('Disabled');
        }
        if ($mode == 5) {
            if ($status == 1)
                return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
            if ($status == 0)
                return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
        }
        if ($mode == 6) {
            if ($status == 1)
                return $langs->trans('Enabled') . ' ' . img_picto($langs->trans('Enabled'), 'statut4');
            if ($status == 0)
                return $langs->trans('Disabled') . ' ' . img_picto($langs->trans('Disabled'), 'statut5');
        }
    }

    /**
     * 	Charge les informations d'ordre info dans l'objet commande
     *
     * 	@param  int	$id    Id of order
     * 	@return	void
     */
//    function info($id) {
//        $sql = 'SELECT rowid, date_creation as datec, tms as datem,';
//        $sql .= ' fk_user_creat, fk_user_modif';
//        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t';
//        $sql .= ' WHERE t.rowid = ' . $id;
//        $result = $this->db->query($sql);
//        if ($result) {
//            if ($this->db->num_rows($result)) {
//                $obj = $this->db->fetch_object($result);
//                $this->id = $obj->rowid;
//                if ($obj->fk_user_author) {
//                    $cuser = new User($this->db);
//                    $cuser->fetch($obj->fk_user_author);
//                    $this->user_creation = $cuser;
//                }
//
//                if ($obj->fk_user_valid) {
//                    $vuser = new User($this->db);
//                    $vuser->fetch($obj->fk_user_valid);
//                    $this->user_validation = $vuser;
//                }
//
//                if ($obj->fk_user_cloture) {
//                    $cluser = new User($this->db);
//                    $cluser->fetch($obj->fk_user_cloture);
//                    $this->user_cloture = $cluser;
//                }
//
//                $this->date_creation = $this->db->jdate($obj->datec);
//                $this->date_modification = $this->db->jdate($obj->datem);
//                $this->date_validation = $this->db->jdate($obj->datev);
//            }
//
//            $this->db->free($result);
//        } else {
//            dol_print_error($this->db);
//        }
//    }

    /**
     * Return HTML string to put an input field into a page
     * Code very similar with showInputField of extra fields
     *
     * @param  array   		$val	       Array of properties for field to show
     * @param  string  		$key           Key of attribute
     * @param  string  		$value         Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
     * @param  string  		$moreparam     To add more parameters on html input tag
     * @param  string  		$keysuffix     Prefix string to add into name and id of field (can be used to avoid duplicate names)
     * @param  string  		$keyprefix     Suffix string to add into name and id of field (can be used to avoid duplicate names)
     * @param  string|int	$morecss       Value for css to define style/length of field. May also be a numeric.
     * @return string
     */
    function showInputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = 0) {

        global $conf, $langs, $form;

        if (!is_object($form)) {
            require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
            $form = new Form($this->db);
        }

        $val = $this->fields[$key];

        $out = '';
        if ($val['type'] == 'blob') {
            $size = [256, 256];
            $out = '<label for="clear-sign" class="custom-file-upload" id="clear_sign"><i class="fa fa-trash"></i> Clear</label>'
                    . '<input type="button" class="button" name="clear-sign" value="Clear" style="display: none"/> '
                    . '<label for="upload_sign" class="custom-file-upload"><i class="fa fa-cloud-upload"></i> Upload</label>'
                    . '<input type="file" id="upload_sign" style="display: none"/>'
                    . '<br><br>'
                    . '<canvas class="flat ' . $morecss . ' maxwidthonsmartphone" name="' . $keyprefix . $key . $keysuffix . '" id="' . $keyprefix . $key . $keysuffix . '" style="width: ' . $size[0] . 'px; height: ' . $size[1] . 'px;" width="' . $size[0] . '" height="' . $size[1] . '"></canvas>'
                    . '';
        }
        return $out . parent::showInputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
    }

    /**
     * Return HTML string to show a field into a page
     * Code very similar with showOutputField of extra fields
     *
     * @param  array   $val            Array of properties of field to show
     * @param  string  $key            Key of attribute
     * @param  string  $value          Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
     * @param  string  $moreparam      To add more parametes on html input tag
     * @param  string  $keysuffix      Prefix string to add into name and id of field (can be used to avoid duplicate names)
     * @param  string  $keyprefix      Suffix string to add into name and id of field (can be used to avoid duplicate names)
     * @param  mixed   $showsize       Value for css to define size. May also be a numeric.
     * @return string
     */
    function showOutputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $showsize = 0) {
        global $conf, $langs, $form;

        if (!is_object($form)) {
            require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
            $form = new Form($this->db);
        }

        $out = '';
        if ($val['type'] == 'blob') {
            $size = [256, 256];
            $out = '<img class="flat ' . $morecss . ' maxwidthonsmartphone" name="' . $keyprefix . $key . $keysuffix . '" id="' . $keyprefix . $key . $keysuffix . '" style="width: ' . $size[0] . 'px; height: ' . $size[1] . 'px;" width="' . $size[0] . '" height="' . $size[1] . '" src="' . $value . '"/>';
            $value = '';
        }
        return $out . parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $showsize);
    }

    /**
     * Initialise object with example values
     * Id must be 0 if object instance is a specimen
     *
     * @return void
     */
    public function initAsSpecimen() {
        $this->initAsSpecimenCommon();
    }

    /**
     * Action executed by scheduler
     * CAN BE A CRON TASK
     *
     * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
     */
    public function doScheduledJob() {
        global $conf, $langs;

        $this->output = '';
        $this->error = '';

        dol_syslog(__METHOD__, LOG_DEBUG);

        // ...

        return 0;
    }

    /**
     * 		Set last model used by doc generator
     *
     * 		@param		User	$user		User object that make change
     * 		@param		string	$modelpdf	Modele name
     * 		@return		int					<0 if KO, >0 if OK
     */
    function setDocModel($user, $modelpdf) {
//        if (!$this->table_element) {
//            dol_syslog(get_class($this) . "::setDocModel was called on objet with property table_element not defined", LOG_ERR);
//            return -1;
//        }
//
//        $newmodelpdf = dol_trunc($modelpdf, 255);
//
//        $sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element;
//        $sql .= " SET model_pdf = '" . $this->db->escape($newmodelpdf) . "'";
//        $sql .= " WHERE rowid = " . $this->id;
//        // if ($this->element == 'facture') $sql.= " AND fk_statut < 2";
//        // if ($this->element == 'propal')  $sql.= " AND fk_statut = 0";
//
//        dol_syslog(get_class($this) . "::setDocModel", LOG_DEBUG);
//        $resql = $this->db->query($sql);
//        if ($resql) {
//            $this->modelpdf = $modelpdf;
//            return 1;
//        } else {
//            dol_print_error($this->db);
//            return 0;
//        }
        return 1;
    }

    /**
     *  Create a document onto disk according to template module.
     *
     *  @param	    string		$modele			Force template to use ('' to not force)
     *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
     *  @param      int			$hidedetails    Hide details of lines
     *  @param      int			$hidedesc       Hide description
     *  @param      int			$hideref        Hide ref
     *  @return     int         				0 if KO, 1 if OK
     */
    public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0) {
        global $conf, $langs;

        $langs->load("bills");

        if (!dol_strlen($modele)) {
            $modele = 'test';
        }

        // Increase limit for PDF build
        $err = error_reporting();
        error_reporting(0);
        @set_time_limit(120);
        error_reporting($err);

        $srctemplatepath = '';

        // If selected modele is a filename template (then $modele="modelname:filename")
        $tmp = explode(':', $modele, 2);
        if (!empty($tmp[1])) {
            $modele = $tmp[0];
            $srctemplatepath = $tmp[1];
        }

        // Search template files
        $file = '';
        $classname = '';
        $filefound = 0;
        $dirmodels = array('/');
        if (is_array($conf->modules_parts['models']))
            $dirmodels = array_merge($dirmodels, $conf->modules_parts['models']);
        foreach ($dirmodels as $reldir) {
            foreach (array('html', 'doc', 'pdf') as $prefix) {
                $file = $prefix . "_" . preg_replace('/^html_/', '', $modele) . ".modules.php";

                // On verifie l'emplacement du modele
                $file = dol_buildpath($reldir . "giftmodule/core/modules/giftmodule/" . $file, 0);
                if (file_exists($file)) {
                    $filefound = 1;
                    $classname = $prefix . '_' . $modele;
                    break;
                }
            }
            if ($filefound)
                break;
        }

        // Charge le modele
        if ($filefound) {
            require_once $file;

            $object = $this;

            $classname = $modele;
            $obj = new $classname($this->db);

            // We save charset_output to restore it because write_file can change it if needed for
            // output format that does not support UTF8.
            $sav_charset_output = $outputlangs->charset_output;
            if ($obj->write_file($object, $outputlangs) > 0) {
                $outputlangs->charset_output = $sav_charset_output;

                // we delete preview files
                require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
                dol_delete_preview($object);
                dol_syslog("Gift::generateDocument " . $object->rowid, LOG_DEBUG);
                return 1;
            } else {
                $outputlangs->charset_output = $sav_charset_output;
                dol_syslog("Erreur dans Gift::generateDocument " . $object->rowid);
                dol_print_error($this->db, $obj->error);
                return 0;
            }
        } else {
            print $langs->trans("Error") . " " . $langs->trans("ErrorFileDoesNotExists", $file);
            return 0;
        }
    }

}
