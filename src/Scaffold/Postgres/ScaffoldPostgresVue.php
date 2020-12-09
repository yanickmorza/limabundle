<?php

namespace App\LimaBundle\Scaffold\Postgres;

use App\LimaBundle\Scaffold\UtilitaireDatabase;
use Symfony\Component\HttpFoundation\Session\Session;

class ScaffoldPostgresVue
{
    // ------- Generer une vue -------
    public function genererPostgresVue($objet, $vue, $namespace)
    {
        $session = new Session();
        $db = $session->get('database');

        $utilitaireDatabase = new UtilitaireDatabase;
        // --- Recuperer tous les champs de la table concernee

        if ($namespace !== null) {
            $path_vue = "../templates/" . $namespace . "/" . $objet;
        } else {
            $path_vue = "../templates/" . $objet;
        }

        $form_widget = "";
        $colspan = 1;
        $champs_titre = "";
        $champs_donnee = "";
        $nameIndex = $objet . "_index_" . $db;
        $nameEdit  = $objet . "_edit_" . $db;
        $nameDelete = $objet . "_delete_" . $db;
        $nameTruncate = $objet . "_truncate_" . $db;
        $nameNew  = $objet . "_new_" . $db;
        $nameUploader = $objet . "_uploader_" . $db;
        $nameExporter = $objet . "_exporter_" . $db;

        $fields = $utilitaireDatabase->listerChamps($objet);

        foreach ($fields as $field) {

            $type = strtolower($utilitaireDatabase->afficherTypeChamp($objet, $field));

            if ($field != "id") {

                $UCfield = ucfirst(str_replace("_id", "", $field));

                if (substr($field, -3) == "_id") {
                    $nomfield = str_replace("_id", ".id", $field);
                } else {
                    $nomfield = str_replace("_", "", $field);
                }

                if ($UCfield != 'Plainpassword') {
                    $champs_titre .= "<th>" . $UCfield . "&uarr;&darr;</th>\n\t\t\t\t";
                }

                if ($type == 'date' || $type == 'timestamp without time zone') {
                    $champs_donnee .= "<td>{{ $objet.$nomfield | date('d/m/Y') }}</td>\n\t\t\t\t";
                } elseif ($type == 'json') {
                    $champs_donnee .= "<td>{{ $objet.$nomfield ? $objet.$nomfield | json_encode : '' }}</td>\n\t\t\t\t";   //  | json_encode : ''
                } elseif ($type == 'boolean') {
                    $champs_donnee .= "<td>{{ $objet.$nomfield == '1' ? 'OUI' : 'NON' }}</td>\n\t\t\t\t";
                } else {
                    if ($nomfield == 'password') {
                        $champs_donnee .= "<td> ... ... </td>\n\t\t\t\t";
                    } elseif ($nomfield == 'plainpassword') {
                        $champs_donnee .= "";
                        $colspan--;
                    } else {
                        $champs_donnee .= "<td>{{ $objet.$nomfield }}</td>\n\t\t\t\t";
                    }
                }
                $colspan++;
            }
        }
        $champs_titre = trim($champs_titre);
        $champs_donnee = trim($champs_donnee);
        // --- Recuperer tous les champs de la table concernee

        // ----- 1 vue cochée -----
        if ($vue == 1) {

            if (!is_dir($path_vue)) {
                @mkdir($path_vue, 0755, true);
                @fopen($path_vue . "/" . $objet . ".html.twig", "w+");
            }

            @unlink($path_vue . "/form_" . $objet . ".html.twig");

            $fields = $utilitaireDatabase->listerChamps($objet);

            foreach ($fields as $field) {

                $type = strtolower($utilitaireDatabase->afficherTypeChamp($objet, $field));

                if ($field != "id") {
                    if (substr($field, -3) != "_id") {
                        $champ = $field;
                        $Champ = ucfirst(str_replace("_", "", $field));

                        if ($type == 'boolean' || $type == 'json') {
                            $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off' }}) }}\n\t";
                        } else {
                            if ($Champ == 'Plainpassword') {
                                $Champ = 'Password';
                                $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off', 'class': 'border-lima' }}) }}\n\t";
                            } else {
                                $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off', 'class': 'border-lima' }}) }}\n\t";
                            }
                        }
                    } elseif (substr($field, -3) == "_id") {
                        $champ = $field;
                        $Champ = ucfirst(str_replace("_id", "", $field));
                        $champ = str_replace("_id", "", $field);
                        $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off' }}) }}\n\t";
                    }
                }
            }
            $form_widget = trim($form_widget);
            $fichier_view = $path_vue . "/" . $objet . ".html.twig";
            $texte_view = "{% extends 'base.html.twig' %}
{% block title %} {{ titre }} {% endblock %}
{% block body %}
<div class=\"row\">
<div class=\"col-sm-4\">
<div class=\"card mt-3 mb-3 ml-3 border-lima\">
{% if edition %}
<div class=\"card-header breadcrumb-item small bg-lima\">
    <a href=\"{{ path('$nameIndex') }}\" class=\"float-right\" title=\"Fermer\"><img src=\"{{ asset('bundles/lima/assets/images/fermer_24.png') }}\"></a>
</div>
{% else %}
<div class=\"card-header breadcrumb-item small bg-lima\">&nbsp;</div>
{% endif %}
<div class=\"card-body\">
    {{ form_start(form) }}
    $form_widget
    <div class=\"col\">
        <button type=\"submit\" class=\"btn btn-primary\">{{ edition == 'Enregistrer' ? 'Modifier' : 'Enregistrer' }}</button>
    </div>
    {{ form_end(form) }}
<hr />
<form action=\"{{ path('$nameUploader') }}\" method=\"post\" enctype=\"multipart/form-data\">
<div class=\"form-group row\">
    <div class=\"col-12\"><label for=\"charger\"><b>Charger un fichier au format CSV : </b></label></div>
</div>
<div class=\"form-group row\">
<div class=\"col-7 custom-file\">
    <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('uploader') }}\" />
    <input type=\"file\" class=\"custom-file-input\" id=\"charger\" name=\"charger\" lang=\"fr\" accept=\".csv,.pdf\" required />
    <label class=\"custom-file-label\" for=\"charger\">Sélectionner un fichier</label>
</div>
<div class=\"col-5\">
    <button type=\"submit\" class=\"btn btn-primary\">Charger</button>
</div>
</div>
</form>
</div>
</div>
</div>

<div class=\"col-sm-8\">
<div class=\"card mt-3 mb-3 mr-3 border-lima\">
<div class=\"card-header breadcrumb-item small bg-lima\">&nbsp;</div>
<div class=\"table-responsive\">
<div class=\"card-body\"><br />
<table class=\"table paginer\" id=\"table\">
        <thead>
            <tr class=\"list-group-item-lima bg-lima text-white\">
                <th>#</th>
                $champs_titre
                <th colspan=\"2\"></th>
            </tr>
        </thead>
        <tbody>
        {% for $objet in listes %}
            <tr>
                <td>{{ loop.index }}</td>
                $champs_donnee
                <td>
                    <form method=\"post\" action=\"{{ path('$nameEdit', {'id': $objet.id}) }}\">
                        <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('edit' ~ $objet.id) }}\">
                        <input type=\"image\" title=\"Editer\" src=\"{{ asset('bundles/lima/assets/images/edit.png') }}\">
                    </form>
                </td>
                <td>
                    <form method=\"post\" action=\"{{ path('$nameDelete', {'id': $objet.id}) }}\" onsubmit=\"return confirm('Attention vous allez supprimer cet élément, voulez-vous continuer ?');\">
                        <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('delete' ~ $objet.id) }}\">
                        <input type=\"hidden\" name=\"_method\" value=\"DELETE\">
                        <input type=\"image\" title=\"Supprimer\" src=\"{{ asset('bundles/lima/assets/images/delete.png') }}\">
                    </form>
                </td>
            </tr>
        {% else %}
            <tr>
                <td colspan=\"$colspan\">Aucun enregistrement trouvé !</td>
            </tr>
        {% endfor %}
        </tbody>
        <thead>
            <tr>
                <td colspan=\"$colspan\">&nbsp;</td>
                <td>
                    <form method=\"post\" action=\"{{ path('$nameExporter') }}\">
                    <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('exporter') }}\">
                    <input type=\"image\" title=\"Exporter cette liste\" src=\"{{ asset('bundles/lima/assets/images/csv.png') }}\">
                    </form>
                </td>
                <td>
                    <form method=\"post\" action=\"{{ path('$nameTruncate') }}\" onsubmit=\"return confirm('Attention vous allez vider cette liste, voulez-vous continuer ?');\">
                    <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('truncate') }}\">
                    <input type=\"image\" title=\"Vider cette liste\" src=\"{{ asset('bundles/lima/assets/images/corbeille.png') }}\">
                    </form>
                </td>
            </tr>
        </thead>
    </table>
