<?php

/* Copyright (C) 2018 SuperAdmin
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
 *
 * Library javascript to enable Browser notifications
 */

if (!defined('NOREQUIREUSER'))
    define('NOREQUIREUSER', '1');
if (!defined('NOREQUIREDB'))
    define('NOREQUIREDB', '1');
if (!defined('NOREQUIRESOC'))
    define('NOREQUIRESOC', '1');
if (!defined('NOREQUIRETRAN'))
    define('NOREQUIRETRAN', '1');
if (!defined('NOCSRFCHECK'))
    define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL'))
    define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN'))
    define('NOLOGIN', 1);
if (!defined('NOREQUIREMENU'))
    define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML'))
    define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX'))
    define('NOREQUIREAJAX', '1');


/**
 * \file    giftmodule/js/giftmodule.js.php
 * \ingroup giftmodule
 * \brief   JavaScript file for module GiftModule.
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
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/../main.inc.php"))
    $res = @include(substr($tmp, 0, ($i + 1)) . "/../main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php"))
    $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
    $res = @include("../../../main.inc.php");
if (!$res)
    die("Include of main fails");

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache))
    header('Cache-Control: max-age=3600, public, must-revalidate');
else
    header('Cache-Control: no-cache');

print "
jQuery(function($){
    function $(el) {return document.getElementById(el.replace(/#/,''));};
    var sign = document.getElementById('sign_value');
    var imageLoader = document.getElementById('upload_sign');
    var canvas = document.getElementById('sign');
    var isDrawing = false;
    var context;
    if (canvas && canvas.nodeName.toLowerCase() === 'canvas') {
        var width = canvas.width;
        var height = canvas.height;
        context = canvas.getContext('2d');

        if (sign && sign.value != '') {
            var img = new Image();
            img.onload = function(){
                if (context) {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    context.drawImage(img,0,0);
                }
            }
            img.src = sign.value;
        }

        var start = function(coors) {
            context.moveTo(coors.x, coors.y);
            context.beginPath();
            isDrawing = true;
        };
        var move = function(coors) {
            if (isDrawing) {
                var canvasPos = canvas.getBoundingClientRect();
                context.strokeStyle = \"#000\";
                context.lineJoin = \"round\";
                context.lineWidth = 2;
                context.lineTo(coors.x - canvasPos.left, coors.y - canvasPos.top);
                context.stroke();
            }
        };
        var stop = function(coors) {
            if (isDrawing) {
                this.touchmove(coors);
                isDrawing = false;
            }
        };
        var drawer = {
            isDrawing: false,
            mousedown: start,
            mousemove: move,
            mouseup: stop,
            touchstart: start,
            touchmove: move,
            touchend: stop
        };

        var draw = function(e) {
            var coors = {
                x: 0,
                y: 0
            };
            if (e instanceof MouseEvent) {
                coors.x = e.clientX;
                coors.y = e.clientY;
            }
            else if (e instanceof TouchEvent && e.touches[0]) {
                coors.x = e.touches[0].clientX;
                coors.y = e.touches[0].clientY;
            } else {
                return;
            }
            drawer[e.type](coors);
            // prevent elastic scrolling
            if (isDrawing)
                e.preventDefault();
        }

        // mouse events listeners
        canvas.addEventListener('mousedown', draw, false);
        canvas.addEventListener('mousemove', draw, false);
        canvas.addEventListener('mouseup', draw, false);

        // touch events listeners
        canvas.addEventListener('touchstart', draw, false);
        canvas.addEventListener('touchmove', draw, false);
        canvas.addEventListener('touchend', draw, false);

        // handle clear canvas
        var clear_sign = function(e) {
            canvas.width = width;
            canvas.height = height;
            context.clearRect(0, 0, canvas.width, canvas.height);
            console.log('Canvas cleared !');
        };

        // listeners for clear canvas
        $('#clear_sign').addEventListener('mousedown', clear_sign, false);
        $('#clear_sign').addEventListener('touchstart', clear_sign, false);

        console.log('Sign canvas ready !');
    }

    window.onload = function() {
        var form = document.querySelector('.fiche form');
        if (form) {
            form.onsubmit = submitted.bind(form);
            if (imageLoader)
                imageLoader.addEventListener('change', handleImage, false);
        }
    }

    function submitted(event) {
        if (canvas && canvas.nodeName.toLowerCase() === 'canvas') {
            var pngUrl = canvas.toDataURL();
            sign.value = pngUrl;
        }
    }

    function handleImage(e){
        var reader = new FileReader();
        reader.onload = function(event){
            var img = new Image();
            img.onload = function(){
                if (context) {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    context.drawImage(img,0,0);
                }
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
";
