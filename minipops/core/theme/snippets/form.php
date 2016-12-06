<?php defined('ABSPATH') or die('No direct script access.');
/**
 * snippet: form.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */
?>

<section class="formulaire mtl mbs">
    
    <form method="post" action="traitement.php">

       <label for="pseudo">Votre pseudo :</label>
       <input type="text" name="pseudo" id="pseudo" autofocus/>

       <label for="pass">Votre mot de passe :</label>
       <input type="password" name="pass" id="pass" required/>

       <label for="email">Votre email :</label>
       <input type="email" name="email" id="email" />

       <label for="number">Votre number :</label>
       <input type="number" name="number" id="number" />

       <label for="search">Votre recherche :</label>
       <input type="search" name="search" id="search" />

       <label for="tel">Votre tel :</label>
       <input type="tel" name="tel" id="tel" />

       <label for="url">Votre url :</label>
       <input type="url" name="url" id="url" />

       <label for="range">Votre range :</label>
       <input type="range" name="range" id="range"/>

       <label for="color">Votre couleur :</label>
       <input type="color" name="color" id="color"/>

       <label for="date">Votre date :</label>
       <input type="date" name="date" id="date"/>

       <label for="time">Votre time :</label>
       <input type="time" name="time" id="time"/>

       <label for="week">Votre week :</label>
       <input type="week" name="week" id="week"/>

       <label for="month">Votre month :</label>
       <input type="month" name="month" id="month"/>

       <label for="datetime">Votre datetime :</label>
       <input type="datetime" name="datetime" id="datetime"/>

       <label for="datetime-local">Votre datetime-local :</label>
       <input type="datetime-local" name="datetime-local" id="datetime-local"/>

       <label for="text">Votre texte :</label>
       <input type="text" name="text" id="text" />

       <label for="texte">Votre texte :</label>
       <textarea name="texte" id="texte"></textarea>

       <label for="checkbox">Votre checkbox :</label>
       <input type="checkbox" name="checkbox" checked/>

       <label for="radio">Radio</label>
       <input type="radio" name="radio" value="moins15" id="radio" />

       <label for="select">Select</label>
       <select name="select" id="select">
           <option value="france">France</option>
           <option value="espagne">Espagne</option>
           <option value="italie">Italie</option>
           <option value="royaume-uni">Royaume-Uni</option>
           <option value="canada">Canada</option>
           <option value="etats-unis">États-Unis</option>
           <option value="chine">Chine</option>
           <option value="japon">Japon</option>
       </select>

       <label for="select2">Select group</label>
       <select name="select2" id="select2">
           <optgroup label="Europe">
               <option value="france">France</option>
               <option value="espagne">Espagne</option>
               <option value="italie">Italie</option>
               <option value="royaume-uni">Royaume-Uni</option>
           </optgroup>
           <optgroup label="Amérique">
               <option value="canada">Canada</option>
               <option value="etats-unis">Etats-Unis</option>
           </optgroup>
           <optgroup label="Asie">
               <option value="chine">Chine</option>
               <option value="japon">Japon</option>
           </optgroup>
       </select>

       <fieldset>
	       	<legend>Votre souhait</legend> <!-- Titre du fieldset -->
	   </fieldset>

       <input type="submit" value="Envoyer" class="button-outline"/>
       <input type="reset" value="reset"/>
       <input type="image" src="http://www.w3schools.com/css/trolltunga.jpg" value="image"/>
       <input type="button" value="button" />

    </form>
</section>
