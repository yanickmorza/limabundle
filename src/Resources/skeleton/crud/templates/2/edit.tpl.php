<?= $helper->getHeadPrintCode('Modifier ce '.$entity_class_name) ?>

{% block body %}
    <h4 class="text-secondary m-4">Modifier ce <?= $entity_class_name ?></h4>

    {{ include('<?= $templates_path ?>/_form.html.twig') }}

{% endblock %}
