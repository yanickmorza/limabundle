<?= $helper->getHeadPrintCode('Liste des '.$entity_class_name); ?>

{% block body %}
<h4 class="text-secondary m-4">Liste des <?= $entity_class_name ?></h4>
<div class="card shadow border-lima">
    <div class="card-body">
        <table class="table" id="dataTable">
            <thead>
                <tr class="list-group-item-lima bg-lima text-white">
                    <th>#</th>
    <?php foreach ($entity_fields as $field): ?>
        <?php if ($field['fieldName'] != 'id'): ?>
                    <th><?= ucfirst($field['fieldName']) ?></th>
        <?php endif; ?>
    <?php endforeach; ?>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
        {% for <?= $entity_twig_var_singular ?> in <?= $entity_twig_var_plural ?> %}
                <tr>
                    <td><a href="{{ path('<?= $route_name ?>_show', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="btn btn-warning">{{ loop.index }}</a></td>
    <?php foreach ($entity_fields as $field): ?>
        <?php if ($field['fieldName'] != 'id'): ?>
                    <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
        <?php endif; ?>
    <?php endforeach; ?>
                    <td>
                        <a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="btn btn-success">Editer</a>
                    </td>
                    <td>
                        {{ include('<?= $templates_path ?>/_delete_form.html.twig') }}
                    </td>
                </tr>
        {% else %}
                <tr>
                    <td colspan="<?= (count($entity_fields) + 1) ?>">Aucun enregistrement trouvé</td>
                </tr>
        {% endfor %}
            </tbody>
        </table>
        <a href="{{ path('<?= $route_name ?>_new') }}" class="btn btn-lima">Nouvel enregistrement</a>
    </div>
</div>
{% endblock %}

