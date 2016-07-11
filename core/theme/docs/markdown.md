#HTML INTERCALÉ

La syntaxe de Markdown sert un objectif : être utilisée comme format d’écriture sur le web.

Markdown n’est pas un remplacement pour le HTML, ni même près de l’être. Sa syntaxe est petite et correspond en fait à un très petit sous-ensemble du HTML. L’idée n’est pas de créer une syntaxe qui rend plus facile l’insertion de balises HTML. Selon moi, ces balises sont déjà faciles à insérer. L’idée derrière Markdown est de faciliter la lecture, l’écriture et l’édition du texte en prose. Le HTML est un format de publication, Markdown est un format d’écriture. C’est pour cette raison que Markdown s’adresse uniquement à ce qui peut être convoyé par du texte.

Pour n’importe quelle fonction qui n’est pas couverte par la syntaxe de Markdown, vous utilisez directement une balise HTML. Il n’y a pas de raison de la précéder ou de la délimiter par des marqueurs spéciaux indiquant le passage de la syntaxe Markdown au HTML ; vous écrivez tout simplement la balise là où vous la voulez.

Les seules restrictions sont que les éléments HTML représentant un bloc de texte — c’est à dire `<div>`, `<table>`, `<pre>`, `<p>`, etc. — doivent être séparés du contenu environnant par des lignes vides, et les balises de début et de fin ne doivent pas êtres indentées avec des tabulations ou des espaces. Markdown est assez intelligent pour ne pas ajouter des balises <p> supplémentaires (et indésirables) autour des éléments de bloc.

Par exemple, pour ajouter un tableau HTML à un article écrit avec Markdown :

Ceci est un paragraphe régulier.

    <table>
        <tr>
            <td>Foo</td>
        </tr>
    </table>

Ceci est un autre paragraphe régulier.
    
Notez que le formatage Markdown n’est pas appliqué à l’intérieur des balises d’élément de blocs HTML. C’est à dire que vous ne pouvez pas appliquer de *l'emphase* selon la syntaxe Markdown à l’intérieur d’un bloc HTML.

Les éléments délimitant une étendue de texte — comme `<span>`, `<cite>` ou `<del>` — peuvent être utilisés n’importe où dans un paragraphe, dans l’élément d’une liste ou dans un titre. Si vous voulez, vous pouvez même utiliser des balises HTML à la place du format Markdown; par exemple si vous préférez utiliser la balise HTML `<a>` ou `<img>` au lieu de la syntaxe Markdown pour les liens ou les images, faites-le.

Contrairement aux éléments de bloc, la syntaxe Markdown est traitée à l’intérieur des balises d’étendue de texte.


-------------------------------

##ÉCHAPPEMENT AUTOMATIQUE DES CARACTÈRES SPÉCIAUX

En HTML, il y a deux caractères qui demandent un traitement spécial: < et &. Les chevrons ouvrants sont utilisés pour débuter les balises ; les perluètes indiquent une entité HTML. Si vous voulez les utiliser en tant que caractères littéraux, vous devez les échapper avec des entités, c’est à dire que vous devez écrire &lt; et &amp;.

La perluète est particulièrement désagréable pour ceux qui écrivent pour le web. Si vous voulez parler de « AT&T », vous devrez écrire « AT&amp;T ». Vous devrez aussi échapper les perluètes à l’intérieur des URLs. Cela signifie que si vous voulez créer un lien vers :

    http://images.google.com/images?num=30&q=larry+bird
    
vous devrez encoder l’URL comme suit :

    http://images.google.com/images?num=30&amp;q=larry+bird
    
dans l’attribut href de la balise `<a>`. Il n’est pas nécessaire de dire que c’est facile à oublier ; il s’agit probablement de la plus grande source d’erreur de validation HTML sur les sites web qui sont autrement valides.

Markdown vous permet d’utiliser ces caractères de façon naturelle en prenant soin de les encoder pour vous quand c’est nécessaire. Si vous utilisez une perluète pour débuter une entité HTML, elle restera inchangée ; autrement elle sera convertie en &amp;.

Alors, si vous voulez inclure le symbole de copyright dans votre article, vous pouvez écrire :

    &copy;
    
et Markdown n’y touchera pas. Mais si vous écrivez :

    AT&T
    
Markdown le convertira en :

    AT&amp;T
    