</div>
</div>
</div>
</div>
</div>

<script>
  $(\"input[type=file]\").change(function (e){\$(this).next('.custom-file-label').text(e.target.files[0].name);})
</script>
<script>
	jQuery(document).ready(function($){	
		$('.paginer tbody').paginathing({
		  // Pagination controles
		  prevNext: true,
		  firstLast: true,
		  prevText: '«',
		  nextText: '»',
		  firstText: 'Début',
		  lastText: 'Fin',
		  containerClass: 'pagination-container',
	    	  ulClass: 'pagination',
	    	  liClass: 'page-item',
	    	  activeClass: 'active',
		  limitPagination: true,  // false pour afficher tous les numéros de page
		  insertAfter: '.table'
		});		
	});
</script>
<script>
	var foo = '';
	$(\"#table\").find('tbody').append(foo);
	var table = $(\"#table\").easyTable();
</script>
{% endblock %}";
        }
        // ------ 1 vue cochée ------

        // ----- 2 vues cochées -----
        else {
            if (!is_dir($path_vue)) {
                @mkdir($path_vue, 0755, true);
                fopen($path_vue . "/" . $objet . ".html.twig", "w+");
                fopen($path_vue . "/form_" . $objet . ".html.twig", "w+");
            }

            $fields = $utilitaireDatabase->listerChamps($objet);
            foreach ($fields as $field) {
                $type = strtolower($utilitaireDatabase->afficherTypeChamp($objet, $field));
                if ($field != "id") {
                    if (substr($field, -3) != "_id") {
                        $champ = $field;
                        $Champ = ucfirst(str_replace("_", "", $field));

                        if ($type == 'boolean' || $type == 'json') {
                            $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off' }}) }}\n\t";
                        } else {
                            if ($Champ == 'Plainpassword') {
                                $Champ = 'Password';
                                $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off', 'class': 'border-lima' }}) }}\n\t";
                            } else {
                                $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off', 'class': 'border-lima' }}) }}\n\t";
                            }
                        }
                    } elseif (substr($field, -3) == "_id") {
                        $champ = $field;
                        $Champ = ucfirst(str_replace("_id", "", $field));
                        $champ = str_replace("_id", "", $field);
                        $form_widget .= "{{ form_row(form.$champ, {'label': '$Champ :', 'attr': {'autocomplete': 'off' }}) }}\n\t";
                    }
                }
            }
            $form_widget = trim($form_widget);

            $fichier_view1 = $path_vue . "/" . $objet . ".html.twig";
            $texte_view1 = "{% extends 'base.html.twig' %}
{% block title %} {{ titre }} {% endblock %}
{% block body %}
<div class=\"row\">
<div class=\"col\">
<div class=\"card mt-3 mb-3 ml-3 mr-3 border-lima\">
<div class=\"card-header breadcrumb-item small bg-lima\">&nbsp;</div>
<div class=\"table-responsive\">
<div class=\"card-body\"><br />
<table class=\"table paginer\" id=\"table\">
        <thead>
            <tr class=\"list-group-item-lima bg-lima text-white\">
                <th>#</th>
                $champs_titre
                <th colspan=\"2\"></th>
            </tr>
        </thead>
        <tbody>
        {% for $objet in listes %}
            <tr>
                <td>{{ loop.index }}</td>
                $champs_donnee
                <td>
                    <form method=\"post\" action=\"{{ path('$nameEdit', {'id': $objet.id}) }}\">
                        <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('edit' ~ $objet.id) }}\">
                        <input type=\"image\" title=\"Editer\" src=\"{{ asset('bundles/lima/assets/images/edit.png') }}\">
                    </form>
                </td>
                <td>
                    <form method=\"post\" action=\"{{ path('$nameDelete', {'id': $objet.id}) }}\" onsubmit=\"return confirm('Attention vous allez supprimer cet élément, voulez-vous continuer ?');\">
                        <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('delete' ~ $objet.id) }}\">
                        <input type=\"hidden\" name=\"_method\" value=\"DELETE\">
                        <input type=\"image\" title=\"Supprimer\" src=\"{{ asset('bundles/lima/assets/images/delete.png') }}\">
                    </form>
                </td>
            </tr>
        {% else %}
            <tr>
                <td colspan=\"$colspan\">Aucun enregistrement trouvé !</td>
            </tr>
        {% endfor %}
        </tbody>
        <thead>
            <tr>
                <td colspan=\"$colspan\">&nbsp;</td>
        	    <td colspan=\"2\"><a href=\"{{ path('$nameNew') }}\" title=\"Nouvel Enregistrement\" class=\"btn btn-primary\">Nouvel Enregistrement</a></td>
            </tr>
        </thead>
        <thead>
            <tr>
                <td colspan=\"$colspan\">
                    <form action=\"{{ path('$nameUploader') }}\" method=\"post\" enctype=\"multipart/form-data\">
                    <div class=\"form-group row\">
                        <div class=\"col-12\"><label for=\"charger\"><b>Charger un fichier au format CSV : </b></label></div>
                    </div>
                    <div class=\"form-group row\">
                    <div class=\"col-7 custom-file\">
                        <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('uploader') }}\" />
                        <input type=\"file\" class=\"custom-file-input\" id=\"charger\" name=\"charger\" lang=\"fr\" accept=\".csv,.pdf\" required />
                        <label class=\"custom-file-label\" for=\"charger\">Sélectionner un fichier</label>
                    </div>
                    <div class=\"col-5\">
                        <button type=\"submit\" class=\"btn btn-primary\">Charger</button>
                    </div>
                    </div>
                    </form>
                </td>
                <td>
                    <form method=\"post\" action=\"{{ path('$nameExporter') }}\">
                    <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('exporter') }}\">
                    <input type=\"image\" title=\"Exporter cette liste\" src=\"{{ asset('bundles/lima/assets/images/csv.png') }}\">
                    </form>
                </td>
                <td>
                    <form method=\"post\" action=\"{{ path('$nameTruncate') }}\" onsubmit=\"return confirm('Attention vous allez vider cette liste, voulez-vous continuer ?');\">
                    <input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token('truncate') }}\">
                    <input type=\"image\" title=\"Vider cette liste\" src=\"{{ asset('bundles/lima/assets/images/corbeille.png') }}\">
                    </form>
                </td>
            </tr>
        </thead>
    </table>
