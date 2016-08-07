<?php defined('ABSPATH') or die('No direct script access.');
/**
 * snippet: menu.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */
?>
<nav role="navigation" class="main-navigation">
            <input type="checkbox" id="menu-link" class="visually-hidden" />
            <label for="menu-link" class="menu-link pas" onclick>&#x2630;</label>
            <?php the_menu('primary_menu') ?>
    
        </nav>
