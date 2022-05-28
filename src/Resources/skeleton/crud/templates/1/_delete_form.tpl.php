<form method="post" action="{{ path('<?= $route_name ?>_delete', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" onsubmit="return confirm('Êtes-vous sûr de bien vouloir supprimer cet élément ?');">
    <input type="hidden" name="_token" value="{{ csrf_token('delete' ~ <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>) }}">
    <button class="btn btn-danger">Supprimer</button>
</form>