De la même façon, parce que Markdown supporte le HTML intercalé, si vous utilisez les chevrons pour délimiter les balises HTML, Markdown les traitera comme tel. Mais si vous écrivez :

    4 < 5
    
Markdown le convertira en :

    4 &lt; 5
    
Cependant, à l’intérieur des blocs ou des étendues de code, les chevrons et les perluètes seront toujours encodés automatiquement. Ceci facilite grandement l’utilisation de Markdown quand il s’agit d’écrire au sujet du HTML. (Par opposition au HTML brut qui est un très mauvais format pour écrire à propos de sa propre syntaxe parce que chaque < et & dans vos exemples de code doivent être échappés.)

-------------------------------

##Éléments de bloc


###PARAGRAPHES ET SAUTS DE LIGNE

Un paragraphe est simplement une ou plusieurs lignes consécutives de texte, séparées par une ou plusieurs lignes vides. (Une ligne contenant uniquement des espaces ou des tabulations est considérée vide.) Les paragraphes ne doivent normalement pas êtres indentés par des espaces ou des tabulations.

La règle « une ou plusieurs lignes consécutives de texte » implique que Markdown supporte les sauts de ligne manuels à l’intérieur des paragraphes. Ceci diffère beaucoup de la plupart des autres convertisseurs de texte vers HTML (incluant l’option « Convertir les sauts de ligne » de MovableType) qui traduisent chaque saut de ligne à l’intérieur d’un paragraphe par la balise `<br />`.

Si vous voulez vraiment insérer une balise de saut de ligne `<br />` en utilisant Markdown, vous n’avez qu’à terminer la ligne par deux espaces, ou plus, puis taper « retour ».

Oui, ceci nécessite un peu plus d’effort pour créer un `<br />`, mais une règle plus simple dans le style de « chaque saut de ligne correspond à un `<br />` » ne fonctionnerait pas bien pour Markdown. La syntaxe des blocs de citation et des listes à plusieurs paragraphes fonctionne mieux — tout en étant plus lisible — quand vous formatez vos paragraphes avec un saut de ligne à la fin de chaque ligne.


###TITRES

Markdown supporte deux syntaxes pour les titres, Setext et atx.

Les titres de style Setext sont « soulignés » en utilisant le symbole égal (pour le premier niveau) et des tirets (pour le second niveau). Par exemple :

    Titre de niveau 1 (balise H1)
    =============================

    Titre de niveau 2 (balise H2)
    -----------------------------
    
N’importe quelle longueur de soulignement avec des = ou des - fonctionne.

