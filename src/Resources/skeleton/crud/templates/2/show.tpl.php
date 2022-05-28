<?= $helper->getHeadPrintCode($entity_class_name) ?>

{% block body %}
<h4 class="text-secondary m-4"><?= $entity_class_name ?></h4>
<div class="card shadow border-lima">
    <div class="card-body">
        <table class="table">
            <tbody>
    <?php foreach ($entity_fields as $field): ?>
        <?php if ($field['fieldName'] != 'id'): ?>
                <tr>
                    <th><?= ucfirst($field['fieldName']) ?></th>
                    <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
                </tr>
        <?php endif; ?>
    <?php endforeach; ?>
            </tbody>
                <tr>
                    <td><a href="{{ path('<?= $route_name ?>_index') }}" class="btn btn-warning">Fermer</a></td>
                    <td><a href="{{ path('<?= $route_name ?>_edit', {'<?= $entity_identifier ?>': <?= $entity_twig_var_singular ?>.<?= $entity_identifier ?>}) }}" class="btn btn-success">Editer</a></td>
                    <td>{{ include('<?= $templates_path ?>/_delete_form.html.twig') }}</td>
                </tr>
        </table>
    </div>
</div>  
{% endblock %}
