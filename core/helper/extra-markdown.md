#HTML intercalé

Avec Markdown vous pouvez insérer des balises HTML au milieu de votre texte. C’est souvent pratique quand vous avez besoins d’éléments qui ne sont pas accessibles par la syntaxe Markdown mais qui sont facile à écrire en HTML.

Mais Markdown possède impose un restriction sévère quand au HTML que l’on introduit. Tiré de la documentation sur la syntaxe:

Les élément HTML représentant un bloc de texte ” c’est à dire `<div>`, `<table>`, `<pre>`, `<p>`, etc. ” doivent être séparés du contenu environnant par des lignes vides, et les balises de début et de fin ne doivent pas êtres indentés avec des tabulations ou des espaces.
Ces restrictions sont levés avec Markdown Extra et sont remplacés par deux autres moins restrictives:

La balise d’ouverture d’un élément bloc ne doit pas être indenté par plus de trois espaces. Toute balise indenté de plus de trois espaces sera traité en bloc de code tel que défini par la syntaxe normale de Markdown.

Quand l’élément bloc se trouve à l’intérieur d’une liste, tout son contenu doit être indenté au même niveau que l’élément de la liste. (Plus d’indentation ne causera aucun problème en autant que la première balise ouvrante n’est pas suffisamment indenté pour devenir un bloc de code — voir la première règle.)

Markdown dans un bloc HTML

Précédemment avec Markdown, vous ne pouviez pas include du texte formaté pour Markdown dans un élément `<div>`. Parce que `<div>` est un élément bloc, Markdown ne formate pas son contenu.

Markdown Extra vous donne un moyen de placer du texte formaté pour Markdown dans des éléments bloc. Vous faites cela en ajoutant l’attribut markdown à la balise avec la valeur 1, ce qui donne ` markdown="1" ` comme ici:

    <div markdown="1">
    Voici du *vrai* texte Markdown.
    </div>
    
L’attribut markdown="1" sera enlevé et le contenu du `<div>` sera converti en HTML. Le résultat final sera:

    <div>

    <p>Voici du <em>vrai</em> texte Markdown.</p>

    </div>
    
Markdown Extra est suffisamment intelligent pour appliquer le formatage correctement dépendant de l’élément bloc sur lequel vous placez l’attribut markdown. Si vous appliquez l’attribut markdown sur un élément `<p>` par exemple, seul les éléments de niveau texte seront produits à l’intérieur — les listes, les blocs de citation et les blocs de code ne seront pas permis.

Il y a cependant quelque cas qui sont ambigus, en voici justement un:

    <table>
    <tr>
    <td markdown="1">Voici du *vrai* texte Markdown.</td>
    </tr>
    </table>
    
Une cellule de tableau peut contenir soit des éléments bloc ou directement du texte. Dans un cas comme celui-ci, Markdown Extra produira uniquement les éléments de niveau texte. Si vous voulez permettre les blocs, écrivez markdown="block".

--------------------

##Attributs spéciaux

