<?php defined('ABSPATH') or die('No direct script access.');
/**
 * snippet: menu.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */
?>
<nav role="navigation" class="main-navigation mtl mbs">
    <input type="checkbox" id="menu-link" class="visually-hidden" />
    <label for="menu-link" class="menu-link mbm" onclick>menu</label>
    <?php the_menu(array('à propos'=>'about', 'test'=>'about/contact') ) ?>
</nav>