</div>
</div>
</div>
</div>
</div>

<script>
  $(\"input[type=file]\").change(function (e){\$(this).next('.custom-file-label').text(e.target.files[0].name);})
</script>
<script>
	jQuery(document).ready(function($){	
		$('.paginer tbody').paginathing({
		  // Pagination controles
		  prevNext: true,
		  firstLast: true,
		  prevText: '«',
		  nextText: '»',
		  firstText: 'Début',
		  lastText: 'Fin',
		  containerClass: 'pagination-container',
	    	  ulClass: 'pagination',
	    	  liClass: 'page-item',
	    	  activeClass: 'active',
		  limitPagination: true,  // false pour afficher tous les numéros de page
		  insertAfter: '.table'
		});		
	});
</script>
<script>
	var foo = '';
	$(\"#table\").find('tbody').append(foo);
	var table = $(\"#table\").easyTable();
</script>
{% endblock %}";
            $fichier_view2 = $path_vue . "/form_" . $objet . ".html.twig";
            $texte_view2 = "{% extends 'base.html.twig' %}
{% block title %} {{ titre }} {% endblock %}
{% block body %}
<div class=\"row\">
<div class=\"col\">
<div class=\"card mt-3 mb-3 ml-3 mr-3 border-lima\">
<div class=\"card-header breadcrumb-item small bg-lima\">
    <a href=\"{{ path('$nameIndex') }}\" class=\"float-right\" title=\"Fermer\"><img src=\"{{ asset('bundles/lima/assets/images/fermer_24.png') }}\"></a>
</div>
<div class=\"card-body\">
    {{ form_start(form) }}
    $form_widget
    <div class=\"col\">
        <button type=\"submit\" class=\"btn btn-primary\">{{ edition == 'Enregistrer' ? 'Modifier' : 'Enregistrer' }}</button>
    </div>
    {{ form_end(form) }}
</div>
</div>
</div>
</div>
{% endblock %}";
        }
        // ----- 2 vues cochées -----
        if ($vue == 1) {
            file_put_contents($fichier_view, $texte_view);
        } else {
            file_put_contents($fichier_view1, $texte_view1);
            file_put_contents($fichier_view2, $texte_view2);
        }
    }
    // ------- Generer une vue -------

    // ------ Supprimer une vue ------
    public function supprimerPostgresVue($objet, $namespace)
    {
        if ($namespace !== null) {
            $path_vue = "../templates/" . $namespace . "/" . $objet;
        } else {
            $path_vue = "../templates/" . $objet;
        }

        if (is_dir($path_vue)) {
            array_map('unlink', glob($path_vue . "/*.twig"));
            rmdir($path_vue);
        }
    }
    // ------ Supprimer une vue ------
}