Avec Markdown Extra, vous pouvez donner un attribut « id » et plusieurs noms de classe (attribut « class ») à certains éléments en utilisant un bloc d’attributs spéciaux. Par exemple, on ajoute à un titre l’identifiant précédé d’un dièse entre accolades à la fin de la ligne du titre, comme ceci:

    Titre 1            {#titre1}
    =======

    ## Titre 2 ##      {#titre2}
    
Ceci permet de créer des liens aux différentes parties du document de cette façon:

    [Lien vers le titre 1](#titre1)
    
Pour ajouter un nom de classe, qui peut servir de référence pour une feuille de style, on utilise le point au début, comme ceci:

    ## Le site ##      {.principal}

Vous pouvez aussi ajouter des attributs additionnels ayant des valeurs simples en spécifiant le nom, suivi d’un signe égal, suivi de la valeur (qui peut comprendre d’espace pour le moment):

    ## The Site ##      {lang=en}

On peut combiner l’identifiant, plusieures classes et attributs additionnels en les plaçant tous dans la même bloc d’attributs spéciaux :

    ## The Site ##      {.principal .eclat #le-site lang=en}

Les blocs d’attributs spéciaux peuvent êtres utilisés avec

les titres,
les blocs de code balisés,
les liens, et
les images.
Pour les liens et les images, placez le bloc d’attributs spéciaux immédiatement après la parenthèse contenant l’adresse :

    [lien](url){#id .class}
    ![img](url){#id .class}
    
Ou si vous utilisez un lien par référence, placez-le à la fin de la ligne de définition du lien come ceci :

    [lien][ref] ou [ref]
    ![img][ref]

    [ref]: url "titre optionnel" {#id .class}
    
    
-----------------------    
##Blocs de code balisés

Markdown Extra ajoute une syntaxe pour les blocs de code sans indentation. Les blocs de code balisés sont tout comme les blocs de code de Markdown, à l’exception qu’ils n’ont pas d’indentation et utilisent à la place une ligne balise au début et à la fin pour délimiter le bloc de code. Le bloc de code débute avec une ligne constituée de trois tilde ~ ou plus, et se termine à la première ligne contenant le même nombre de tilde ~. Par exemple:

Voici un paragraphe introduisant:

    ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    un bloc de code d'une ligne
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
Il est aussi possible d’utiliser des guillemets obliques (accent grave) en remplacement du tilde:

    ````````````
    bloc de code
    ````````````
    
Contrairement à leur équivalent avec indentation, les blocs de code balisés peuvent débuter et se terminer avec des lignes vierges:

    ~~~

    ligne vierge avant
    ligne vierge après

    ~~~
    
Les blocs de code avec indentation ne peuvent pas être utilisés immédiatement après une liste parce que l’indentation de la liste prend préséance, mais vous pouvez utiliser un bloc de code balisé à la place:

    1.  Élément d'une liste

        Pas un code de bloc indenté, mais un second
        paragraphe dans l'élément de la liste

    ~~~
    Ceci est un bloc de code balisé
    ~~~
    
Les blocs de code balisés sont aussi idéaux si vous devez copier-coller du code dans un éditeur qui ne supporte pas l’indentation automatique, tel une boîte de texte dans votre navigateur web.

Vous pouvez aussi spécifier un nom de classe qui s’appliquera à un bloc de code balisé. Ceci peut être utile si vous voulez donner un style différent aux blocs de code selon le langage. Ou vous pouvez aussi l’utiliser pour indiquer à un colorieur syntaxique quelle syntaxe utiliser.

    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ .html
    <p>paragraphe <b>emphase</b>
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
Le nom de la classe est placé à la suite de la balise de début. Il peut être précédé d’un point, mais ce n’est pas obligatoire. Il est aussi possible de d’utiliser un bloc d’attributs spéciaux:

    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ {.html #exemple-1}
    <p>paragraphe <b>emphase</b>
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
Dans la sortie HTML, les attributs du bloc de code s’appliquent à l’élément code; si vous voulez les attributs appliqués à l’élément pre, réglez la variable code_attr_on_pre du parseur à true. Voir la documentation de la [configuration] pour plus de détail.

---------------
##configuration


###Tableaux

Markdown Extra possède sa propre syntaxe pour écrire des tableaux simples. Un tableau « simple » ressemble à ceci:

    Premier titre | Second titre
    ------------- | ------------
    Cellule       | Cellule     
    Cellule       | Cellule     
    
La première ligne contient le titre des colones; la deuxième ligne contient une ligne de séparation obligatoire entre les titres et les autres cellules; chaque ligne qui suit représente une rangé du tableau. Les colones sont séparés par le caractère ligne verticale (|). Une fois converti en HTML, le résultat est le suivant:

    <table>
    <thead>
    <tr>
      <th>Premier titre</th>
      <th>Second titre</th>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td>Cellule</td>
      <td>Cellule</td>
    </tr>
    <tr>
      <td>Cellule</td>
      <td>Cellule</td>
    </tr>
    </tbody>
    </table>
    
Si vous voulez, vous pouvez ajouter une ligne verticale supplémentaire au début et à la fin de chaque ligne du tableau. Pour illustrer, ceci donnera la même résultat que le tableau précédent:

    | Premier titre | Second titre |
    | ------------- | ------------ |
    | Cellule       | Cellule      |
    | Cellule       | Cellule      |
    
Note: Un tableau doit avoir au minimum une ligne verticale sur chaque ligne pour que Markdown Extra le lise correctement. Ceci veux dire que la seule façon de créer un tableau à une seule colonne est d’ajouter une ligne verticale au début ou à la fin (ou les deux) de chaque ligne.

Vous pouvez spécifier un alignement pour chaque colonne en ajoutant des deux-points à la ligne de séparation. Un deux-point à gauche de la ligne signifie que la colonne doit être aligné sur la gauche; un deux-point à droite signifie un alignement à droite; des deux-points de chaque côté signifie que la colonne est centrée.

    | Article    | Valeur |
    | ---------- | ------:|
    | Ordinateur | 1600 $ |
    | Téléphone  |   12 $ |
    | Tuyau      |    1 $ |
    
L’attribut HTML align est appliqué à chaque cellule de la colonne concernée.

Vous pouvez appliquer du formatage de niveau texte à chaque cellule en utilisant la syntaxe Markdown:

    | Nom de fonction | Description                    |
    | --------------- | ------------------------------ |
    | `aide()`        | Affiche la fenêtre d'aide.     |
    | `exploser()`    | **Détruit l'ordinateur !**     |
    
    
    
###Listes de définitions

Markdown Extra ajouter les listes de définitions. Une liste de définition est composé de termes et de la définition de ces termes, un peu comme dans un dictionnaire.

Une liste de définition simple pour Markdown Extra est composé d’une ligne contenant un terme suivit par un deux-point et la définition pour le terme.

    Pomme
    :   Fruit charnu, de forme quasi sphérique, déprimée au 
        sommet et à la base, à pulpe homogène.

    Orange
    :   Fruit de l'oranger, arbre de la famille des Rutacées.
    Chaque terme doit être séparé de la définition qui le précède par une ligne blanche. Les définitions peuvent s’étaler sur plusieurs lignes. Dans un tel cas, les lignes devrait être indentées, mais ce n’est pas vraiment nécessaire: si vous êtes paresseux, vous pouvez oublier d’indenter une définition qui s’étale sur plusieurs lignes et ça fonctionnera toujours:

    Pomme
    :   Fruit charnu, de forme quasi sphérique, déprimée au 
    sommet et à la base, à pulpe homogène.

    Orange
    :   Fruit de l'oranger, arbre de la famille des Rutacées.
    
Les deux listes de définitions précédentes donnent le même résultat en HTML:

    <dl>
    <dt>Apple</dt>
    <dd>Pomaceous fruit of plants of the genus Malus in 
    the family Rosaceae.</dd>

    <dt>Orange</dt>
    <dd>The fruit of an evergreen tree of the genus Citrus.</dd>
    </dl>
    
Les deux-points en tant que marqueur d’une définition sont généralement accotés sur la marge de gauche, mais peuvent être indentés par un, deux ou trois espaces. Les marqueurs de définition doivent êtres suivi d’au moins un espace ou d’une tabulation.

Les termes d’une listes de définition peuvent avoir plus d’une définition associée:

    Pomme
    :   Fruit charnu, de forme quasi sphérique, déprimée au 
        sommet et à la base, à pulpe homogène.
    :   Une compagnie d'ordinateur américaine.

    Orange
    :   Fruit de l'oranger, arbre de la famille des Rutacées.
    Vous pouvez aussi associer plusieurs termes à la même définition:

    Terme 1
    Terme 2
    :   Définition a

    Terme 3
    :   Définition b
    Si une définition est précédé par une ligne blanche, Markdown Extra l’enrobera dans une balise <p> dans la sortie HTML. Par exemple, ceci:

    Pomme

    :   Fruit charnu, de forme quasi sphérique, déprimée au 
        sommet et à la base, à pulpe homogène.

    Orange

    :   Fruit de l'oranger, arbre de la famille des Rutacées.
    
produira cela:

    <dl>
    <dt>Pomme</dt>
    <dd>
    <p>Fruit charnu, de forme quasi sphérique, déprimée au 
    sommet et à la base, à pulpe homogène.</p>
    </dd>

    <dt>Orange</dt>
    <dd>
    <p>Fruit de l'oranger, arbre de la famille des Rutacées.</p>
    </dd>
    </dl>
    
Et tout comme pour les éléments d’une liste ordonnée ou non-ordonnée, une définition peut contenir plusieurs paragraphes et inclure d’autre éléments bloc tel que des blocs de citation, des blocs de code, des listes et même d’autre listes de définition.

    Terme 1

    :   Voici une définition avec deux paragraphes. Lorem ipsum 
        dolor sit amet, consectetuer adipiscing elit. Aliquam 
        hendrerit mi posuere lectus.

        Vestibulum enim wisi, viverra nec, fringilla in, laoreet
        vitae, risus.

    :   Une deuxième définition pour le terme 1, aussi enrobé d'un 
        paragraphe dû à la ligne blanche qui précède.

    Terme 2

    :   Cette définition possède un bloc de code, un bloc de citation
        et une liste.

            bloc de code.

        > bloc de citation
        > sur deux lignes.

        1.  premier élément d'une liste
        2.  deuxième élément d'une liste
    
    
    
###Notes de bas de page

Les notes de bas de page fonctionnent de la même façon que les liens par référence. Une note de bas de page comprend deux choses: un marqueur dans le texte qui deviendra un nombre surélevé; une définition de note de bas de page qui sera placé dans une liste comprendant toutes les notes à la fin du document. Une note de bas de page ressemble à ceci:

Un peu de texte avec une note de bas de page.[^1]

    [^1]: Et voici la note.
    
Les défintitions de notes de bas de page peuvent se trouver n’importe où dans le document, mais les notes seront toujours listés dans l’ordre où elle sont référencé dans le texte. Notez qu’il est impossible de faire deux liens vers la même note: si vous essayez, la deuxième référence restera sous forme de texte.

Chaque note doit avoir un nom distinct. Le nom sera utilisé pour lier la référence de note à son texte, mais n’aura pas d’effet sur la numérotation. Les noms peuvent contenir n’importe quel caractère valide dans l’attribut id du HTML.

Les notes de bas de page peuvent contenir des éléments de type bloc, ce qui permet d’y mettre plusieurs paragrahes, listes, blocs de citation et ainsi de suite dans une note. Cela fonctionne de la même façon que pour les items d’une liste: vous n’avez qu’à indenter les paragraphes qui suivent par quatre espace dans la définition de la note de bas de page:

Un peu de texte avec une note de bas de page.[^1]

    [^1]: Et voici la note.

    Et voilà un deuxième paragraphe.
    
Si vous voulez que les choses soit mieux alignées, vous pouvez laisser la première ligne de la note vide et commencer le premier paragraph juste au dessous:

    [^1]:
        Et voici la note.

        Et voilà un deuxième paragraphe.
    SORTIE

Un seul format de sortie HTML ne pourra vraisemblablement pas satisfaire tout le monde. Une future version pourrait fournir une interface de programmation permettant de redéfinir le format. Mais pour l’instant, la sortie est calqué sur le modèle de Daring Fireball, avec quelques petites modifications. Voici la sortie par défaut du premier exemple mentioné plus haut:

    <p>Un peu de texte avec une note de bas de page.
       <sup id="fnref-1"><a href="#fn-1" class="footnote-ref">1</a></sup></p>

    <div class="footnotes">
    <hr />
    <ol>

    <li id="fn-1">
    <p>Et voici la note.
       <a href="#fnref-1" class="footnote-backref">&#8617;</a></p>
    </li>

    </ol>
    </div>
    
Un peu compliqué, mais dans un navigatueur le résultat ressemble à ça:

Un peu de texte avec une note de bas de page. 1

Et voici la note. `↩`
Les attributs `class="footnote-ref"` et `class="footnote-backref"` sur les liens expriment la relation qu’ils ont avec les éléments auxuels ils sont liés. On peut utiliser ces attributs pour appliquer un style aux liens avec les règles suivantes:

    a.footnote-ref { ... }
    a.footnote-backref { ... }
    
Vous pouvez personalliser les attributs class et title pour les liens des marqueurs et les liens de retour. Voir la documentation sur la `[configuration]` pour plus de détails.



###Abréviations

Markdown Extra ajoute un support pour les abréviations (La balise HTML `<abbr>`). Ça fonctionne d’une façon très simple. On définie une abbréviation comme ceci:

    *[HTML]: Hyper Text Markup Language
    *[ONU]:  Organisation des nations unies
    
et, ailleurs dans le document, on écrit du texte tout à fait normalement:

La spécification HTML
n'a rien à voir avec l'ONU.
et toutes les occurences des termes définis comme abbréviation deviendront:

La spécification `<abbr title="Hyper Text Markup Language">HTML</abbr>`
n'a rien à voir avec l'`<abbr title="Organisation des nations unies">ONU</abbr>`.
Les abbréviations sont sensibles à la casse, elle peuvent aussi s’étendre sur plusieurs mots si définies comme telle. Une abbréviation peut aussi avoir une définition vide, dans un quel cas la balise `<abbr>` sera ajoutée dans le texte mais l’attribut title sera omis.

L'opération Tigra Genesis avance bien.

    *[Tigra Genesis]:
    
Les définitions d’abbréviations peuvent se trouver n’importe où dans le document. Elle sont retiré du document final.



###Listes ordonnées

Si une liste ordonnée débute par un nombre différent de 1, Markdown Extra traduira ce choix dans la sortie HTML.

Emphase

Les règles de l’emphase on légèrement changées par rapport à la syntaxe originale de Markdown. Avec Markdown Extra, les traits soulignés au milieu d’un mot sont maintenant traités en tant que caractères littéraux. L’emphase par les traits de soulignement ne fonctionne que pour les mots entiers. Si vous voulez placer en emphase seulement une partie d’un mot, c’est toujours possible en utilisant les astérisques comme marqueurs d’emphase.

Par exemple, avec ceci:

    Veuillez ouvrir le dossier "boîte_magique_secrète".
    
Markdown Extra ne convertira pas les traits soulignés en emphase parce qu’ils sont au milieu d’un mot. Le résultat HTML de Markdown Extra est le suivant:

    <p>Veuillez ouvrir le dossier "boîte_magique_secrète".</p>

L’emphase avec les traits soulignés fonctionne toujours en autant que vous placiez des mots entiers en emphase, comme ceci:

    J'aime ça quand tu me dit _je t'aime_.
    
La même chose s’applique pour l’emphase forte: avec Markdown Extra, vous ne pouvez plus placer d’emphase forte au milieu d’un mot en utilisant des traits de soulignement, il est nécessaire d’utiliser des astérisques comme marqueur d’emphase.



###Échappement par barre oblique inverse

Markdown Extra ajoute le deux-points `(:)` et la ligne verticale `(|)` à la listes des caractères qu’il est possible d’échapper avec une barre oblique inverse. Ceci permet d’éviter que ces caractères forment une liste de définitions ou un tableau.