Les titres de style atx sont représentés par entre un et six dièses (#) au début de la ligne, représentant les titres de niveau 1 à 6. Par exemple :

    # Titre de niveau 1

    ## Titre de niveau 2

    ###### Titre de niveau 6
    
Facultativement, vous pouvez « fermer » les titres de style atx. C’est purement cosmétique — faites-le si vous trouvez que c’est plus beau. Le nombre de dièses de fermeture n’a pas besoin de correspondre au nombre utilisé au début l’entête. (Le nombre de dièses ouvrant détermine le niveau du titre.) :

    # Titre de niveau 1 #

    ## Titre de niveau 2 ##

    ### Titre de niveau 3 ######
    
    
###BLOCS DE CITATION

Comme pour le courrier électronique, Markdown utilise le caractère `>` pour délimiter les blocs de citation. Si vous êtes familier avec le format de citation du courrier électronique, alors vous savez comment créer un bloc de citation avec Markdown. Le résultat est plus joli si le texte est écrit avec des sauts de ligne manuels et un `>` au début de chaque ligne :

    > Ceci est un bloc de citation avec deux paragraphes.  Lorem ipsum dolor 
    > sit amet, consectetuer adipiscing elit. Aliquam hendrerit mi posuere 
    > lectus. Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, 
    > risus.
    > 
    > Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
    > id sem consectetuer libero luctus adipiscing.

Markdown vous permet cependant d’être paresseux et de n’écrire qu’un seul `>` au début de la première ligne d’un paragraphe à sauts de ligne manuels :

    > Ceci est un bloc de citation avec deux paragraphes.  Lorem ipsum dolor 
    sit amet, consectetuer adipiscing elit. Aliquam hendrerit mi posuere 
    lectus. Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, 
    risus.

    > Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
    id sem consectetuer libero luctus adipiscing.
    
Les blocs de citation peuvent être imbriqués (c’est à dire qu’on a un bloc de citation à l’intérieur d’un autre bloc de citation) en ajoutant des niveaux additionnels de `>` :

    > Ceci est le premier niveau de citation.
    >
    > > Ceci est un bloc de citation imbriqué.
    >
    > Retour au premier niveau.
    
Les blocs de citation peuvent contenir d’autres éléments de Markdown, comme des titres, des listes et des blocs de code :

    > ## Ceci est un titre.
    > 
    > 1.  Ceci est le premier élément d'une liste.
    > 2.  Ceci est le second élément d'une liste.
    > 
    > Voici un exemple de code :
    > 
    >     return shell_exec("echo $input | $markdown_script");
    
Un éditeur de texte qui se respecte devrait permettre facilement la création de blocs de citation au style du courrier électronique. Par exemple, avec BBEdit, vous pouvez sélectionner du texte et choisir « Augmenter le niveau de citation » dans le menu Texte.



###LISTES

Markdown supporte les listes ordonnées (numérotées) et non-ordonnées (à puces).

Les listes non-ordonnées utilisent l’astérisque, le plus, ou encore le tiret — de façon tout à fait interchangeable — en tant que marqueur de liste :

    *   Rouge
    *   Vert
    *   Bleu
    
est équivalent à :

    +   Rouge
    +   Vert
    +   Bleu
    
et :

    -   Rouge
    -   Vert
    -   Bleu
    
Les listes ordonnées utilisent un nombre suivit d’un point :

    1.  Bird
    2.  McHale
    3.  Parish
    
Il est important de noter que l’ordre des nombres que vous utilisez pour écrire la liste n’a pas d’effet sur la sortie HTML que Markdown produit. Le code HTML produit par Markdown à partir du texte ci-dessus est :

    <ol>
    <li>Bird</li>
    <li>McHale</li>
    <li>Parish</li>
    </ol>
    
Si vous écrivez la liste comme ceci :

    1.  Bird
    1.  McHale
    1.  Parish
    
ou même :

    3. Bird
    1. McHale
    8. Parish
    
vous obtiendriez exactement le même code HTML en sortie. L’idée est que, si vous voulez, vous pouvez utiliser des nombres ordinaux dans vos listes ordonnées faites avec Markdown, de façon à ce que les nombres dans votre texte d’entrée correspondent avec ceux du résultat HTML. Mais si vous préférez, vous n’avez pas à le faire.

Toutefois, si vous ne numérotez pas les éléments dans l’ordre, vous devriez quand même débuter vos listes avec le numéro 1. Il est possible que Markdown supporte dans le futur des listes ordonnées commençant par un numéro quelconque.

Les marqueurs de liste sont typiquement alignés sur la marge de gauche, mais peuvent être indentés de la marge par trois espaces ou moins. Les marqueurs de liste doivent être suivis par au moins un espace, ou par une tabulation.

Pour écrire une liste qui se lit bien, vous pouvez ajouter une indentation après chaque saut de ligne manuel :

    *   Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
        Aliquam hendrerit mi posuere lectus. Vestibulum enim wisi,
        viverra nec, fringilla in, laoreet vitae, risus.
    *   Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
        Suspendisse id sem consectetuer libero luctus adipiscing.
        
Mais si vous préférez vous éviter du travail, vous n’avez pas à le faire :

    *   Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
    Aliquam hendrerit mi posuere lectus. Vestibulum enim wisi,
    viverra nec, fringilla in, laoreet vitae, risus.
    *   Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
    Suspendisse id sem consectetuer libero luctus adipiscing.
    
Si les éléments de la liste sont séparés par une ligne blanche, Markdown placera leur contenu à l’intérieur de balises <p> dans la sortie HTML. Par exemple, cette entrée :

    *   Oiseau
    *   Magie
    
sera changée en :

    <ul>
    <li>Oiseau</li>
    <li>Magie</li>
    </ul>
    
Alors que ceci :

    *   Oiseau

    *   Magie
    
sera changé en :

    <ul>
    <li><p>Oiseau</p></li>
    <li><p>Magie</p></li>
    </ul>
    
Les éléments d’une liste peuvent être constitués de plusieurs paragraphes. Tous les paragraphes suivant le premier doivent à ce moment être indentés par 4 espaces ou une tabulation :

    1.  Ceci est un élément de la liste contenant deux paragraphes. 
        Lorem ipsum dolor sit amet, consectetuer adipiscing elit. 
        Aliquam hendrerit mi posuere lectus.

        Vestibulum enim wisi, viverra nec, fringilla in, laoreet
        vitae, risus. Donec sit amet nisl. Aliquam semper ipsum
        sit amet velit.

    2.  Suspendisse id sem consectetuer libero luctus adipiscing.
    
Le résultat est plus beau si vous indentez toutes les lignes des paragraphes, mais ici aussi Markdown vous permet d’être un peu lâche :

    *   Ceci est un élément de la liste avec deux paragraphes.

        Ceci est le deuxième paragraphe dans l'élément. Vous
    n'avez besoin d'indenter que la première ligne. Lorem ipsum 
    dolor sit amet, consectetuer adipiscing elit.

    *   Un autre élément dans la même liste.
    Pour mettre un bloc de citation à l’intérieur d’un élément d’une liste, les délimiteurs > du bloc de citation doivent être indentés :

    *   Un élément de la liste avec un bloc de citation :

        > Ceci est un bloc de citation
        > à l'intérieur d'une liste.
    Pour placer un bloc de code à l’intérieur de l’élément d’une liste, le bloc de code doit être indenté deux fois (une fois pour la liste et une fois pour le bloc de code), ce qui représente 8 espaces ou deux tabulations :

    *   Un élément de la liste avec un bloc de code :

        <le code va ici>
        
Il vaut la peine de noter qu’il est possible de déclencher une liste ordonnée par accident, en écrivant quelque chose comme ça :

    1986. Quelle belle saison.
    
En d’autre mots, une séquence nombre-point-espace au début d’une ligne. Pour éviter cette situation, vous pouvez échapper le point à l’aide d’une barre oblique inverse :

    1986\. Quelle belle saison.
    
    
    
###BLOCS DE CODE

Les blocs de code sont utilisés pour écrire du code de programmation ou de balisage HTML. À la place de former des paragraphes normaux, les lignes d’un bloc de code sont interprétées littéralement. Markdown enveloppe un bloc de code avec les deux balises `<pre>` et `<code>`.

Pour produire un bloc de code avec Markdown, vous n’avez qu’à indenter chaque ligne d’un bloc par au moins 4 espace ou une tabluation. Par exemple, avec ce texte en entrée :

Ceci est un paragraphe normal :

    Ceci est un bloc de code.
    
Markdown générera :

    <p>Ceci est un paragraphe normal :</p>

    <pre><code>Ceci est un bloc de code.
    </code></pre>
    
Un niveau d’indentation — 4 espaces ou une tabulation — est enlevé de chaque ligne du bloc de code. Par exemple, ceci :

Voici un exemple d'AppleScript :

    tell application "Foo"
        beep
    end tell
    
deviendra :

    <p>Voici un exemple d'AppleScript :</p>

    <pre><code>tell application "Foo"
        beep
    end tell
    </code></pre>
    
Un bloc de code se termine à la première ligne qui n’est pas indentée (ou en atteignant la fin de l’article).

À l’intérieur d’un bloc de code, la perluète (`&`) et les chevrons (`<` et `>`) sont automatiquement convertis en entités HTML. Ceci permet d’inclure très facilement des exemples de code HTML en utilisant Markdown — juste à le coller et à l’indenter, et Markdown s’occupera d’encoder la perluète et les chevrons. Par exemple, ceci :

    <div class="footer">
        &copy; 2004 Foo Corporation
    </div>
    
sera converti en :

    <pre><code>&lt;div class="footer"&gt;
        &amp;copy; 2004 Foo Corporation
    &lt;/div&gt;
    </code></pre>
    
La syntaxe Markdown n’est pas traitée à l’intérieur d’un bloc de code. Ceci signifie qu’il est aussi très facile d’utiliser Markdown pour écrire des exemples portant sur sa propre syntaxe.



###LIGNES HORIZONTALES

Vous pouvez produire une ligne horizontale (`<hr />`) en plaçant au moins trois tirets, astérisques, ou barres de soulignement seuls sur une ligne. Si vous voulez, vous pouvez aussi écrire des espaces entre les tirets ou les astérisques. Chacune des lignes suivante produira une ligne horizontale :

    * * *

    ***

    *****

    - - -

    ---------------------------------------

    _ _ _
    Éléments de texte



###LIENS

Markdown supporte deux styles de liens : incorporé au texte et par référence.

Dans les deux cas, le texte du lien est délimité par des `[`crochets`]`.

Pour créer un lien incorporé au texte, insérez une parenthèse immédiatement après le crochet qui ferme le texte du lien. À l’intérieur de la parenthèse, placez l’URL sur lequel vous voulez faire pointer le lien, optionellement accompagné d’un titre pour le lien entouré de guillemets droits. Par exemple :

    Ceci est [un exemple](http://exemple.com/ "Titre") de lien incorporé.

    [Ce lien](http://exemple.net/) n'a pas de titre.
    
Produira :

    <p>Ceci est <a href="http://exemple.com/" title="Titre">
    un exemple</a> de lien incorporé.</p>

    <p><a href="http://exemple.net/">Ce lien</a> n'a pas de
    titre.</p>
    
Si vous faites référence à une ressource locale sur le même serveur, vous pouvez utiliser un chemin relatif :

    Voir ma page [Description](/description/) pour les détails. 
    
Les liens par référence utilisent une deuxième paire de crochets, à l’intérieur desquels vous placez une étiquette pour identifier le lien :

    Ceci est [un exemple][id] de lien en référence.
    
Vous pouvez facultativement utiliser une espace pour séparer les deux crochets :

    Ceci est [un exemple] [id] de lien en référence.
    
Ensuite, n’importe où dans le document, vous définissez le lien de cette façon, sur sa propre ligne :

    [id]: http://exemple.com/  "Titre facultatif"
    
En résumé :

Des crochets contenant le nom du lien (pouvant être indentés de la marge de gauche par au plus trois espaces) ;
suivis d’un deux-points ;
suivi d’une espace ou plus (ou des tabulations) ;
suivi par l’URL du lien ;
optionellement suivi d’un titre entouré par des guillemets droits simples ou doubles.
L’URL du lien peut, mais ce n’est pas obligatoire, être entouré par des chevrons :

    [id]: <http://exemple.com/>  "Titre facultatif"
    
Vous pouvez placer l’attribut titre sur la ligne suivante et utiliser des espaces supplémentaires ou des tabulations pour faire du remplissage, ce qui donne un plus beau résultat avec de long URLs :

    [id]: http://exemple.com/long/chemin/vers/la/ressource
    
    "Titre facultatif"
    
Les définitions de liens sont utilisées uniquement pour créer des liens durant le traitement par Markdown, et sont supprimées du document HTML en sortie.

Les noms des liens peuvent contenir des lettres, des nombres, des espaces et de la ponctuation — mais ils ne sont pas sensibles à la casse. Par exemple, ces deux liens :

    [link text][a]
    [link text][A]
    
sont équivalents.

Vous pouvez utiliser un nom de lien implicite, ce qui vous permet d’omettre le nom du lien, et dans ce cas le lien est identifié par son texte. Utilisez simplement une paire de crochets vides — c’est à dire que pour créer un lien « Google » vers le site google.com, vous pouvez simplement écrire :

    [Google][]
    
Et ensuite définir le lien :

    [Google]: http://google.com/
    
Parce que le nom du lien peut contenir des espaces, ce raccourci fonctionne aussi quand vous avez plusieurs mots dans le texte lié :

    Visitez [Daring Fireball][] pour plus d'informations.

Et ensuite définissez le lien :

    [Daring Fireball]: https://daringfireball.net/
    
Les définitions de lien peuvent être placées n’importe où dans le document Markdown. J’ai tendance à les placer immédiatement après le paragraphe dans lequel elles sont utilisées, mais si vous voulez, vous pouvez les placer à la fin de votre document, comme des notes de bas de page.

Voici un exemple qui montre les liens par référence en action :

    Je reçoit 10 fois plus de trafic de [Google] [1] que de
    [Yahoo] [2] ou [MSN] [3].

      [1]: http://google.com/        "Google"
      [2]: http://search.yahoo.com/  "Yahoo Search"
      [3]: http://search.msn.com/    "MSN Search"
      
En utilisant les noms de liens implicites, vous pourriez écrire :

    Je reçoit 10 fois plus de trafic de [Google][] que de
    [Yahoo][] ou [MSN][].

      [google]: http://google.com/        "Google"
      [yahoo]:  http://search.yahoo.com/  "Yahoo Search"
      [msn]:    http://search.msn.com/    "MSN Search"
      
Les deux exemples ci-haut produiront le code HTML suivant :

    <p>Je reçoit 10 fois plus de trafic de <a href="http://google.com/"
    title="Google">Google</a> que de
    <a href="http://search.yahoo.com/" title="Yahoo Search">Yahoo</a>
    ou <a href="http://search.msn.com/" title="MSN Search">MSN</a>.</p>
    
En guise de comparaison, voici le même paragraphe écrit avec la syntaxe de lien incorporées :

    Je reçoit 10 fois plus de trafic de [Google](http://google.com/ "Google") 
    que de [Yahoo](http://search.yahoo.com/ "Yahoo Search") ou
    [MSN](http://search.msn.com/ "MSN Search").
    
Le but des liens par référence n’est pas qu’ils soient plus faciles à écrire. L’idée est qu’avec les liens par référence, votre document est beaucoup plus lisible. Comparez les exemple ci-dessus : en utilisant les liens par référence, le paragraphe contient seulement 81 caractères ; avec les liens incorporés au texte nous avons 176 caractères ; et sous forme de code HTML, c’est 234 caractères. Avec le HTML, il y a plus gros de balise que de texte.

Avec les liens par référence de Markdown, un document source ressemble beaucoup plus à sa sortie finale, tel qu’il sera affiché par un navigateur. En vous permettant de prendre les informations supplémentaires reliées aux liens et de les placer en dehors du paragraphe, vous pouvez créer des liens sans interrompre l’écoulement narratif de votre prose.



###EMPHASE

Markdown interprète les astérisques (`*`) et les traits soulignés (`_`) pour indiquer l’emphase. Du texte placé entre deux `*` ou `_` sera entouré par l’élément HTML `<em>` ; avec des doubles `*` ou `_`, le texte sera entouré de l’élément `<strong>`. Par exemple, cette entrée :

    *astérisques simples*

    _traits soulignés simples_

    **astérisques double**

    __traits soulignés doubles__
    
produira :

    <em>astérisques simples</em>

    <em>traits soulignés simples</em>

    <strong>astérisques double</strong>

    <strong>traits soulignés doubles</strong>
    
Vous pouvez utiliser le style que vous préférez ; la seule restriction est que le même caractère doit être utilisé pour ouvrir et fermer l’étendue de l’emphase.

L’emphase peut être utilisée au milieu d’un mot :

    un*fucking*believable
    
Mais si vous placez un `*` ou `_` entre deux espaces, il sera traité littéralement comme un astérisque ou un trait de soulignement.

Pour produire un astérisque littéral à un endroit où il serait autrement utilisé comme délimiteur d’emphase, vous pouvez l’échapper à l’aide d’une barre oblique inverse :

    \*ce texte est entouré d'astérisques litéraux\*
    CODE

Pour indiquer une étendue de code, entourez-la de guillemets obliques (un accent grave solitaire : `). Contrairement aux blocs de code, une étendue de code signale du code à l’intérieur d’un paragraphe. Par exemple :

Utilisez la fonction `printf()` pour afficher.
produira :

    <p>Utilisez la fonction <code>printf()</code> pour afficher.</p>

Pour écrire des guillemets obliques dans une étendue de code, vous pouvez utiliser plusieurs apostrophes obliques pour ouvrir et fermer
l’étendue de code :

    ``Il y a un guillemet oblique (`) ici.``

ce qui produira ceci :

    <p><code>Il y a un guillemet oblique (`) ici.</code></p>
    
Les délimiteurs de l’étendue de code peuvent inclure des espaces — une après le guillemet d’ouverture, une après celui de fermeture. Ceci vous permet de placer un guillemet oblique littéral au début et à la fin de l’étendue de code :

    Un guillemet oblique dans une étendue de code : `` ` ``

    Du texte délimité par des guillemets obliques dans une 
    étendue de code : `` `foo` ``
    
produira :

    <p>Un guillemet oblique dans une étendue de code : <code>`</code></p>

    <p>Du texte délimité par des guillemets obliques dans une 
    étendue de code : <code>`foo`</code></p>
    
Dans une étendue de code, la perluète et les chevrons sont encodés en entités HTML automatiquement, ce qui permet d’ajouter facilement des exemples de balises HTML. Markdown convertira ceci :

N'utilisez pas `<blink>` s'il vous plaît.
en ceci :

    <p>N'utilisez pas <code>&lt;blink&gt;</code> s'il vous plaît.</p>
    
Vous pouvez écrire ceci :

    `&#8212;` est l'équivalent décimal de `&mdash;`.
    
pour produire :

    <p><code>&amp;#8212;</code> est l'équivalent décimal 
    de <code>&amp;mdash;</code>.</p>
    
    
    
###IMAGES

En effet, c’est plutôt difficile de construire une syntaxe « naturelle » pour placer des images dans un document texte.

Markdown utilise une syntaxe d’image qui ressemble à la syntaxe des liens, permettant deux variantes : incorporé et par référence.

La syntaxe des images incorporées ressemble à ceci :

    ![Texte alternatif](/chemin/vers/img.jpg)

    ![Texte alternatif](/chemin/vers/img.jpg "Titre optionnel")
    
C’est à dire :

Un point d’exclamation : `!` ;
suivi d’une paire de crochets contenant le texte de l’attribut alt de l’image ;
suivi par une parenthèse, contenant l’URL ou le chemin vers l’image, et optionellement un titre entouré par des guillemets droits simples ou doubles.
La syntaxe pour les images par référence ressemble à ceci :

    ![Texte alternatif][id]
    
Où « id » est le nom de la référence de l’image. Les références sont définies en utilisant la même syntaxe que celle des liens par référence :

    [id]: url/vers/image  "Titre optionnel"
    
Au moment d’écrire ceci, Markdown n’a pas de syntaxe pour spécifier les dimensions d’une image ; si c’est important pour vous, vous n’avez qu’à utiliser la balise HTML `<img>`.



###LIENS AUTOMATIQUES

Markdown comprend dans sa syntaxe un raccourci permettant de créer des liens « automatiquement » pour les URL et les adresses de courrier électronique : entourez tout simplement l’adresse par des chevrons. Ceci signifie que si vous voulez rendre visible l’URL ou l’adresse de courrier tout en lui permettant d’être cliquable, vous pouvez écrire ceci :

    <http://exemple.com/>
    
Markdown produira ceci :

    <a href="http://exemple.com/">http://exemple.com/</a>
    
Les liens automatiques pour les adresse de courrier électronique fonctionnent de la même façon, sauf que Markdown exécutera en plus un encodage aléatoire en entités décimales et hexadécimales pour rendre l’adresse plus difficile à lire par les robots qui voudraient vous envoyer du spam. Par exemple, Markdown convertira ceci :

    <adresse@exemple.com>
    
en quelque chose ressemblant à ça :

    <a href="&#x6d;&#x61;&#105;l&#x74;&#x6f;:a&#x64;&#114;&#101;
    &#115;&#115;&#101;&#64;&#101;&#x78;&#101;&#x6d;&#112;&#x6c;&#x65;
    .&#99;&#x6f;&#x6d;">a&#x64;&#114;&#101;&#115;&#115;&#101;&#64;
    &#101;&#x78;&#101;&#x6d;&#112;&#x6c;&#x65;.&#99;&#x6f;&#x6d;</a>
    
ce qui sera affiché par le navigateur comme un lien cliquable vers « adresse@exemple.com ».

(Ce type d’encodage par entité peut tromper beaucoup de robots chercheur d’adresses, mais pas tous. C’est mieux que rien, mais une adresse publiée de cette façon finira probablement par recevoir du spam aussi.)



###ÉCHAPPEMENT PAR BARRE OBLIQUE INVERSE

Markdown vous permet d’utiliser une barre oblique inverse `(\)` pour générer des caractères littéraux qui auraient autrement une autre signification dans la syntaxe de Markdown. Par exemple, si vous voulez entourer un mot avec des astérisques (sans créer de balise HTML `<em>`), ajoutez une barre oblique inverse devant l’astérisque, comme ceci :

    \*astérisques littéraux\*
    
Markdown permet d’échapper les caractères suivants :

    \   barre oblique inverse
    `   guillemet oblique (accent grave)
    *   astérisque
    _   trait souligné
    {}  accolades
    []  crochets
    ()  parenthèses
    #   croisillon (dièse)
    +   plus
    -   moins (tiret)
    .   point
    !   point d'exclamation